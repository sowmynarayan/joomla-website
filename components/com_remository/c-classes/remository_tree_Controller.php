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

class remository_tree_Controller extends remositoryUserControllers {

	function tree ($func) {
		$interface = remositoryInterface::getInstance();
		$remlink = $interface->getCfg('live_site').'/components/com_remository/';
		$links = <<<LINKS

	<link rel="stylesheet" href="{$remlink}dtree.css" type="text/css" />
	<script type="text/javascript" src="{$remlink}dtree.js"></script>
	
LINKS;

		$interface->addCustomHeadTag($links);
		$manager = remositoryContainerManager::getInstance();
		$addcontainer = $manager->getTreeAdds($this->remUser);
		$view = new remositoryTreeHTML($this);
		echo $view->treeHTML($addcontainer);
	}
	
}