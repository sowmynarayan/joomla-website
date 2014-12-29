<?php
/**
* yvComment - A User Comments Component, developed for Joomla 1.5
* @version		$Id: install.yvcomment.php 2 2009-08-30 15:10:43Z yvolk $
* @package yvComment
* @(c) 2007-2008 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/

defined('_JEXEC') or die('Restricted access'); // no direct access

function com_install() {
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
		$Ok = $yvComment->VersionChecks('', '', true);
	}
	
	if (!$Ok) {
		$mainframe->enqueueMessage(
			'<strong>Error!</strong> yvComment component was not installed properly.' 
			. ' Please see the <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html"'
			. ' target="_blank">yvComment\'s Homepage</a>.',
		  'error');
		return false;
	}

	$db = & JFactory :: getDBO();
	$TableName = $db->replacePrefix('#__yvcomment');
  if ($yvComment->TableExists($TableName)) {
  	//($error->get('level') == E_NOTICE) ? 'notice' : 'error';
		$mainframe->enqueueMessage(
		  str_replace( '%1', $TableName, JText::_( 'TABLE_YVCOMMENT_ALREADY_EXISTS')),
			'notice');
  } else {
	 	$query = 'CREATE TABLE #__yvcomment (LIKE #__content)';

	 	// This code is for MySQL v < 4.1
	 	// from http://www.joomlagate.com/component/option,com_smf/Itemid,31/topic,868.msg3483/
		//$FromTableName   = $db->replacePrefix('#__content'); 
		//$FromTableResult = $db->getTableCreate(array($FromTableName)); 
		//$query           = str_replace($FromTableName, $TableName, $FromTableResult[$FromTableName]); 
	 	
		$db->setQuery($query);
		$Ok = $db->query();
	  if (!$Ok) {
		  $mainframe->enqueueMessage($yvComment->printDbErrors($db), 'error');
	  }
  }

	// This is a hack in order to see views of this extension in the "New menu item type" window
	// This hack is not needed if extension has backend menu item.
	//$query = "UPDATE `#__components` SET link='" . "option=com_yvcomment" . "' WHERE `option` = 'com_yvcomment'";
	//$db->setQuery($query);
	//$Ok = $db->query();

	if (!$Ok) {
		//JError :: raiseError(500, $db->stderr());
		return false;
	}
?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'DESCRIPTION'); ?></legend>
		<table class="admintable" width="100%">
			<tr><td>
				
	  <p>yvComment is Commenting solution for Joomla! 1.5.7+ only!
	   (Detected Joomla! version="<?php echo $yvComment->JoomlaShortVersion(); ?>")</p>
		<p>This package is "yvComment Component" part of yvComment solution.</p>
	  <p>It is required to install both "yvComment Component" and "yvComment Plugin".</p>
		<p>For the latest and complete information about this extension see 
	    <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">yvComment's Homepage</a>.</p> 
			</td>
			</tr>
		</table>
	</fieldset>
	<?php echo $yvComment->Credits(); ?>
<?php
	return true;
}
?>