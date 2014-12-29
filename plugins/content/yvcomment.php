<?php

/**
* yvComment plugin, part of yvComment - the first native Joomla! 1.5 Commenting Solution
* @version 1.23.0
* @package yvCommentPlugin
* @(c) 2007-2009 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

if (strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'HEAD') {
	// hide, cause something works wrong in this case	
} else {

	global $mainframe;
	$_ExtensionName = 'yvCommentPlugin';
	// Main Release Level. Extensions for the same Release are compatible
	$_Release = '1.24';
	$Ok = false;

	// Initialize yvComment solution
	if (!class_exists('yvCommentHelper')) {
		$path = JPATH_SITE . DS . 'components' . DS . 'com_yvcomment' . DS . 'helpers.php';
		if (file_exists($path)) {
		  require_once ($path);
		}
	}
	if (class_exists('yvCommentHelper')) {
  	$yvComment = &yvCommentHelper::getInstance();
		$Ok = $yvComment->VersionChecks($_ExtensionName, $_Release);
	} else {
		// No yvComment Component
	  $mainframe->enqueueMessage(
		  $_ExtensionName . ' is installed, but "<b>yvComment Component</b>" is <b>not</b> installed. Please install it to use <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">yvComment solution</a>.<br/>' . '(yvComment Plugin version="' . yvCommentPluginVersion . '")',
	  	'error');
	}

	if ($Ok) {
		$yvComment->ContentPluginsImported = true;    	
		// Import library dependencies
		jimport('joomla.event.plugin');
	}
}

class plgContentyvcomment extends JPlugin {

  // Normal positions:
  //		'InsideBox', 'AfterContent', 'DefinedByArticleTemplate' 
  // Error conditions:
  //		'DifferentVersions', 'NoComponent'
  var $_PluginPlace = 'NoComponent';
  
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function plgContentyvcomment(& $subject, $config) {
		parent :: __construct($subject, $config);
		global $mainframe;
		$yvComment = &yvCommentHelper::getInstance();

		//echo 'yvcomment Plugin constructor, subject="' . $subject->toString() . '"<br/>';

    if ($yvComment) {
      if ($yvComment->Ok()) {
				// Should we hide yvComment from this view?
				$paramName = 'comments_position';
				$view = JRequest::getCmd('view');
				switch($view) {
					case 'article':
						$paramName = 'comments_position_article_view';
						break;
					case 'frontpage':
						$paramName = 'comments_position_frontpage';
						break;
				}
			  $PluginPlace = $yvComment->getConfigValue($paramName, 'AfterContent');

			  // Show comments on print page of the article, even if it set to 'hide'
			  if ($view == 'article' && $PluginPlace == 'hide') {
		      $print = JRequest::getBool('print');
		      if ($print) {
			  		$PluginPlace = 'AfterContent';
		      }
			  }

			  $this->_PluginPlace = $PluginPlace;
      }  
		}
		//echo 'position="' . $this->_PluginPlace . '"<br/>';
	}

	// This is the first stage in preparing content for output and is the most common point
	// for content orientated plugins to do their work.
	// Since the article and related parameters are passed by reference,
	// event handlers can modify them prior to display
	function onPrepareContent(& $article, & $params, $page = 0) {
		$Ok = true;
		//echo 'yvcomment onPrepareContent place="' . $this->_PluginPlace . '"<br/>';
		//$article->text .= '<hr>test line<br/>';
		//echo '<hr>Article text 1 ="' . $article->text . '"<br/>';
			
		switch ($this->_PluginPlace) {
			case 'hide' :
			case 'InsideBox' :
			case 'DefinedByArticleTemplate' :
				$Ok = $this->_PluginFunction('onPrepareContent', $article, $params, $page);
				break;
		}
		return $Ok;
	}

	// Information that should be placed immediately after the generated content
	function onAfterDisplayContent(& $article, & $params, $page = 0) {
		$results = '';	
		//echo 'yvcomment onAfterDisplayContent place="' . $this->_PluginPlace . '"<br/>';
		switch ($this->_PluginPlace) {
			case 'AfterContent' :
				$results = $this->_PluginFunction('onAfterDisplayContent',  $article, $params, $page);
				break;
		}
		return $results;
	}

	function _PluginFunction( $EventName, & $article, & $params, $page = 0) {
  	static $level = 0; 
		$yvComment = &yvCommentHelper::getInstance();
		
		$retval = true;
		switch ($EventName) {
			case 'onAfterDisplayContent' :
				$retval = null;
				break;
		}
		$InstanceInd = -1;
		$strOut = '';
    //echo '_PluginFunction: ' . print_r($article, true) . '<br />';
		
		// Hide this plugin for known incompatible components
		global $option;
		$IncompatibleComponents = array( 
			'com_incompatiblewithyvcomment', 
			'com_someotherincompatible' );
		if (in_array($option, $IncompatibleComponents)) {
   		echo 'The component "' . $option . '" is incompatible with yvComment plugin';
			return $retval;
		}
		
		$ArticleID = 0;
		if (is_object( $article )) {
			if (isset( $article->id ))
			{
				$ArticleID = intval($article->id);
			}
		}
		//$strOut .= '<p>_PluginFunction PluginPlace="' . $this->_PluginPlace . '"; ArticleID=' . $ArticleID . '</p>';

    $viewName = '';
		if (is_object( $params )) {
			$viewName = $params->get('yvcomment_view','comment');
		}
		if ($viewName == 'none') {
			return $retval;
		}
		
		// For now, only one view for plugin
    //$viewName = 'comment';

		if ($ArticleID == 0) {
			//global $mainframe;
			//$message = 'yvComment plugin was called with this:<br />' . print_r($article, true);
			//$mainframe->enqueueMessage($message, 'notice');
			return $retval;
		}
		if ($level > 1) {
			// Do we need to make yvComment code more reenterable?		
			return $retval;
		}
		$level += 1;

  	$InstanceInd = $yvComment->BeginInstance('plugin', $params);

		$task = 'viewdisplay';
		
		//Layout for plugin may be requested by this variable:		
		$layoutName = JRequest::getVar('yvcomment_layout', 'default');
    JRequest::setVar('layout', $layoutName);
		
		//$strOut .= ', ArticleID=' . $ArticleID;
		$yvComment->setFilterByContext('article');
		$yvComment->setArticle($article);
		
	  //global $mainframe;
		//$message = print_r($yvComment->getArticleID(), true);
		//$mainframe->enqueueMessage($message, 'notice');

		if ($this->_PluginPlace != 'hide') {
			$config = array ();
			$config['task'] = $task;
			$config['view'] = $viewName;
	
			// This is needed only because we can't 'undefine' this:
			//define( 'JPATH_COMPONENT',					JPATH_BASE.DS.'components'.DS.$name);
			$config['base_path'] = JPATH_SITE_YVCOMMENT;
	
			// Create the controller
			$controller = new yvcommentController($config);
	
			// Perform the Request task
			$controller->execute($task);
	
			$strOut .= $controller->getOutput();
		}	

		$level -= 1;
		switch ($this->_PluginPlace) {
			case 'AfterContent' :
				$retval = $strOut;
				break;
			case 'DefinedByArticleTemplate' :
			  // Article (blog, section...) template will put this
			  // to the desired place
				$article->comments = $strOut;
				break;
			case 'hide' :
			default :
				$article->text .= $strOut;
		}
  	$yvComment->EndInstance($InstanceInd);
		return $retval;
	}
}
?>