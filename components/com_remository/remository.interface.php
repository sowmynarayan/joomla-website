<?php

/**************************************************************
* This file is part of Remository
* Copyright (c) 2006 Martin Brampton
* Issued as open source under GNU/GPL
* For support and other information, visit http://remository.com
* To contact Martin Brampton, write to martin@remository.com
*
* Remository started life as the psx-dude script by psx-dude@psx-dude.net
* It was enhanced by Matt Smith up to version 2.10
* Since then development has been primarily by Martin Brampton,
* with contributions from other people gratefully accepted
*/

// Don't allow direct linking
// Note executable code here, and ALSO below the class definitions

if (!defined( '_VALID_MOS' ) AND !defined('_JEXEC')) die( sprintf ('Direct Access to %s is not allowed.', __FILE__ ));

if (!defined('_REMOSITORY_VERSION')) define('_REMOSITORY_VERSION', '3.52.8');

if (!defined('_JOOMLA_15PLUS') AND defined('_JEXEC') AND !defined('_ALIRO_IS_PRESENT')) define ('_JOOMLA_15PLUS', 1);

$adirectory = dirname(__FILE__);
if (!defined('REMOS_REMOSITORY_DIRECTORY')) define ('REMOS_REMOSITORY_DIRECTORY', $adirectory);
$adirectory = dirname($adirectory);
if (!defined('REMOS_COMPONENT_DIRECTORY')) define ('REMOS_COMPONENT_DIRECTORY', $adirectory);
$adirectory = dirname($adirectory);
if (!defined('_REMOS_ABSOLUTE_PATH')) define ('_REMOS_ABSOLUTE_PATH', $adirectory);

require_once(REMOS_REMOSITORY_DIRECTORY.'/com_remository_constants.php');
if (!defined('_ALIRO_IS_PRESENT')) {
	require_once(REMOS_REMOSITORY_DIRECTORY.'/remository.class.php');
	if (!defined('REMOSITORY_ADMIN_SIDE')) require_once(REMOS_REMOSITORY_DIRECTORY.'/remository.html.php');
	if (file_exists(_REMOS_ABSOLUTE_PATH.'/libraries/joomla/version.php')) {
		require_once(_REMOS_ABSOLUTE_PATH.'/libraries/joomla/version.php');
		if (class_exists('JVersion') AND !defined('_JOOMLA_15PLUS')) define ('_JOOMLA_15PLUS', 1);
	}
	if (!defined('_JOOMLA_15PLUS') AND file_exists(_REMOS_ABSOLUTE_PATH.'/includes/version.php')) {
		require_once (_REMOS_ABSOLUTE_PATH.'/includes/version.php');
		if (class_exists('joomlaVersion') AND !defined('_JOOMLA_10X')) define ('_JOOMLA_10X', 1);
		if (!defined('_JOOMLA_10X') AND class_exists('version')) {
			$mamboversion = new version();
			if ($mamboversion->RELEASE >= '4.6') {
				if (!defined('_MAMBO_46PLUS')) define ('_MAMBO_46PLUS', 1);
			}
			elseif (!defined('_MAMBO_45MINUS')) define ('_MAMBO_45MINUS', 1);
		}
	}
}
if (!defined('_JOOMLA_15PLUS') AND !defined('_ALIRO_IS_PRESENT') AND defined('_ISO')) $iso = split( '=', _ISO );
if (!defined('_CMSAPI_CHARSET')) {
	if (isset($iso[1])) define ('_CMSAPI_CHARSET', $iso[1]);
	else define ('_CMSAPI_CHARSET', 'utf-8');
}

if (!isset($GLOBALS['remositoryInterface'])) {

$GLOBALS['remositoryInterface'] = 1;

class remositoryDebug {

	function trace () {
	    static $counter = 0;
		$html = '';
		foreach(debug_backtrace() as $back) {
		    if (isset($back['file']) AND $back['file']) {
			    $html .= '<br />'.$back['file'].':'.$back['line'];
			}
		}
		$counter++;
		if (1000 < $counter) {
		    echo $html;
		    die ('Program killed - Probably looping');
        }
		return $html;
	}

}

if (defined('_JOOMLA_15PLUS') AND defined('REMOSITORY_ADMIN_SIDE')) {

	class remosMenuBar extends JToolBarHelper {
		function startTable () {
		}
		function endTable () {
		}
	}

	jimport('joomla.html.pagination');
	class remosPageNav extends JPagination {
		function writeLimitBox () {
			return $this->getLimitBox();
		}
		function writePagesLinks () {
			return $this->getPagesLinks();
		}
		function writePagesCounter () {
			return $this->getPagesCounter();
		}
		function listFormEnd ($pagecontrol=true) {
			$act = $_REQUEST['act'];
			$repnum = intval(isset($_REQUEST['repnum']) ? $_REQUEST['repnum'] : 1);
			$hiddenhtml = <<<HIDDEN_HTML
				
				<div>
					<input type="hidden" name="option" value="com_remository" />
					<input type="hidden" name="repnum" value="$repnum" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="limitstart" value="" />
					<input type="hidden" name="act" value="$act" />
					<input type="hidden" name="boxchecked" value="0" />
				</div>
				
HIDDEN_HTML;

			if ($pagecontrol) {
				$displaynum = _DOWN_DISPLAY_NUMBER;
				$links = $this->writePagesLinks();
				$limits = $this->writeLimitBox();
				$counter = $this->writePagesCounter();
				echo <<<PAGE_CONTROL1

			<tfoot>
			<tr>
	    		<td colspan="15">
	    		<del class="container"><div class="pagination">
					<div class="limit">$displaynum
						$limits
					</div>
					$links
					<div class="limit">
						$counter
					</div>
				</div></del>
				$hiddenhtml
				</td>
			</tr>
			</tfoot>

PAGE_CONTROL1;

			}
			else {
				echo <<<END_PAGE

			<tfoot>
			<tr>
	    		<th align="center" colspan="13">
	    			&nbsp;
	    			$hiddenhtml
				</th>
			</tr>
			</tfoot>

END_PAGE;

			}
		}
	}

	jimport('joomla.html.pane');
	//Force loading of JPane to compensate for un-clever autoload
	$dummy = new JPane();
	unset($dummy);
	class remosPane extends JPaneTabs {
		function startTab ($tabText, $tabid) {
			echo parent::startPanel ($tabText, $tabid);
		}
		function endTab () {
			echo parent::endPanel();
		}
		function startPane ($paneid) {
			echo parent::startPane($paneid);
		}
		function endPane () {
			echo parent::endPane();
		}
	}
}

if (defined('_JOOMLA_15PLUS')) {
	class remosDBTable extends JTable {
		function remosDBTable ($table, $key, $db) {
			$this->__construct ($table, $key, $db);
		}
	}

}
else {
	if (defined('REMOSITORY_ADMIN_SIDE')) {
		if (!defined('_ALIRO_IS_PRESENT')) {
			$remopath = str_replace('\\','/',dirname(__FILE__));
			$compath = dirname($remopath);
			$absolute_path = dirname($compath);
			require_once ($absolute_path.'/administrator/includes/menubar.html.php');
			require_once ($absolute_path.'/administrator/includes/pageNavigation.php');
		}
		
		class remosMenuBar extends mosMenuBar {}

		class remosPageNav extends mosPageNav {
			function listFormEnd ($pagecontrol=true) {
				$act = $_REQUEST['act'];
				if ($pagecontrol) {
					echo <<<PAGE_CONTROL1

			<tfoot>
			<tr>
	    		<th align="center" colspan="13">

PAGE_CONTROL1;
					$this->writePagesLinks();
					echo <<<PAGE_CONTROL2

			</th>
			</tr>
			<tr>
				<td align="center" colspan="13">

PAGE_CONTROL2;
					$this->writeLimitBox();
					$this->writePagesCounter();
					echo <<<PAGE_CONTROL3

			</td>
			</tr>

PAGE_CONTROL3;

				}
				else {
					echo <<<END_PAGE

			<tfoot>
			<tr>
	    		<th align="center" colspan="13">&nbsp;</th>
			</tr>

END_PAGE;

				}
				$repnum = intval(isset($_REQUEST['repnum']) ? $_REQUEST['repnum'] : 1);
				echo <<<HIDDEN_HTML
				
			<tr>
				<td>
					<input type="hidden" name="option" value="com_remository" />
					<input type="hidden" name="repnum" value="$repnum" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="act" value="$act" />
					<input type="hidden" name="boxchecked" value="0" />
				</td>
			</tr>
			</tfoot>
				
HIDDEN_HTML;

			}
		}

		class remosPane extends mosTabs {
			function remosPane () {
				parent::mosTabs(0);
			}
		}
	}


	class remosDBTable extends mosDBTable {
		function remosDBTable ($table, $key, $db) {
			$this->mosDBTable ($table, $key, $db);
		}
	}

}


class remositoryInterface {

	private $mainframe;
	private $absolute_path;
	private $live_site;
	private $cachepath;
	private $lang;
	private $sitename;
	private $currentUser = null;
	private $knownUsers = array();
	private $customTags = array();
	private $onecategory = false;

	function __construct () {
		$this->absolute_path = dirname(dirname(dirname(__FILE__)));
		$this->getMainFrame();
		if (defined('_JOOMLA_15PLUS')) {
			$this->live_site = substr(JURI::root(), 0, -1);
			$this->admin_site = $this->live_site.'/administrator';
			$lang = JFactory::getLanguage();
			$this->lang = strtolower($lang->get('backwardlang'));
			$this->cachepath = JPATH_CACHE;
		}
		if (!defined('_ALIRO_IS_PRESENT')) {
			if (function_exists('__autoload')) spl_autoload_register('__autoload');
			spl_autoload_register(array($this,'autoload'));
		}
		$lang = $this->getCfg('lang');
		if(file_exists($this->absolute_path.'/components/com_remository_files/custom.php')) require_once($this->absolute_path.'/components/com_remository_files/custom.php');
	}
	
	function loadLanguageFile () {
		$repository = remositoryRepository::getInstance();
		//Need config values for language files
		$lang = $repository->Force_Language ? $repository->Force_Language : $this->getCfg('lang');
		foreach (get_object_vars($repository) as $k=>$v) $$k = $repository->$k;
		$mosConfig_sitename = $this->getCfg('sitename');
		$mosConfig_live_site = $this->getCfg('live_site');
		if ('utf-8' == _CMSAPI_CHARSET AND file_exists($this->absolute_path.'/components/com_remository/language/utf.'.$lang.'.php')) require_once($this->absolute_path.'/components/com_remository/language/utf.'.$lang.'.php');
		elseif (file_exists($this->absolute_path.'/components/com_remository/language/'.$lang.'.php')) require_once($this->absolute_path.'/components/com_remository/language/'.$lang.'.php');
		require_once($this->absolute_path.'/components/com_remository/language/english.php');
	}

	function purify ($string) {
		return $string;
	}
	
	function autoload ($classname) {
		$explicit = array(
		'remositoryToolbar' => array('dir' => '/v-admin-classes/', 'file' => 'remositoryToolbar'),
		'remositoryAdminManager' => array('dir' => '/c-admin-classes/', 'file' => 'remositoryAdmin'),
		'remositoryAdminControllers' => array('dir' => '/c-admin-classes/', 'file' => 'remositoryAdmin'),
		'aliroAuthoriser' => array('dir' => '/aliro/', 'file' => 'remositoryAuthoriser'),
		'aliroAuthorisationAdmin' => array('dir' => '/aliro/', 'file' => 'remositoryAuthoriser'),
		'remositoryBasicHTML' => array('dir' => '/v-admin-classes/', 'file' => 'basicHTML'),
		'remositoryAdminHTML' => array('dir' => '/v-admin-classes/', 'file' => 'basicHTML'),
		'sef_remository' => array('dir' => '/', 'file' => 'sef_ext')
		);
		if (false !== strpos($classname, '://') OR false != strpos($classname, '..')) die ('Invalid class name for loading');
		if (isset($explicit[$classname])) {
			$dir = $explicit[$classname]['dir'];
			$file = $explicit[$classname]['file'];
		}
		elseif (0 === strpos($classname, 'remositoryAdmin')) $dir = '/c-admin-classes/';
		elseif (0 === strpos($classname, 'remository_plugin')) $dir = '/g-classes/';
		elseif (0 === strpos($classname, 'remository_')) $dir = '/c-classes/';
		else if (0 != strpos($classname, 'HTML')) {
			if (0 === strpos($classname, 'remository')) $dir = '/v-classes/';
			else $dir = '/v-admin-classes/';
		}
		elseif (0 === strpos($classname, 'remository')) $dir = '/p-classes/';
		elseif (0 === strpos($classname, 'mod_remository')) $dir = '/m-classes/';
		else return false;
		$file = REMOS_REMOSITORY_DIRECTORY.$dir.(empty($file) ? $classname.'.php' : $file.'.php');
		if (file_exists($file)) {
			require_once($file);
			return true;
		}
		return false;
	}
	
	public function setSingleCategory () {
		$this->onecategory = true;
	}
	
	public function isSingleCategory () {
		return $this->onecategory;
	}
    
	function class_exists ($string, $autoload=false) {
		if (PHP_VERSION >= '5') return class_exists($string, $autoload);
		return class_exists($string);
	}

	function getMainFrame () {
		if (!is_object($this->mainframe)) {
			if (!defined('_JOOMLA_15PLUS') AND method_exists('mosMainFrame', 'getInstance')) $this->mainframe = mosMainFrame::getInstance();
			else {
				global $mainframe;
				$this->mainframe = $mainframe;
			}
		}
	}

	public static function getInstance () {
        static $instance;
        if (!is_object($instance)) $instance = new remositoryInterface();
        return $instance;
    }

	function rawGetCfg ($string) {
		if (isset($this->$string)) return $this->$string;
		if (defined('_ALIRO_IS_PRESENT') OR defined('_JOOMLA_15PLUS')) return $this->$string = $this->mainframe->getCfg($string);
		if (method_exists($this->mainframe, 'getCfg')) {
			if ('admin_site' == $string) return $this->mainframe->getCfg('live_site').'/administrator';
			else return $this->mainframe->getCfg($string);
		}
		else {
			include ($this->absolute_path.'/configuration.php');
			$this->live_site = $mosConfig_live_site;
			// Fake $mosConfig_admin_site so rest of logic still works if request is for admin_site
			$this->admin_site = $mosConfig_admin_site = $this->live_site.'/administrator';
			$this->lang = $mosConfig_lang;
			$this->sitename = $mosConfig_sitename;
			$configitem = 'mosConfig_'.$string;
			$this->$string = $$configitem;
			return $$configitem;
		}
	}
	
	function getCfg ($string) {
		$result = $this->rawGetCfg($string);
		if ('live_site' == $string OR 'absolute_path' == $string OR 'admin_path' == $string) {
			if ('/' == substr($result,-1)) $result = substr($result,0,-1);
		}
		return $result;
	}

	function getTemplate () {
		return $this->mainframe->getTemplate();
	}

	function appendPathWay ($name, $link) {
		$repository = remositoryRepository::getInstance();
		if (0 == ($repository->Remository_Pathway & 1)) return;
		if (defined('_JOOMLA_15PLUS')) $this->mainframe->appendPathWay($name, $link);
		elseif (defined('_MAMBO_46PLUS')) {
			$pathway = mosPathway::getInstance();
			$pathway->addItem($name, $link);
		}
		else {
			$url = $this->sefRelToAbs($link);
			$url = preg_replace ('/\&([^amp;])/', '&amp;$1', $url);
			$this->mainframe->appendPathWay('<a href="'.$url.'">'.$name.'</a>');
		}
	}

	function getDB () {
		if (defined('_JOOMLA_15PLUS')) $database = JFactory::getDBO();
		elseif (remositoryInterface::class_exists('mamboDatabase', false)) $database = mamboDatabase::getInstance();
		else global $database;
		return $database;
	}

	function getEscaped ($string) {
		$database = $this->getDB();
		return $database->getEscaped($string);
	}

	function getUser () {
		if (!is_object($this->currentUser)) {
			if (defined('_JOOMLA_15PLUS')) $my = JFactory::getUser();
			elseif (method_exists('mamboCore','get')) {
				if (mamboCore::is_set('currentUser')) $my = mamboCore::get('currentUser');
				else $my = aliroUser::getInstance();
			}
			else global $my;
			$this->currentUser = new remositoryUser ($my->id,$my);
		}
		return $this->currentUser;
	}

	function getIdentifiedUser ($id) {
		if (isset($this->knownUsers[$id])) return $this->knownUsers[$id];
		if (defined('_JOOMLA_15PLUS')) {
			$my = new JUser($id);
			$this->knownUsers[$id] = $my;
			return $my;
		}
		$database = $this->getDB();
		$my = new mosUser($database);
		$my->load($id);
		$this->knownUsers[$id] = $my;
		return $my;
	}

	function getCurrentItemid () {
		if (method_exists('mamboCore','get')) $Itemid = mamboCore::get('Itemid');
		else global $Itemid;
		return intval($Itemid);
	}

	function getUserStateFromRequest ($var_name, $req_name, $var_default=null) {
		$this->getMainFrame();
		$mainframe = $this->mainframe;
		if (isset($var_default) AND is_numeric($var_default)) $forcenumeric = true;
		else $forcenumeric = false;
		if (isset($_REQUEST[$req_name])) {
			if ($forcenumeric) $mainframe->setUserState($var_name, intval($_REQUEST[$req_name]));
			else $mainframe->setUserState($var_name, $_REQUEST[$req_name]);
		}
        elseif (isset($var_default) AND !isset($mainframe->userstate[$var_name])) $mainframe->setUserState($var_name, $var_default);
        return $mainframe->getUserState($var_name);
	}

	function getPath ($name, $option='') {
		if (defined('_JOOMLA_15PLUS')) return JApplicationHelper::getPath($name, $option);
		$this->getMainFrame();
		return $this->mainframe->getPath($name, $option);
	}

	function setPageTitle ($title) {
		$this->getMainFrame();
		if (method_exists($this->mainframe, 'SetPageTitle')) $this->mainframe->SetPageTitle($title);
		if (defined('_JOOMLA_15PLUS') AND $this->getCfg('sef')) {
			// $this->appendPathway($title, remositoryRepository::getInstance()->RemositoryBasicFunctionURL());
		}
	}

	function prependMetaTag ($tag, $content) {
		$this->getMainFrame();
		if (method_exists($this->mainframe, 'prependMetaTag')) $this->mainframe->prependMetaTag($tag, $content);
	}

	function addCustomHeadTag ($tag) {
		if (in_array($tag, $this->customTags)) return;
		$this->getMainFrame();
		$this->mainframe->addCustomHeadTag($tag);
		$this->customTags[] = $tag;
	}

	function addMetaTag ($name, $content, $prepend='', $append='') {
		$this->getMainFrame();
		$this->mainframe->addMetaTag($name, $content, $prepend='', $append='');
	}

	function redirect ($url, $msg='') {
    	if (defined('_JOOMLA_15PLUS')) $this->mainframe->redirect($url, $msg);
    	else mosRedirect($url, $msg);
    }

    function makePageNav ($total, $limitstart, $limit) {
		$pagenav = new remosPageNav($total, $limitstart, $limit);
    	return $pagenav;
    }

    function triggerMambots ($event, $args=null, $doUnpublished=false) {
    	if (defined('_JOOMLA_15PLUS')) {
    		if (!defined('REMOSITORY_ADMIN_SIDE')) {
	    		JPluginHelper::importPlugin('content'); 
	    		JPluginHelper::importPlugin('remository');  
    		}
    		$handler = JDispatcher::getInstance();
    	}
    	elseif (defined('_ALIRO_IS_PRESENT')) $handler = aliroMambotHandler::getInstance();
    	else {
	    	global $_MAMBOTS;
    		$handler = $_MAMBOTS;
    		if (!defined('REMOSITORY_ADMIN_SIDE')) {
	    		$handler->loadBotGroup('content');
    			$handler->loadBotGroup('remository');
    		}
    	}
    	return $handler->trigger($event, $args, $doUnpublished);
    }

    function initEditor () {
    	if (defined('_JOOMLA_15PLUS')) {
			$editor = JEditor::getInstance();
			$editor->initialise();
    	}
    	else initEditor();
    }

    function getEditorContents ($hiddenField) {
    	if (defined('_JOOMLA_15PLUS')) {
    		$editor = JFactory::getEditor();
    		$editor->getContent ($hiddenField);
    	}
    	else getEditorContents ($hiddenField, $hiddenField);
    }

	function editorArea($name, $content, $hiddenField, $width, $height, $col, $row) {
		echo $this->editorAreaText($name, $content, $hiddenField, $width, $height, $col, $row);
	}

	function editorAreaText ($name, $content, $hiddenField, $width, $height, $col, $row) {
		if (defined('_JOOMLA_15PLUS')) {
			$editor = JFactory::getEditor();
			// Last parameter suppresses buttons
			return $editor->display($hiddenField, $content, $width, $height, $col, $row, false);
		}
		else {
			$results = $this->triggerMambots('onEditorArea', array( $name, $content, $hiddenField, $width, $height, $col, $row ) );
			$html = '';
			foreach ($results as $result) $html .= trim($result);
			return $html;
		}
	}

	function objectSort ($objarray, $property, $direction='asc') {
		$GLOBALS['remositorySortProperty'] = $property;
		$GLOBALS['remositoryDirection'] = strtolower($direction);
		usort( $objarray, create_function('$a,$b','
	        global $remositorySortProperty, $remositoryDirection;
	        $result = strcmp($a->$remositorySortProperty, $b->$remositorySortProperty);
	        return \'asc\' == $remositoryDirection ? $result : -$result;' ));
		return $objarray;
	}
	
	function sefRelToAbs ($link) {
		if (defined('_JOOMLA_15PLUS')) return $this->getRootSite().JRoute::_($link);
		else return sefRelToAbs($link);
	}
	
	function remove_magic_quotes ($array, $keyname=null) {
		$result = array();
		foreach ($array as $k => $v) {
			if (is_object($v)) $result[$k] = $v;
			elseif (is_array($v)) $result[$k] = $this->remove_magic_quotes($v, $keyname);
			elseif (empty($keyname) OR $k == $keyname) $result[$k] = stripslashes($v);
			else $result[$k] = $v;
		}
		return $result;
	}

	function sendMail ($recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) {
		$from = $this->getCfg('mailfrom');
		$fromname = $this->getCfg('fromname');
		if (!$from) $from = remositoryUser::superAdminMail();
		if (!$fromname) $fromname = _DOWN_ADMINISTRATOR;
		if (defined('_JOOMLA_15PLUS')) return JUTility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname );
		else return mosMail ($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
	}

	function getRootSite () {
		$scheme = isset($_SERVER['HTTP_SCHEME']) ? $_SERVER['HTTP_SCHEME'] : ((isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS'] != 'off')) ? 'https' : 'http');
		if (isset($_SERVER['HTTP_HOST'])) {
			$withport = explode(':', $_SERVER['HTTP_HOST']);
			$servername = $withport[0];
			if (isset($withport[1])) $port = ':'.$withport[1];
		}
		elseif (isset($_SERVER['SERVER_NAME'])) $servername = $_SERVER['SERVER_NAME'];
		else return '';
		if (!isset($port) AND !empty($_SERVER['SERVER_PORT'])) $port = ':'.$_SERVER['SERVER_PORT'];
		if (!isset($port) OR ('http' == $scheme AND ':80' == $port) OR ('https' == $scheme AND ':443' == $port)) $port = '';
		return $scheme.'://'.$servername.$port;
	}

}

}

// Rudimentary controller for use by modules and plugins
abstract class remositoryAddOnController {
	public $repnum = 0;
	public $remUser = '';
	public $repository = '';
	public $admin = '';
	public $idparm = 0;
	public $Itemid = 0;
	public $orderby = _REM_DEFAULT_ORDERING;
	public $submit_text = '';
	public $submitok = true;
	
	function __construct () {
		$interface = remositoryInterface::getInstance();
		$this->remUser = $interface->getUser();
		$this->repository = remositoryRepository::getInstance();
		$this->admin = $this;
	}
}

remositoryInterface::getInstance()->loadLanguageFile();
