<?php
/**
* yvComment - A User Comments Component, developed for Joomla 1.5
* @version 1.11.0
* @package yvComment
* @(c) 2007 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

	global $mainframe;
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
		$Ok = $yvComment->VersionChecks();
	}
	
	if (!$Ok) {
		$mainframe->enqueueMessage(
			'<strong>Error!</strong> yvComment component was not installed properly.' 
			. ' Please see the <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html"'
			. ' target="_blank">yvComment\'s Homepage</a>.',
		  'error');
	}
	if ($Ok) {
  	$parms = null;	
   	$InstanceInd = $yvComment->BeginInstance('component', $parms);

		$config = array ();
		$config['base_path'] = JPATH_SITE_YVCOMMENT;
		 		
		// Create the controller
		$controller = new yvcommentController($config);
		
		// Perform the Request task
		$controller->execute(JRequest::getVar('task'));
		
		// Redirect if set by the controller
		$controller->redirect();

  	$yvComment->EndInstance($InstanceInd);
  }  
?>