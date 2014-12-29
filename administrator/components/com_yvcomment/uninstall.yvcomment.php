<?php
/** * yvComment - A User Comments Component, developed for Joomla 1.5 
* @version 1.4.0 
* @package yvComment 
* @(c) 2007 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 

defined( '_JEXEC') or die('Restricted access'); // no direct access

function com_uninstall()
{
	global $mainframe;
	$yvComment = null;
	$Ok = false;
	$db =& JFactory::getDBO();

	$message = '';

	// Initialize yvComment solution
	if (!class_exists('yvCommentHelper')) {
		$path = JPATH_SITE . DS . 'components' . DS . 'com_yvcomment' . DS . 'helpers.php';
		if (file_exists($path)) {
		  require_once ($path);
		}
	}
	if (class_exists('yvCommentHelper')) {
  	$yvComment = &yvCommentHelper::getInstance();
		$Ok = $yvComment->Ok();
	}
	if (!$Ok) {
		$message = '<strong>Error!</strong> yvComment component was not installed properly. Please see the <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">yvComment\'s Homepage</a>.';
		$mainframe->enqueueMessage($message, 'error');
	}
	if ($Ok) {
		$db = & JFactory :: getDBO();
		$TableName = $db->replacePrefix('#__yvcomment');
	  if ($yvComment->TableExists($TableName)) {
	    $deleted = false;	
	  	
	    $nRecords = $yvComment->DLookup_db($db, 'COUNT(*)',$TableName);
	    if ($nRecords == 0) {
				$query = 'DROP TABLE ' . $TableName ;
				$db->setQuery($query);
				$Ok = $db->query();
			  if ($Ok) {
					$mainframe->enqueueMessage(
					  str_replace( '%1', $TableName, JText::_( 'TABLE_YVCOMMENT_WAS_DELETED')),
					  'message');
			  	$deleted = true;
			  } else {
				  $mainframe->enqueueMessage($yvComment->printDbErrors($db), 'error');
			  }
	    }
			if (!$deleted) {
				$mainframe->enqueueMessage(
				  str_replace( '%2', $nRecords, str_replace( '%1', $TableName, JText::_( 'TABLE_YVCOMMENT_WAS_NOT_DELETED'))),
				  'notice');
			}
	  }
?>
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'DESCRIPTION'); ?></legend>
		<table class="admintable" width="100%">
			<tr><td>
				<p>Thank you for using yvComment!</p>
				<p>For the latest and complete information about this extension see 
			    <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">yvComment Home Page</a>.
			  </p> 
			</td>
			</tr>
		</table>
	</fieldset>
	<?php echo $yvComment->Credits();
	}
	return true;
} ?>