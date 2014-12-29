<?php

/**************************************************************
* This file is part of Remository
* Copyright (c) 2006,2007 Martin Brampton
* Issued as open source under GNU/GPL
* For support and other information, visit http://remository.com
* To contact Martin Brampton, write to martin@remository.com
*
* Remository started life as the psx-dude script by psx-dude@psx-dude.net
* It was enhanced by Matt Smith up to version 2.10
* Since then development has been primarily by Martin Brampton,
* with contributions from other people gratefully accepted
*/

class remositoryAdminManager {
	var $plugin_name = '';
	var $act = '';
	var $actname = '';
	var $task = '';
	var $limitstart = 0;
	var $limit = 0;
	var $cfid = 0;
	var $currid = 0;
	var $c_classes_path = '';
	var $v_classes_path = '';
	var $repository = null;

	function remositoryAdminManager ($plugin_name) {
		$interface = remositoryInterface::getInstance();
		$mosConfig_admin_site = $interface->getCfg('admin_site');
		$style = <<<ADMIN_STYLE
<link rel="stylesheet" href="$mosConfig_admin_site/components/com_remository/admin.css" type="text/css" />
ADMIN_STYLE;
		if (defined('_MAMBO_46PLUS') OR defined ('_MAMBO_45MINUS')) echo $style;
		else $interface->addCustomHeadTag($style);
		// Include files that contain definitions
		$this->repository = remositoryRepository::getInstance();
		$mosConfig_sitename = $interface->getCfg('sitename');
		$mosConfig_absolute_path = $interface->getCfg('absolute_path');
		$this->plugin_name = $plugin_name;
		$this->c_classes_path = $this->v_classes_path = $mosConfig_absolute_path.'/components/com_remository/';
		$this->c_classes_path .= 'c-admin-classes/';
		$this->v_classes_path .= 'v-admin-classes/';
		$this->noMagicQuotes($interface);
		if ($this->act = remositoryRepository::GetParam ($_REQUEST, 'act', 'cpanel'));
		else $this->act = 'cpanel';
		if ($this->task = remositoryRepository::GetParam($_REQUEST, 'task', 'list'));
		else $this->task = 'list';
		if ('cpanel' == $this->task) $this->act = 'cpanel';
		$_REQUEST['act'] = $this->act;
		$_REQUEST['task'] = $this->task;
		$this->actname = strtoupper(substr($this->act,0,1)).strtolower(substr($this->act,1));
		$default_limit  = $interface->getUserStateFromRequest( "viewlistlimit", 'limit', $interface->getCfg('list_limit') );
		$this->limit = intval( remositoryRepository::getParam( $_REQUEST, 'limit', $default_limit ) );
		if (1 > $this->limit) $this->limit = 99999;
		$this->limitstart = intval( remositoryRepository::getParam( $_REQUEST, 'limitstart', 0 ) );
		$this->cfid = remositoryRepository::getParam($_REQUEST, 'cfid', array(0));
		if (is_array( $this->cfid )) foreach ($this->cfid as $key=>$value) $this->cfid[$key] = intval($value);
		else $this->cfid = array(intval($this->cfid));
		$this->currid = $this->cfid[0];
		$control_class = $this->plugin_name.'Admin'.$this->actname;
		if (file_exists($this->c_classes_path.$control_class.'.php')) {
			if (class_exists($control_class)) {
				$controller = new $control_class($this);
				$task = $this->task.'Task';
				if (method_exists($controller,$task)) $controller->$task();
				else trigger_error(sprintf(_DOWN_METHOD_NOT_PRESENT, $this->plugin_name, $task, $control_class));
			}
			else trigger_error(sprintf(_DOWN_CLASS_NOT_PRESENT, $this->plugin_name, $control_class));
		}
		else {
			$view_class = 'list'.$this->actname.'HTML';
			$controller = new remositoryAdminControllers($this);
			$view = $this->newHTMLClassCheck ($view_class, $controller, 0, '');
			if ($view AND $this->checkCallable($view, 'view')) $view->view();
			else trigger_error(sprintf(_DOWN_CLASS_NOT_PRESENT, $this->plugin_name, $view_class));
		}
	}

	function noMagicQuotes ($interface) {
		// Is magic quotes on?
		if (get_magic_quotes_gpc()) {
			// Yes? Strip the added slashes
			$_REQUEST = $interface->remove_magic_quotes($_REQUEST);
			$_GET = $interface->remove_magic_quotes($_GET);
			$_POST = $interface->remove_magic_quotes($_POST);
			$_FILES = $interface->remove_magic_quotes($_FILES, 'name');
		}
		set_magic_quotes_runtime(0);
	}

	function check_selection ($text) {
		if (!is_array($this->cfid) OR count( $this->cfid ) < 1) {
			echo "<script> alert('".$text."'); window.history.go(-1);</script>\n";
			exit;
		}
	}

	function newHTMLClassCheck ($name, &$controller, $total_items, $clist) {
		$controller->makePageNav($this, $total_items);
		if (class_exists($name)) return new $name ($controller, $this->limit, $clist);
		trigger_error(sprintf("Component %s error: attempt to use non-existent class %s", $this->plugin_name, $name));
		return false;
	}

	function checkCallable ($object, $method) {
		if (method_exists($object, $method)) return true;
		$name = get_class($object);
		trigger_error(sprintf("Component $this->plugin_name error: attempt to use non-existent method $method in $name", $this->plugin_name, $method, $name));
		return false;
	}

}

class remositoryAdminControllers {
	public $repnum = 0;
	public $remUser = '';
	public $repository = '';
	public $interface = '';
	public $admin = '';
	public $pageNav = '';
	public $absolute_path = '';
	public $live_site = '';

	function remositoryAdminControllers ($admin) {
		$this->repnum = max(1, remositoryRepository::getParam($_REQUEST, 'repnum', 1));
		$this->admin = $admin;
		$this->repository = $admin->repository;
		$this->interface = remositoryInterface::getInstance();
		$this->remUser = $this->interface->getUser();
		$this->absolute_path = $this->interface->getCfg('absolute_path');
		$this->live_site = $this->interface->getCfg('live_site');
	}

	function makePageNav (&$admin, $total) {
		$this->pageNav = $this->interface->makePageNav( $total, $admin->limitstart, $admin->limit );
	}

	function backTask ($message='') {
		$this->interface->redirect( "index2.php?option=com_remository&repnum=".$this->repnum, $message);
	}

	function error_popup ($message) {
		echo "<script> alert('".$message."'); </script>\n";
	}

}
