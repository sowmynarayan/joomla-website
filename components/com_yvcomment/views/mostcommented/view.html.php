<?php
/**
* yvComment - A User Comments Component, developed for Joomla 1.5
* @version 1.18.002
* @package yvComment
* @(c) 2007-2008 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/
defined('_JEXEC') or die( 'Restricted access' );


if (function_exists('jimport')) {
	// yvolk 2008-07-09 Somehow it is not found sometimes...
	jimport( 'joomla.application.component.view');
}

class yvcommentViewMostcommented extends JView
{
  var $_doEcho = true;
  // '', 'plugin', 'module'
  var $_DisplayTo = '';

	function getOutput()
	{
		return $this->_output;
	}  
  
	function display( $tpl = null)
	{
		global $mainframe;
    $yvComment = &yvCommentHelper::getInstance();
    $result = '';
		$message = array();
		$items = null;

    $ShowComments = false;

   	$ShowFilters = false;
   	$ShowPagination = false;
   	$ShowControlForm = false;
    	
    //echo 'yvcommentViewMostcommented->display()';

	  $DisplayTo = $yvComment->DisplayTo();
		switch ($DisplayTo) {
		  case 'module' :
		  case 'plugin' :
        $this->_doEcho = false;
		}
		$this->_output = null;

		$print = JRequest::getBool('print');
    
		// Initialize some variables
		$user		=& JFactory::getUser();

		// Get the page/component configuration
    $params =& $yvComment->PageParameters(); 

		// Get information from model
		$task =& $this->get('task');

		if ($mainframe->isAdmin()) {
   		$ShowFilters = true;
		}
		if ($yvComment->IsPaginationEnabled()) {
			// Pagination is set to true by default, 
			// to avoid too long lists 
			// (there may be too many comments in the list...)		
   		$ShowPagination = $params->get('show_pagination', true);
		}
		
		if ($yvComment->_debug) {
      $message[] = 'Pagination enabled=' . $yvComment->IsPaginationEnabled()
        . '; Params=' . $params->get('show_pagination') 
      	. '; ShowPagination=' . $ShowPagination;
      $message[] = 'orderby_pri=' . $yvComment->getPageValue('orderby_pri','(not set)');	
		}  	
    if ($ShowPagination) {
     	$yvComment->setPagination();
    }
    
    $pagination =& $this->get('pagination');
    $nCommentsTotal = ($pagination ? $pagination->total : 0);

    $ShowComments = ($nCommentsTotal > 0);
		if (!$ShowComments) {
   		$ShowPagination = false;
		}	
		if ($ShowComments) {
			$items =& $this->get('data');
			
	    // on this page 
		  $nComments = count($items);
		  if ($nComments >0 && ($DisplayTo != 'module')) {
				$yvComment->setShowLogo(true);
		  }
			
			for ($i=0; $i < $nComments; $i++) {
				// Unify the introtext and fulltext fields... for compatibility with content plugins...
				$item = & $items[$i];
				$yvComment->PrepareItemForView($item);
		  }
		}
    //$message[] = 'nComments=' . $nComments;

    // $message is an array of messages
		$message = array_merge ($message, $this->get('Message'));

 	  // Filters and pagination are inside Control Form
   	$ShowControlForm = $ShowFilters || $ShowPagination;
    $ShownSomething = ($ShowComments || $ShowControlForm);
    if (!$ShownSomething) {
      if ($DisplayTo == 'plugin' || $DisplayTo == 'module') {
	  	  // Hide yvComment
	  	  // TODO some param to hide it... $params->set('HideyvComment', true);
      } else {
      	$message[] = '<br />No comments';
      }
    }

    $ShowTemplate = (boolean) ($ShownSomething || (count($message)>0)); 
		if ($ShowTemplate) {
			if (count($message)>0) {
				$yvComment->setShowLogo(true);
			}

			$this->assignRef('print', $print);
			$this->assignRef('user',		$user);
			$this->assign('task' , $task);
			$this->assignRef('pagination',	$pagination);
	
			if ($items) {
				$this->assignRef('items',		$items);
			}
	
			$this->assign('message' , $message);
	
			$params->def('author_linkable', $yvComment->getConfigValue('author_linkable', '0'));
			$params->def('comment_linkable', $yvComment->getConfigValue('comment_linkable', '0'));
			
			$params->def('orderby_pri', $yvComment->getPageValue('orderby_pri'));
	
			$params->def('execute_content_plugins', $yvComment->getConfigValue('execute_content_plugins', '0'));

			$params->set('nCommentsTotal', $nCommentsTotal);
			$params->set('ShowComments', $ShowComments);
			$params->set('ShowFilters', $ShowFilters);
			$params->set('yvcomment_show_pagination', $ShowPagination);
			$params->set('ShowControlForm', $ShowControlForm);

			$params->def('date_format', JText::_('DATE_FORMAT_LC2'));
			$params->set('filter_state', $this->get('Filter_state'));

			$this->assignRef('params',	$params);
	
	  	//echo 'yvcommentViewComment task="' . $task . '"<br/>';
	
			//return parent::display($tpl);
			$result = $this->loadTemplate($tpl);
		}	
		if (JError::isError($result)) {
			return $result;
		}
    if ($this->_doEcho) {
		  echo $result;
		}
	}
}
?>