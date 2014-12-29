<?php
/**
* @version		$Id: yvcommentspacer.php 2 2009-08-30 15:10:43Z yvolk $
* @package		yvComment
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Real need for this element is loading yvComment language file
 * To load all language strings, this 'parameter' should be
 *   the first in the list.
 */
class JElementYvcommentspacer extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Yvcommentspacer';

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	function __construct($parent = null) {
    parent::__construct($parent);

		// Let's do something custom...
		//global $mainframe;
		//$message = 'Class initialized: ' . get_class($this);
		//$mainframe->enqueueMessage($message, 'notice');

		if (!class_exists('yvCommentHelper')) {
			$path = JPATH_SITE . DS . 'components' . DS . 'com_yvcomment' . DS . 'helpers.php';
			if (file_exists($path)) {
			  require_once ($path);
			}
		}
		if (class_exists('yvCommentHelper')) {
			// Initialize yvComment, so Language file will be loaded...
			$yvComment = &yvCommentHelper::getInstance();
		}	
	}

	function fetchElement($name, $value, &$node, $control_name)
	{
		if (substr($name,0,1) != '@') {
			return JText::_($name) . '<hr />';
		} else {
			return '<hr />';
		}
	}
}