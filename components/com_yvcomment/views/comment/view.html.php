<?php
/**
* yvComment - A User Comments Component, developed for Joomla 1.5
* @version		$Id: view.html.php 2 2009-08-30 15:10:43Z yvolk $
* @package yvComment
* @(c) 2007-2008 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/

defined('_JEXEC') or die( 'Restricted access' );

if (function_exists('jimport')) {
	// yvolk 2008-07-09 Somehow it is not found sometimes...
	jimport( 'joomla.application.component.view');
}

class yvcommentViewcomment extends JView
{
  var $_doEcho = true;
	
	function getOutput()
	{
		return $this->_output;
	}  
  
	function display( $tpl = null)
	{
		global $mainframe;
	  $yvComment = &yvCommentHelper::getInstance();
	  $message = array();
	  $article = null;	
    $ParentArticle = null;
    $CurrentItem = null;
    $nCommentsTotal = 0;
      
	  $linkOn = null;    
		$editor = null;

    $ShowTemplate = false;

	  $WhatToShow = "comment";
		// Show some info about the Article that is being commented
    $ShowArticleTitle = false;  // and, maybe, link to the article itself
    $ShowArticle = false;
		// Show 'This is comment of...'
    $ShowParentArticle = false;
		// Show number of comments only (or link to add comment)
    $ShowNumberOfCommentsAlone = false;  
		// Show list of comments (using other MVC...)
		$ShowComments = false;
		// Preview current item (i.e. comment)
		$ShowPreview = false;
		// Show form or link to the form
		$ShowForm = false;
		$ShowPleaseRegister = false;
		
    //echo 'yvcommentViewComment->display()';

	  $DisplayTo = $yvComment->DisplayTo();
		switch ($DisplayTo) {
		  case 'module' :
		  case 'plugin' :
        $this->_doEcho = false;
		}
		$this->_output = null;

		$print = JRequest::getBool('print');

		$ArticleID = $yvComment->getArticleID();
		if ($ArticleID == 0) {
			if ($yvComment->_debug) {
				global $mainframe;
				$mainframe->enqueueMessage( '"' . __CLASS__ . '": $ArticleID == 0', 'notice');
			}
			return;
		}
    
		// Initialize some variables
		$user		=& JFactory::getUser();

		// Get the page/component configuration
    $params =& $yvComment->PageParameters(); 

		$task =& $this->get('task');
    $task_next = "";
		if ($yvComment->_debug) {
    	$message[] =	'task="' . $task . '"; DisplayTo="' . $DisplayTo . '"'; 
		}

		$MayAdd = false;
		$MayUpdate = false;
		$AddEnabledForArticle = false;
		$CommentsAreClosed = false;
		$MayAccessFullArticle = false;
		
    switch ($task) {
      case 'deletedisplay':
      case 'editdisplay':
				$MayUpdate = $yvComment->EditEnabled($this->get('yvCommentID'));
				break;
  		default:
				$AddEnabledForArticle = $yvComment->AddEnabledForArticle($ArticleID);
				$CommentsAreClosed = $yvComment->CommentsAreClosed($ArticleID);
				if ($AddEnabledForArticle) {
					$MayAdd = $yvComment->AddEnabledForUser();
				}
		   	//$message[] = 'MayAdd="' . $MayAdd . '"; AddEnabledForArticle="' . $AddEnabledForArticle . '"';
    } 	  

		if ($yvComment->DisplayTo() != 'component'
			&& $yvComment->ParentOption() != 'com_yvcomment') {
 			if (!$article) { $article = $this->get('Article'); }
			// Check to see if the user has access to view the full article
			if ($article->access <= $user->get('aid', 0)) {
				$MayAccessFullArticle = true;
			}
			if ($MayAccessFullArticle) {
			 	$paramName = 'what_to_show';
				switch ($yvComment->ParentView()) {
				case 'article':
					$paramName = 'what_to_show_article_view';
					break;				
				case 'frontpage':
					$paramName = 'what_to_show_frontpage';
					break;				
				}
		  	$WhatToShow = $params->get($paramName);
				//$message[] = 'WhatToShow=' . $WhatToShow;
			}
	  	switch ($WhatToShow) {
	  		case 'comment':
					$ShowComments = true;
	  			break;
	  		case 'comments_n_link':
			    $nCommentsTotal = $yvComment->getNComments($ArticleID);
			    $ShowNumberOfCommentsAlone = ( ($nCommentsTotal > 0) || $MayAdd);
			    $WhereToLink = "";
					if ($ShowNumberOfCommentsAlone) {
						// Where should the link lead?
						if ($yvComment->ParentView() == 'article') {
							// We're on the Article page already
							$WhereToLink = "comment";		
						} else {
		  				switch ($params->get('what_to_show_article_view')) {
				  		case 'comment':
								$WhereToLink = "article";		
				  			break;
				  		default:
								$WhereToLink = "comment";		
		  				}
						}
						
						if ($WhereToLink == 'article') {
							// If there are no comments and Form to add comments is on a separate page
							// then link directly to that separate page.
							if ($nCommentsTotal == 0) {
				  			switch ($yvComment->getPageValue('addform_position', 'below')) {
				  				case 'separate_below':
				  				case 'separate_above':
										$WhereToLink = "comment";		
				  			} 
							}
						}	
						
						//$message[] = 'WhereToLink=' . $WhereToLink;
				    switch ($WhereToLink) {
				  	case 'comment':
				  		$link = "index.php?option=com_yvcomment&view=comment&ArticleID=" . $article->id
				  		. "&url=" . $yvComment->buildReturnURL(true, "yvComment" . $article->id) ;
							if ($nCommentsTotal == 0) {
				  			$link .= "&task=adddisplay";
							}
							// 2009-03-15 This line was uncommented for Joomla! 1.5.9 - JRoute is fixed?!
							$linkOn = JRoute::_($link . '#yvComment'  . $article->id);
							//Was:
							// $linkOn = JRoute::_($link) . '#yvComment' . $article->id;
							
							// 2009-07-11 There is Joomla! bug in v.1.5.12:
							// JURI class remembers the 'fragment' of the processed URI and uses it
							// in subsequent calls to build URIs...
							// So let's clear this fragment  
							$uri =& JFactory::getURI();
							$uri->setFragment('');
							
				  	  break;
				  	case 'article':
				  	  $linkOn = $yvComment->ContentIDToURL($ArticleID, true, null, false, 'yvComment' . $article->id);
				  	  break;
				  	} 
					}			    			    
	  			break;
	  		default:
			    $nCommentsTotal = $yvComment->getNComments($ArticleID);
			    $ShowNumberOfCommentsAlone = ($nCommentsTotal > 0); 
	  	}	
		} 

		if ( $yvComment->DisplayTo() == 'component'	) {
			// Comments should be preceded by title of commented artile
		  $ShowArticleTitle = true;
			$ShowComments = true;
		}

		if ( $yvComment->ParentView() == 'article'
			|| $ShowArticleTitle	) {
		  $ParentArticle =& $this->get('ParentArticle');
		  $ShowParentArticle = (boolean) $ParentArticle;
		}

    switch ($task) {
      case 'add':
      case 'addpreview':
      case 'deletedisplay':
      case 'edit':
      case 'editdisplay':
      case 'editpreview':
      	// Not for 'adddisplay'
				$ShowPreview = true;
      case 'adddisplay':
        // Don't waste time to load other comments of the article
				$ShowComments = false;
				$ShowArticle = $ShowArticleTitle;
				//$message[] = 'Show current only, task=' . $task;
				break;
  		default:
    } 	  

    if (($MayAdd || $MayUpdate) && !$print) {
			if ( $WhatToShow == "comment") {
		    switch ($task) {
		      case 'add':
		      case 'edit':
		      	break;
		  		default:
		   			$ShowForm = true;
		    } 	  
	    }
    }
    if ($ShowForm) {
			switch ($task) {
			  case 'viewdisplay' :
					// Do we need to 'hide' the form?
				  $task_next = "add";
				  if ($yvComment->DisplayTo() != 'component') {
		  			switch ($yvComment->getPageValue('addform_position', 'below')) {
		  				case 'separate_below':
		  				case 'separate_above':
						  	$task_next = "adddisplay";
		  			} 
				  }
				  break;
				case 'adddisplay' :
				case 'addpreview' :
				  $task_next = "add";
				  break;
				case 'editdisplay' :
				case 'editpreview' :
				  $task_next = "edit";
				  break;
				case 'deletedisplay' :
				  $task_next = "delete";
				  break;
				default :
				  $message[] = '?? task="'. $task . '"<br />';
				  $task_next = "viewdisplay";
			}
    	
			switch ($task_next) {
    		case 'delete' :
				case 'adddisplay' :
					break;
				default;
					// Fields to edit comments will be shown
					$yvComment->setShowLogo(true);
    	
					$editor_type = $yvComment->getConfigValue('editor_type', '');
					if ($editor_type == 'wysiwyg') {
						// Load the JEditor object
						$editor =& JFactory::getEditor();
					}
    	}	
    }

		if ($yvComment->_debug) {
	  	$message[] = 'task=' . $task . '; ' . '-> ' . $task_next . ';';
    	$message[] = 'ShowForm=' . ((boolean) $ShowForm);
    	$message[] = 'ShowComments=' . ((boolean) $ShowComments);
		}

    if ($ShowPreview || $ShowForm) {
			// Get information about current item from model
	  	$CurrentItem =& $this->get('CurrentItem');
	  	if (!$CurrentItem) {
				$ShowPreview = false;
		  } 	  
  	}
		if ($yvComment->_debug) {
	  	$message[] = 'CurrentItem:"' . print_r($CurrentItem, true) . '"';
		}	
  	

    if ((!$MayAdd) && $AddEnabledForArticle && $yvComment->UserIsGuest()) {
	    if ( ($DisplayTo == 'plugin' || $DisplayTo == 'module') && 
	         $yvComment->ParentView() == 'article'
	      	) {
		  	$ShowPleaseRegister = $yvComment->getConfigValue('show_please_register', false);
	    }
    }

		$message = array_merge ($message, $this->get('Message'));

    $ShowTemplate = ($ShowArticleTitle || $ShowParentArticle || $ShowNumberOfCommentsAlone 
    	|| $ShowComments || $ShowPreview || $ShowForm || $ShowPleaseRegister
    	|| (count($message)>0) );
    if (!$ShowTemplate) {
      if ($DisplayTo == 'component') {
      	$message[] = 'Nothing to show in yvComment?? InstanceInd=' . $yvComment->_Ind . '; task=' . $task . '; ArticleID=' . $ArticleID . '; yvCommentID=' . $this->get('yvCommentID') . '; nCommentsTotal=' . $nCommentsTotal . '; DisplayTo=' . $DisplayTo;
      	$ShowTemplate = true;
      }
    }
		if (!$ShowTemplate) {
			return;
		}
		if ( count($message)>0) {
			$yvComment->setShowLogo(true);
		}

		$this->assignRef('print', $print);
		$this->assignRef('user',		$user);
		$this->assign('task' , $task);
		$this->assign('task_next' , $task_next);
		$this->assign('ArticleID' , $ArticleID);
		$this->assignRef('ParentArticle',	$ParentArticle);
	  $this->assign('nCommentsTotal', $nCommentsTotal);
	  $this->assign('CommentsAreClosed', $CommentsAreClosed);

		// These unset are essential for recursion of this view
		unset($this->editor);
		unset($this->numcomments_link);
		unset($this->CurrentItem);
		unset($this->params);
		
		if ($editor) {
			$this->assignRef('editor',	$editor);
		}	
		if ($linkOn) {
			$this->assign('numcomments_link' , $linkOn);
		}	
		if ($CurrentItem) {
			$this->assignRef('CurrentItem' , $CurrentItem);
		}
		
		$this->assign('message' , $message);

		$params->def('allow_html_edit_text', $yvComment->getConfigValue('allow_html_edit_text', 'no'));

		// 2007-09-14 yvolk - Use Captcha for guests only!
		$use_captcha = 0;
		$delay_captcha_image = 0;
		$use_openid = $yvComment->getConfigValue('use_openid', '0');
    if (!$yvComment->UserIsRegistered()) {
			// These parameters are not for registered users:	    	
			$use_captcha = $yvComment->getConfigValue('use_captcha', '0');
			$delay_captcha_image = $yvComment->getConfigValue('delay_captcha_image', '1');
    }
    if ($yvComment->UserIsGuest() && $use_openid) {
			if ( !JPluginHelper::isEnabled('authentication', 'openid')) {
	  		$message[] = 'openid plugin is not enabled??';
			}
    }
    
		$params->set('yvcomment_use_captcha', $use_captcha);
		$params->set('yvcomment_delay_captcha_image', $delay_captcha_image);
		$params->set('yvcomment_use_openid', $use_openid);

		$params->def('guest_email_required', $yvComment->getConfigValue('guest_email_required', '0'));
		$params->def('author_linkable', $yvComment->getConfigValue('author_linkable', '0'));
		$params->def('allow_guest_link', $yvComment->getConfigValue('allow_guest_link', '0'));

		$params->def('execute_content_plugins', $yvComment->getConfigValue('execute_content_plugins', '0'));
		$params->def('use_bbcode_form', $yvComment->getConfigValue('use_bbcode_form', '0'));
		$params->def('use_smiley_form', $yvComment->getConfigValue('use_smiley_form', '0'));
		
		$params->def('orderby_pri', $yvComment->getConfigValue('orderby_pri'));

		$params->set('ShowArticleTitle', $ShowArticleTitle);
		$params->set('ShowArticle', $ShowArticle);
		$params->set('ShowParentArticle', $ShowParentArticle);
		$params->set('ShowNumberOfCommentsAlone', $ShowNumberOfCommentsAlone);
		$params->set('ShowComments', $ShowComments);
		$params->set('ShowPreview', $ShowPreview);
		$params->set('ShowForm', $ShowForm);
		$params->set('ShowPleaseRegister', $ShowPleaseRegister);

		$params->def('date_format', JText::_('DATE_FORMAT_LC2'));
		$this->assignRef('params',	$params);
		
  	//echo 'yvcommentViewComment task="' . $task . '"<br/>';

		//return parent::display($tpl);
		$result = $this->loadTemplate($tpl);
		if (JError::isError($result)) {
			return $result;
		}
    if ($this->_doEcho) {
		  echo $result;
		}
	}
}
?>
