<?php
/**
* FileName: mod_remositoryTree.php
* Date: April 2009
* License: GNU General Public License v.2
* Script Version #: 3.50
* ReMOSitory Version #: 3.50 or above
* Author: Martin Brampton - martin@remository.com (http://remository.com)
* Copyright: Martin Brampton 2009
**/

class mod_remositoryTree extends mod_remositoryBase {

	public function showTreeOverview ($module, &$content, $area, $params) {
		$interface = remositoryInterface::getInstance();
		$remlink = $interface->getCfg('live_site').'/components/com_remository/';
		$links = <<<LINKS

	<link rel="stylesheet" href="{$remlink}dtree.css" type="text/css" />
	
LINKS;

		$interface->addCustomHeadTag($links);
		$manager = remositoryContainerManager::getInstance();
		$addcontainer = $manager->getTreeAdds($this->remUser);
		$view = new remositoryTreeHTML($this);
		$content = $view->treeHTML($addcontainer);
	}
	
}