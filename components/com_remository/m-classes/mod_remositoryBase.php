<?php
/**
* FileName: mod_remositoryBase.php
* Date: April 2009
* License: GNU General Public License
* Script Version #: 3.50
* ReMOSitory Version #: 3.50 or above
* Author: Martin Brampton - martin@remository.com (http://remository.com)
* Copyright: Martin Brampton 2006-9
* The base class for Remository modules
**/

abstract class mod_remositoryBase {
	private static $cssdone = false;
	protected $live_site = '';
	protected $remUser = null;
	
	public function __construct () {
		$interface = remositoryInterface::getInstance();
		$this->live_site = $interface->getCfg('live_site');
		$this->remUser = $interface->getUser();
	}

	protected function remos_get_module_parm ($params, $name, $default) {
		$value =  method_exists($params,'get') ? $params->get($name,$default) : (isset($params->$name) ? $params->$name : $default);
		$isnumeric = is_numeric($default);
		if ($isnumeric AND !is_numeric($value)) return $default;
		if ($isnumeric) return intval($value);
		return $value;
	}
	
	// Limited use - older systems have already created header before module code is run
	protected function remos_module_CSS () {
		if (self::$cssdone) return;
		self::$cssdone = true;
		$interface = remositoryInterface::getInstance();
		$module_css = <<<MODULE_CSS
		
<link href="{$interface->getCfg('live_site')}/components/com_remository/remository.module.css" rel="stylesheet" type="text/css" />

MODULE_CSS;

		$interface->addCustomHeadTag($module_css);
	}	

	protected function remos_getItemID ($component_string) {
		$repository = remositoryRepository::getInstance();
		return $repository->getItemid($component_string);
	}

}