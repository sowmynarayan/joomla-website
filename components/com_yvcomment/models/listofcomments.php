<?php
/** * yvComment - A User Comments Component, developed for Joomla 1.5 
* @package yvComment 
* @(c) 2007-2009 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * yvComments Component "Listofcomments" Model
 *
 */
class yvcommentModelListofcomments extends JModel
{
	var $_yvCommentID = 0;
	var $_task = '';
	// Content state filter
	var $_filter_state = '';
	var $_authoridsfilter = '';
	
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
	var $_allow_comments_on_comment = '0';
	var $_ShowCommentsOnComment = false;

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
		if ($yvComment->getPageValue('show_comments_on_comment', '1')) {
	  	$this->_allow_comments_on_comment = $yvComment->getConfigValue('allow_comments_on_comment', '0');	
			switch ($this->_allow_comments_on_comment) {
				case 'administrators_reply_only' :
				case 'owners_reply_only' :
				case 'one_level_deep' :
				case 'threaded_comments' :
					$this->_ShowCommentsOnComment = true;
			}
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
	
		// Set yvComment ID and wipe data
		$this->_yvCommentID		= $yvCommentID;
		$this->_data	= null;
		$this->setTask($task);
		$this->_filter_state = $filter_state;
		switch ($this->_filter_state) {
			case 'P':
				break;
			case 'U':
			case 'A':
				// Filter by state in on (and not 'Published'), so show list of comments in one level only
				$this->_ShowCommentsOnComment = false;
				break;
		}
		$this->_authoridsfilter = $yvComment->getPageValue('authoridsfilter', '');
		if (!empty($this->_authoridsfilter)) {
			$this->_ShowCommentsOnComment = false;
		}
		
		//echo 'setParms id=' . $this->_yvCommentID . '; task=' . $task .'<br/>';
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
	    	$query = $this->_buildSQL(true, 0);
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
	    	$query = $this->_buildSQL(false, 0);
				//$this->appendMessage('query="' . $query . '"');
			  $this->_data = $this->_getList($query, $this->_pagination->limitstart, $this->_pagination->limit);
			  if ($this->_db->getErrorNum() != 0) {
				  $this->appendMessage($yvComment->printDbErrors($this->_db));
			  } else  {
			  	$Ok = (boolean) $this->_data;
			  }
			  //echo ' this->_yvCommentID=' . $this->_yvCommentID . ' count=' . count($this->_data);
			}

		} else {
			$Ok = true;
		}
		return $Ok;
	}

	function getShowCommentsOnComment()
	{
		return $this->_ShowCommentsOnComment;
	}

	// Load comments on Comment
	function getChildren()
	{
	  $yvComment = &yvCommentHelper::getInstance();
		$user		=& JFactory::getUser();
		$aid		= (int) $user->get('aid', 0);
	  
	  $Ok = false;	
		if ($this->_ShowCommentsOnComment)
		if ($this->_data) 
		foreach ($this->_data as $comment) {
			$CommentID = $comment->id;
    	$query = $this->_buildSQL(false, $CommentID);
		  $children = $this->_getList($query);
		  if ($this->_db->getErrorNum() != 0) {
			  $this->appendMessage($yvComment->printDbErrors($this->_db));
			  break;
		  } else  {
		  	if ($children) {
		  		$comment->children = $children; 
		  	}
		  	$Ok = true;
		  }
		} // foreach $comments
		return $Ok;
	}

	// Load number of children only
	function getChildrenCount()
	{
	  $yvComment = &yvCommentHelper::getInstance();
		$user		=& JFactory::getUser();
		$aid		= (int) $user->get('aid', 0);
	  
	  $Ok = false;	
		if ($this->_data) 
		foreach ($this->_data as $comment) {
			$CommentID = $comment->id;
    	$query = $this->_buildSQL(true, $CommentID);
			$ChildrenCount = $yvComment->DLookup_db($this->_db, $query, '');
		  if ($this->_db->getErrorNum() != 0) {
			  $this->appendMessage($yvComment->printDbErrors($this->_db));
			  break;
		  } else  {
				$comment->ChildrenCount = $ChildrenCount;
		  	$Ok = true;
		  }
		} // foreach $comments
		return $Ok;
	}

	// If $CommentID != 0 - load data for children (comments on Comment)
	function _buildSQL($CountOnly = false, $CommentID = 0)
	{
	  $yvComment = &yvCommentHelper::getInstance();
	  $query = '';  
		$user		=& JFactory::getUser();
		$aid		= (int) $user->get('aid', 0);
	  
    $Select = 'c.*, u.' . $this->_author_mentioned_by . ' AS AuthorName';	
  	$From = $yvComment->getTableName() . ' AS c' .
			    ' LEFT JOIN #__users AS u ON u.id=c.created_by';
		$Where = '';    
		$OrderBy = '';
		if ($yvComment->UseDesignatedSectionForComments()) {
			$Where = '(c.sectionid=' . $yvComment->getSectionForComments() . ')';    
		} else {
			$Where = '(c.parentid<>0)';    
		}
		
		$Where .= ' AND c.access <= '. (int) $aid;

		$From = '(' . $From . ') LEFT JOIN #__content AS ar ON c.parentid=ar.id' ;
		$Where .= ' AND ar.access <= '. (int) $aid;

		if ($CommentID == 0) {
			$Select .= ', ar.title AS ArticleTitle, ar.created_by AS ArticleAuthorID, ar.created_by_alias AS ArticleAuthorAlias';   
	
	  	switch ($yvComment->getFilterByContext()) {
	  		case 'article':
			  	$Where .= ' AND c.parentid=' . $yvComment->getArticleID();
	  		  break;
	  		case 'category':
			  	$Where .= ' AND ar.catid=' . $yvComment->getCategoryID();
	  		  break;
	  		case 'section':
			  	$Where .= ' AND ar.sectionid=' . $yvComment->getSectionID();
	  		  break;
	  		default:
					if ($this->_ShowCommentsOnComment) {
		  			// For the whole site don't show comments on comments on the first level
				  	$Where .= ' AND ar.parentid=0';
					}
	  	}

			if (!empty($this->_authoridsfilter)) {
			  $Where .= ' AND (c.created_by IN(' . $this->_authoridsfilter . '))';
			}
	
			$From = '(' . $From . ') LEFT JOIN #__users AS aru ON aru.id=ar.created_by' ;
			$Select .= ', aru.' . $this->_author_mentioned_by . ' AS ArticleAuthorName';   
		} else {
	  	$Where .= ' AND c.parentid=' . $CommentID;
		}

		if ($this->_author_linkable == 'yes') {
	    $Select .= ', cd.webpage';
	    $From = '(' . $From . ') LEFT JOIN #__contact_details AS cd ON cd.user_id = c.created_by';
		}

		//See similar 'if condition' in /components/com_content/models/frontpage.php,
		//  function _buildContentWhere
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

		$nDays = $yvComment->ResultDaysToNDays($yvComment->getPageValue('result_days', 'all'));
		if ($nDays > 0) {
			$Where .= ' AND (c.created > ' . 
				$yvComment->SecondsFromNowToSQLDate($yvComment->DaysToSeconds($nDays)) . ')';
		}

		if ($CommentID == 0) {
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
	      	$OrderBy = 'c.created DESC';
			}

	    $query = 'SELECT ' . $Select
			    . ' FROM ' . $From 
			    . ' WHERE ' . $Where
			    . ' ORDER BY ' . $OrderBy;
			//$this->appendMessage('query="' . $query . '"');
	  }
		return $query;
	}

	function &getyvCommentID()
	{
		return $this->_yvCommentID;
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
