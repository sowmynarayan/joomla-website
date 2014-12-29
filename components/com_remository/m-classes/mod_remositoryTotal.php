<?php
/**
* FileName: mod_remositoryTotal.php
* Date: April 2009
* License: GNU General Public License v.2
* Script Version #: 3.50
* ReMOSitory Version #: 3.50 or above
* Author: Martin Brampton - martin@remository.com (http://remository.com)
* Copyright: Martin Brampton 2009
**/

class mod_remositoryTotal extends mod_remositoryBase {

	public function showCount ($module, &$content, $area, $params) {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();

		$sql = "SELECT SUM(downloads) from #__downloads_files";
		$database->setQuery( $sql );
		$total = number_format($database->loadResult());
		$content = "<div align='center' class='sitename'>$total</div>";
	}
	
}