<?php
/**
* FileName: mod_remositoryNewest.php
* Date: April 2009
* License: GNU General Public License
* Script Version #: 3.50
* ReMOSitory Version #: 3.50 or above
* Author: Martin Brampton - martin@remository.com (http://remository.com)
* Copyright: Martin Brampton 2006-9
**/

class mod_remositoryNewest extends mod_remositoryBase {

	public function showFileList ($module, &$content, $area, $params) {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		$repository = remositoryRepository::getInstance();
		$remUser = $interface->getUser();

		// Find out $Itemid
		$base_url = 'index.php?option=com_remository';        	// Base URL string
		$base_url .= '&Itemid='.$this->remos_getItemID('com_remository');
		$base_url .= '&func=fileinfo&id=';

		/*********************Configuration*********************/
		// Set to '1' to Show the Description, set to 0 to not show it
		$showsmall = $this->remos_get_module_parm($params,'showsmall',0);
		// Max number of entries to show
		$max = $this->remos_get_module_parm($params,'max',5 );
		// Max number of description characters
		$maxchars = $this->remos_get_module_parm($params,'maxchars',100);
		// Date format for display
		$date_format = $this->remos_get_module_parm($params,'dateformat','%b.%g');
		// Category from which to select files
		$category = $this->remos_get_module_parm($params,'category', 0);

		$max = max($max,1);
		$maxchars = max($maxchars,20);
		/*******************************************************/

		$tabclass_arr = explode(",",$repository->tabclass);

		// Newest 5 Downloads
		$newdownloads = remositoryFile::newestFiles ($category, $max, $remUser);
		if ($category AND count($newdownloads)==0) $newdownloads = remositoryFile::newestFiles (0, $max, $remUser);

		// $this->remos_module_CSS ();
		$content = <<<START_CATS
		
		<table class="remositorymodule" cellspacing="2" cellpadding="1" border="0" width="100%">

START_CATS;

		$tabcnt = 0;

		foreach ($newdownloads as $newdownload) {
			$sdesc = '';
			if ($showsmall) {
				if (($newdownload->description<>'') AND ($newdownload->autoshort)) $sdesc.='<br/>'.strip_tags($newdownload->description);
				elseif ($newdownload->smalldesc<>'') $sdesc.='<br/>'.strip_tags($newdownload->smalldesc);
				if (strlen($sdesc)>$maxchars) $sdesc=substr($sdesc,0,$maxchars-3).'...';
			}
			$curicon = $newdownload->icon ? $newdownload->icon : 'generic.png';
			$url = $interface->sefRelToAbs($base_url.$newdownload->id);
			$class = $tabclass_arr[$tabcnt];
			$content .= "<tr class='$class'>";
			if (strtolower($date_format) != 'none') {
				$content .= "<td width='20%' valign='middle' class='number'>".strftime($date_format, strtotime($newdownload->filedate));
				$content .= "<br /><img src='$this->live_site/components/com_remository/images/calendar.gif' border='0' width='16' height='16' alt='' align='middle'/></td>";
			}
			$content .= "<td width='80%'><a href='$url'>";
			$content .= $repository->RemositoryImageURL('file_icons/'.$curicon,16,16);
			$content .= $newdownload->filetitle."</a>$sdesc</td></tr>\n";
			$tabcnt = 1 - $tabcnt;
		}
		$content .= "\n</table>\n";
	}
}