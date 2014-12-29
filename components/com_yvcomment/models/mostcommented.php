<?php
/** * yvComment - A User Comments Component, developed for Joomla 1.5 
* @version 1.0.0 
* @package yvComment 
* @(c) 2007 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * yvComments Component "Mostcommented" Model
 *
 */
class yvcommentModelMostcommented extends JModel
{
	/**
	 * yvCommentID
	 *
	 * @var int
	 */
	var $_task = '';
	// Content state filter
	var $_filter_state = '';
	
	/**
	 * Selected comments
	 *
	 * @var array
	 */
	var $_data = null;
	// Total number of rows (without pagination)
	var $_dataRowsTotal = 0;
	var $_pagination = null; //JPagination
	
	var $_Message = array();
	
	// Configuration settings
	var $_author_linkable = '0';	
	var $_author_mentioned_by = 'name';

	function __construct()
	{
		parent::__construct();
	  $yvComment = &yvCommentHelper::getInstance();	

		//echo 'Constructor of yvcommentModelComment <br />';
		
  	$this->_author_linkable = $yvComment->getConfigValue('author_linkable', '0');	

  	$val1 = $yvComment->getConfigValue('author_mentioned_by', 'name');	
		switch ($val1) {
			case 'name' :
			case 'username' :
				$this->_author_mentioned_by = $val1;
		}
	}

	/**
	 * Method to set identifiers...
	 *
	 * @access	public
	 */
	function setParms($yvCommentID = 0, $task = 'add', $filter_state = 'P')
	{
		$yvComment = &yvCommentHelper::getInstance();	
	
		// Wipe data
		$this->_data	= null;
		$this->setTask($task);
		$this->_filter_state = $filter_state;
		
		//echo 'setParms task=' . $task .'<br/>';
	}

	/**
	 * Rows of Comments...
	 *
	 */
	function &getData()
	{
		if ($this->_loadData('data'))
		{
			// Initialize some variables
			// ...
		}

		return $this->_data;
	}
	
	// Total number of rows (without pagination)
	function getdataRowsTotal() {
	  return $this->_dataRowsTotal;	
	}

	function getPagination() {
		$this->_loadData('pagination');
	  return $this->_pagination;	
	}
	
	function getFilter_state() {
	  return $this->_filter_state;	
	}
	
	// $mode = 'pagination', 'data'
	function _loadData($mode = 'pagination')
	{
	  $yvComment = &yvCommentHelper::getInstance();
		$user		=& JFactory::getUser();
		$aid		= (int) $user->get('aid', 0);
	  
	  $Ok = false;	
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination) || ($mode == 'data' && empty($this->_data)))	{
      if (empty($this->_pagination)) {
	    	$query = $this->_buildSQL(true);
				jimport('joomla.html.pagination');
				$this->_dataRowsTotal = $yvComment->DLookup_db($this->_db, $query, '');
			  if ($this->_db->getErrorNum() != 0) {
				  $this->appendMessage($yvComment->printDbErrors($this->_db));
			  } else  {
			  	$Ok = true;
			  }

			  $limitstart = $yvComment->getPageValue('yvcomment_limitstart', '0'); // offset from first row returned
			  $limit = $yvComment->getPageValue('yvcomment_limit', '0');	     // number of rows returned
				if ($yvComment->_debug) {
					$this->appendMessage('limit=' . $limit . '; limitstart=' . $limitstart . '; rowstotal=' . $this->_dataRowsTotal);
				}  	
	      $this->_pagination = new yvCommentJPagination($this->_dataRowsTotal, $limitstart, $limit);
      }
			if ($mode == 'data') {
	    	$query = $this->_buildSQL(false);
				//$this->appendMessage('query="' . $query . '"');
			  $this->_data = $this->_getList($query, $this->_pagination->limitstart, $this->_pagination->limit);
			  if ($this->_db->getErrorNum() != 0) {
				  $this->appendMessage($yvComment->printDbErrors($this->_db));
			  } else  {
			  	$Ok = (boolean) $this->_data;
			  }
			  //echo ' count=' . count($this->_data);
			}

		} else {
			$Ok = true;
		}
		return $Ok;
	}

	// TODO: delete: If $CommentID != 0 - load data for children (comments on Comment)
	function _buildSQL($CountOnly = false)
	{
	  $yvComment = &yvCommentHelper::getInstance();
	  $query = '';  
		$user		=& JFactory::getUser();
		$aid		= (int) $user->get('aid', 0);
	  
    $Select = 'COUNT(c.id) As NumComments';	
  	$From = $yvComment->getTableName() . ' AS c';
		$Where = '';    
		$OrderBy = '';
		if ($yvComment->UseDesignatedSectionForComments()) {
			$Where = '(c.sectionid=' . $yvComment->getSectionForComments() . ')';    
		} else {
			$Where = '(c.parentid<>0)';    
		}
		$GroupBy = 'c.parentid';
		
		$Where .= ' AND c.access <= '. (int) $aid;

		$nDays = $yvComment->ResultDaysToNDays($yvComment->getPageValue('result_days', 'all'));
		if ($nDays > 0) {
			$Where .= ' AND (c.created > ' . 
				$yvComment->SecondsFromNowToSQLDate($yvComment->DaysToSeconds($nDays)) . ')';
		}

		$From = '(' . $From . ') INNER JOIN #__content AS ar ON c.parentid=ar.id' ;
		$Select .= ', ar.*';   
		$Where .= ' AND ar.access <= '. (int) $aid;

    $Select .= ', u.' . $this->_author_mentioned_by . ' AS AuthorName';	
		$From = '(' . $From . ') LEFT JOIN #__users AS u ON u.id=ar.created_by';

  	switch ($yvComment->getFilterByContext()) {
  		case 'article':
		  	$Where .= ' AND ar.id=' . $yvComment->getArticleID();
  		  break;
  		case 'category':
		  	$Where .= ' AND ar.catid=' . $yvComment->getCategoryID();
  		  break;
  		case 'section':
		  	$Where .= ' AND ar.sectionid=' . $yvComment->getSectionID();
  		  break;
  		default:
  			// For the whole site don't show comments on comments on the first level
		  	$Where .= ' AND ar.parentid=0';
  	}

		if ($this->_author_linkable == 'yes') {
	    $Select .= ', cd.webpage';
	    $From = '(' . $From . ') LEFT JOIN #__contact_details AS cd ON cd.user_id = ar.created_by';
		}

		//Filter state of both Articles AND Comments
		//See similar 'if condition' in /components/com_content/models/frontpage.php,
		//  function _buildContentWhere
		// 1. For Articles
		$WhereState = ''; 
		if ($yvComment->UserCanEdit() == 'all') {
			// Content state filter
			if ($this->_filter_state) {
				if ($this->_filter_state == 'P') {
			  	$WhereState = 'ar.state = 1';
				} else {
					if ($this->_filter_state == 'U') {
			  		$WhereState = 'ar.state = 0';
					} else if ($this->_filter_state == 'A') {
			  		$WhereState = 'ar.state = -1';
					}
				}
			}
		}
		else {
	  	$WhereState = 'ar.state = 1';
		}
		if (empty($WhereState)) {
			$WhereState = 'ar.state != -2';
		}
  	$Where .= ' AND ' . $WhereState;
		// 2. The same - for comments:
		$WhereState = ''; 
		if ($yvComment->UserCanEdit() == 'all') {
			// Content state filter
			if ($this->_filter_state) {
				if ($this->_filter_state == 'P') {
			  	$WhereState = 'c.state = 1';
				} else {
					if ($this->_filter_state == 'U') {
			  		$WhereState = 'c.state = 0';
					} else if ($this->_filter_state == 'A') {
			  		$WhereState = 'c.state = -1';
					}
				}
			}
		}
		else {
	  	$WhereState = 'c.state = 1';
		}
		if (empty($WhereState)) {
			$WhereState = 'c.state != -2';
		}
  	$Where .= ' AND ' . $WhereState;

		// Filter by section(s), category(ies) and even by article(s)
		$exclude = (boolean) $yvComment->getPageValue('articlesectionids_excludefilter', '0');
		$articlesectionidsfilter = $yvComment->getPageValue('articlesectionidsfilter', '');
		$filter = '';
		if (!empty($articlesectionidsfilter)) {
			if (!empty($filter)) {
			  $filter .= ($exclude ? ' AND ' : ' OR ');
			}
		  $filter .= 'ar.sectionid' . ($exclude ? ' NOT' : '') . ' IN(' . $articlesectionidsfilter . ')';
		}
		$articlecategoryidsfilter = $yvComment->getPageValue('articlecategoryidsfilter', '');
		if (!empty($articlecategoryidsfilter)) {
			if (!empty($filter)) {
			  $filter .= ($exclude ? ' AND ' : ' OR ');
			}
			$filter .= 'ar.catid' . ($exclude ? ' NOT' : '') . ' IN(' . $articlecategoryidsfilter . ')';
		}
		$articleidsfilter = $yvComment->getPageValue('articleidsfilter', '');
		if (!empty($articleidsfilter)) {
			if (!empty($filter)) {
			  $filter .= ($exclude ? ' AND ' : ' OR ');
			}
			$filter .= 'ar.id' . ($exclude ? ' NOT' : '') . ' IN(' . $articleidsfilter . ')';
		}
		if (!empty($filter)) {
		  $Where .= ' AND (' . $filter . ')';
		}

  	if ($CountOnly) {
	    $query = 'SELECT Count(*) As Expr1'
			    . ' FROM ' . $From 
			    . ' WHERE ' . $Where;
	  } else {
			switch ($yvComment->getPageValue('orderby_pri', 'default')) {
				case 'date' :
	      	$OrderBy = 'c.created ASC';
				  break;
				default :
					$OrderBy = 'NumComments DESC';
	      	//$OrderBy = 'c.created DESC';
			}

	    $query = 'SELECT ' . $Select
			    . ' FROM ' . $From 
			    . ' WHERE ' . $Where
			    . ' GROUP BY ' . $GroupBy
			    . ' ORDER BY ' . $OrderBy;
			//$this->appendMessage('query="' . $query . '"');
	  }
		return $query;
	}

	function appendMessage( $messages )
	{
		if (is_array($messages)){
			foreach ($messages as $message) {
	      $this->_Message[] = $message;
			}
		} else if (strlen(trim($messages)) > 0) {
	    $this->_Message[] = $messages;
	  }  
    return true;
	}

	function &getMessage()
	{
		return $this->_Message;
	}

	function setTask($task)
	{
   	//echo 'setTask="' . $task . '"<br/>';
		$this->_task = $task;
	}

	function &getTask()
	{
		return $this->_task;
	}
}

?>
