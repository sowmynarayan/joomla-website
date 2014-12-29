<?php
/**
* yvComment - A User Comments Component, developed for Joomla 1.5
* @version 1.0.0
* @package yvComment
* @(c) 2007 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/

jimport('joomla.application.component.controller');

class yvcommentController extends JController
{

  // Button, pressed on the form
  var $_button = '';
  var $_yvCommentID = 0;
  var $_created_by_alias = '';
  var $_created_by_link = '';
  var $_created_by_email = '';
  var $_title = '';
  var $_text = '';
  var $_state = 0;
  
  var $_model = null;
  var $_view = null;

	
	var $_AccessEnabled = false;
  
	function __construct( $config = array() )
	{
		global $mainframe;
	  $yvComment = &yvCommentHelper::getInstance();
		$message = '';
	  $Ok = true;
		// Content state filter
		$filter_state		= 'P';
	
		//Joomla doesn't check Access level of component, so let's do it ourselves.
		$user =& JFactory::getUser();
		if ($yvComment->getConfigValue('access', 0) <= $user->get('aid', 0)) {
			$this->_AccessEnabled = true;
		} else {
		  echo JText::_('ACCESS_DENIED') . ' User_aid=' . $user->get('aid', 0) . '<br />';
		  return;
		}
    $params =& $yvComment->PageParameters(); 

		$viewName	= '';
		if ( isset( $config['view'] ) ) {
	  	$viewName = $config['view'];
	  	unset( $config['view'] );
		} else {
			$viewName	= JRequest::getVar( 'view', 'unknown');
		}
    //echo 'viewName=' . $viewName . '<br/>';
		$Ok = ($viewName != 'none');

		if ($Ok) {
			$task = '';
			if ( isset( $config['task'] ) ) {
		  	$task = $config['task'];
		  	unset( $config['task'] );
			} else {
			  $task	= JRequest::getVar( 'task', '');
			}
	  	if (empty($task)) {
				$task = 'viewdisplay';
	  	}
		
			// ToDo: Is this optimal? 	
		  $document =& JFactory::getDocument();
		  $viewType	= $document->getType();
	
			// Correct viewName
			switch ($viewName) {
	    	case 'comment':
	    	case 'listofcomments' :	
	    	case 'mostcommented' :	
			    break;
				default:
		      // Set default view name
					switch ($yvComment->DisplayTo()) {
					  case 'module' :
		      		$viewName = 'listofcomments';
		      		break;
					  case 'plugin' :
		      		$viewName = 'comment';
		      		break;
		      	default:	
		      		$viewName = 'listofcomments';
					}
			}
			$yvComment->setViewName($viewName);
	
	    parent::__construct($config);
	
			$Ok = $yvComment->FindContext();
      //echo 'setArticle: ' . print_r($yvComment->_CategoryID, true) . '; User="' . print_r($user, true) . '"' . '; Ok="' . $Ok . '"<br />';
		}

		if ($Ok && $viewName == 'comment') {
	  	if ($yvComment->IsIdShown($yvComment->getArticleID(), false)) {
	  		// Info about this article was shown already, so hide this second duplicate.
				if ($yvComment->_debug) {
	      	echo '<hr />Info about this article was shown already, so hide this second duplicate; ArticleID=' . $yvComment->getArticleID() . '<hr />';
				}	
	  		$Ok = false;
	  	} 
	  }

		if ($Ok) {
	    $context = 'yvComment_' . $viewName . '_';

			// Content state filter
			if ($yvComment->UserCanEdit() == 'all') {
		    // Code from "administrator/components/com_content/controller.php"
				$filter_state		= $mainframe->getUserStateFromRequest( $context.'filter_state',		'filter_state',		'',	'word' );
			}
		  //echo 'filter_state="' . $filter_state . '"<br />';
		}	

		if ($Ok && $task != 'viewdisplay') {
	    $this->_button = JRequest::getVar('button', '', '', 'STRING');
		  
		  $this->_created_by_alias = trim( JRequest::getVar( 'created_by_alias', '', '', 'STRING'));
		  $this->_created_by_link = JRequest::getVar( 'created_by_link', '', '', 'STRING');
		  $this->_created_by_email = JRequest::getVar( 'created_by_email', '', '', 'STRING');
		  $this->_title = JRequest::getVar( 'title', '', '', 'STRING');
	
			//JREQUEST_ALLOWRAW , JREQUEST_ALLOWHTML
			switch ($yvComment->getConfigValue('allow_html_edit_text', 'no')) 
			{
				case 'allowhtml' :	
					// This doesn't work with unclosed tags:
				  $mask = JREQUEST_ALLOWHTML;
				  break;
				case 'allowraw' :	
					$mask = JREQUEST_ALLOWRAW;
				  break;
			  default :
					$mask = 0;
			}
			$this->_text	= JRequest::getVar('text', '', '', 'STRING', $mask);
		  //echo 'mask:'. $mask . ';text: "' . $this->_text . '"<br/>';
		  //$this->_text	= 'changed <hr>';
			$this->_state	= intval(JRequest::getInt('state', $this->_state));
	 	
	 	  $this->_yvCommentID = intval(JRequest::getInt( 'yvCommentID', 0));
	 	  //echo 'yvComment_p001; task="' . $task . '"; ArticleID="' . $yvComment->getArticleID() . '"<br />';
		  //echo 'yvCommentID="' . $this->_yvCommentID . '"<br />';
		}

		if ($Ok) {
	    if ($yvComment->getConfigValue('execute_content_plugins', '0')) {
	    	if (!$yvComment->ContentPluginsImported) {
	    		$yvComment->ContentPluginsImported = true;    	
					JPluginHelper::importPlugin('content');
					//$mainframe->enqueueMessage('Importing content plugins',	'notice');
	    	}    
	    }
		}	

		if ($Ok) {
		  $model = & $this->getModel( $viewName );
			if ($model)	{
				$model->setParms($this->_yvCommentID, $task, $filter_state);
			}
	    $this->_view = & $this->getView($viewName, $viewType, '', $config);
		}
		if (!$Ok) {
			$this->_AccessEnabled = false;
		}

		if (strlen($message) > 0) {
			global $mainframe;
			$mainframe->enqueueMessage('yvcommentController: <br />' . $message . '<br />', ( $Ok ? 'notice' : 'error'));
		}

		return;
	}

  function execute( $task ){
		$return = null;  	
    if ($this->_AccessEnabled) {
      $return = parent::execute( $task );			    	
    }
    return $return;
  }
	
	function displaycaptcha() {
		global $mainframe;
		$yvComment = &yvCommentHelper::getInstance();
		if ($yvComment->getConfigValue('use_captcha', 0)) {
			$Ok = false;
			$mainframe->triggerEvent('onCaptcha_Display', array($Ok));
			if (!$Ok) {
				echo "<br/>Error displaying Captcha<br/>";
			}		
		}
	}
	
	/**
	 * Inserts Comment to the database
	 */
	function add()
	{
		global $mainframe;
		$yvComment = &yvCommentHelper::getInstance();
		$Ok = false;
		$IsPublished = false;
		do {
      //echo 'preview=' . JRequest::getVar('preview') . '<br />';
      if ($this->_button == 'preview') {
		    $this->_model->setTask('addpreview');
		    // Preview
        $this->_model->AddOrPreview($this->_title, $this->_text,
        			$this->_created_by_alias, $this->_created_by_link,
        			$this->_created_by_email,
        			true, $IsPublished);
        break;
      } elseif ($this->_button == 'post') {
        if (!$this->_model->AddOrPreview($this->_title, $this->_text,
        		 $this->_created_by_alias, $this->_created_by_link,
        		 $this->_created_by_email,
        		 false, $IsPublished)) {
			    $this->_model->setTask('addpreview');
	        break;
        }
        if (!$IsPublished) {
          // Don't redirect: show message before continue...	
	        break;
        }
      } 
      if  ($yvComment->getReturnURL()) {
	      //Close
		    $this->setRedirect(base64_decode($yvComment->getReturnURL()));
	      $this->Redirect();
	      return true;
      }

		  // Message is formed by the Model!
		  //setMessage(JText::_('COMMENT ADDED'));
		  $Ok = true;
		} while(0);
		
		$this->execute('display');
		return $Ok;
	}

	/**
	 * Edit Comment in the database
	 */
	function edit()
	{
		global $mainframe;
	  $yvComment = &yvCommentHelper::getInstance();	
		$Ok = false;
		do {
      //echo 'preview=' . JRequest::getVar('preview') . '<br />';
      if ($this->_button == 'preview') {
		    $this->_model->setTask('editpreview');
		    // Preview
        $this->_model->EditOrPreview($this->_title, $this->_text, 
                                     $this->_created_by_alias, $this->_created_by_link, 
                                     $this->_created_by_email, 
                                     $this->_state, true);
        break;
      } elseif ($this->_button == 'post') {
        if (!$this->_model->EditOrPreview($this->_title, $this->_text, 
        																	$this->_created_by_alias, $this->_created_by_link, 
        																	$this->_created_by_email, 
                                     			$this->_state, false)) {
			    $this->_model->setTask('editdisplay');
	        break;
        }
      }
      if  ($yvComment->getReturnURL()) {
	      //Close
		    $this->setRedirect(base64_decode($yvComment->getReturnURL()));
	      $this->Redirect();
	      return true;
      }

		  // Message is formed by Model!
		  //setMessage(JText::_('COMMENT_MODIFIED'));
		  //$this->setRedirect($url, JText::_('COMMENT_MODIFIED'));
		  $Ok = true;
		} while(0);
		
		$this->execute('display');
		return $Ok;
	}

	/**
	 * Delete/Trash Comment
	 */
	function delete()
	{
		global $mainframe;
	  $yvComment = &yvCommentHelper::getInstance();	
		$Ok = false;

		do {
		  $user		=& JFactory::getUser();
      if ($user->get('guest')) {
        JError::raiseError( 403, JText::_( 'ALERTNOTAUTH'));
	      break;
      }

      if ($this->_button == 'delete') {
        if (!$this->_model->Delete()) {
			    $this->_model->setTask('deletedisplay');
	        break;
        }
      }
      if  ($yvComment->getReturnURL()) {
	      //Close
		    $this->setRedirect(base64_decode($yvComment->getReturnURL()));
	      $this->Redirect();
	      return true;
      }

		  // Message is formed by Model!
		  //setMessage(JText::_('COMMENT_MODIFIED'));
		  //$this->setRedirect($url, JText::_('COMMENT_MODIFIED'));
		  $Ok = true;
		} while(0);
		
		$this->execute('display');
		return $Ok;
	} 
	
	/**
	 * Publish/Unpublish Comment
	 */
	function publish()
	{
		global $mainframe;
	  $yvComment = &yvCommentHelper::getInstance();	
		$Ok = false;

		do {
		  $user		=& JFactory::getUser();
      if ($user->get('guest')) {
        JError::raiseError( 403, JText::_( 'ALERTNOTAUTH'));
	      break;
      }

			if (!$this->_model->Publish($this->_state)) {
		    $this->_model->setTask('editdisplay');
		    break;
			}

      if  ($yvComment->getReturnURL()) {
	      //Close
		    $this->setRedirect(base64_decode($yvComment->getReturnURL()));
	      $this->Redirect();
	      return true;
      }

		  $Ok = true;
		} while(0);
		
		$this->execute('display');
		return $Ok;
	}

	/**
	 * Redirect to the plugin mode (return to the Article)
	 */
	function viewdisplay()
	{
		global $mainframe;
	  $yvComment = &yvCommentHelper::getInstance();	
		$Ok = false;
		if ($yvComment->DisplayTo() == 'component') {
	    if  ($yvComment->getReturnURL()) {
	      //Close
		    $this->setRedirect(base64_decode($yvComment->getReturnURL()));
	      $this->Redirect();
	      return true;
	    }
		}  
		
		$this->execute('display');
		return $Ok;
	}


	function &getModel( $name, $prefix = '' )
	{
		if (!$this->_model)	{
	    $this->_model = & parent::getModel( $name, $prefix );
		}
		return $this->_model;
	}
	
	function getOutput()
	{
    if ($this->_AccessEnabled) {
		  if ($this->_view) {
	  	  return $this->_view->getOutput();
		  }
	  	return JText::_('View object is not set');
    }
  }
}
?>
