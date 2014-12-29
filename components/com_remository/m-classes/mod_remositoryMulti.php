<?php
/**
* FileName: mod_remositoryMulti.php
* Date: April 2009
* License: GNU General Public License
* Script Version #: 3.50
* ReMOSitory Version #: 3.50 or above
* Author: Martin Brampton - martin@remository.com (http://remository.com)
* Copyright: Martin Brampton 2006-9
**/

class mod_remositoryMulti extends mod_remositoryBase {

	private $modtype = 'newest';
	private $listtype = 'list';
	private $showcat = 0;
	private $showthumb = 0;
	private $iconsize = 16;
	private $diconsize = 16;
	private $max = 5;
	private $maxchars = 0;
	private $date_format = 'M.d';
	private $category= 0;
	private $days = 30;
	private $html = '';
	
	public function showFileList ($module, &$content, $area, $params) {	
		$interface = remositoryInterface::getInstance();
		$this->remUser = $interface->getUser();
		
		/*********************Configuration*********************/
		// Type of module - popular, downloads, newest
		$this->modtype = $this->remos_get_module_parm($params,'modtype','newest');
		// Type of output - list of files or RSS link
		$this->listtype = $this->remos_get_module_parm($params,'listtype','list');
		// Set to 1 to show container, set to 0 to omit
		$this->showcat = $this->remos_get_module_parm($params,'showcat',0);
		// Set to 1 to show the file thumbnail (if any), set to 0 to not show thumbnail
		$this->showthumb = $this->remos_get_module_parm($params,'showthumb',0);
		// Set to non zero pixel size to show file icon, 0 to not show
		$this->iconsize = $this->remos_get_module_parm($params,'iconsize',16);
		// Set to non zero pixel size to show date icon, 0 to not show
		$this->diconsize = $this->remos_get_module_parm($params,'diconsize',16);
		// Max number of entries to show
		$this->max = $this->remos_get_module_parm($params,'max',5 );
		// Max number of description characters, 0 for no description
		$this->maxchars = $this->remos_get_module_parm($params,'maxchars',100);
		// Date format for display, 'none' if no display required
		$this->date_format = $this->remos_get_module_parm($params,'dateformat','%b.%g');
		// Category from which to select files
		$this->category = $this->remos_get_module_parm($params,'category', 0);
		// if (!$this->category) $this->showcat = 0;
		// Maximum number of days to consider where log file is used
		$this->days = $this->remos_get_module_parm($params, 'days', 30);

		$this->max = max($this->max,1);
		/*******************************************************/
	
		if ($this->listtype == 'list') {
			switch ($this->modtype) {
				case 'random':
					$files = remositoryFile::randomFiles ($this->category, $this->max, $this->remUser);
					break;
				case 'popular':
					$files = remositoryFile::popularLoggedFiles ($this->category, $this->max, $this->days, $this->remUser);
					break;
				case 'download':
					$files = remositoryFile::popularDownloadedFiles ($this->category, $this->max, $this->remUser);
					break;
				case 'newest':
				default:
					$files = remositoryFile::newestFiles ($this->category, $this->max, $this->remUser);
	
			}
		
			foreach ($files as $file) $this->displayFile($file);
		}
		else $this->displayRSS();
		$content = $this->html;
	}

	private function displayFile ($file) {
		$repository = remositoryRepository::getInstance();
		if ($this->showcat) $caturl = $repository->RemositoryFunctionURL('select', $file->containerid);
		if ($this->iconsize) {
			if ($file->icon) $icon = $file->icon;
			else $icon = 'generic.png';
			$iconurl = $repository->RemositoryImageURL('file_icons/'.$icon, $this->iconsize, $this->iconsize);
		}
		else $iconurl = '';
		if ($this->showthumb) $thumbnails = new remositoryThumbnails($file);
		if (strtolower($this->date_format) != 'none') {
			if ($this->diconsize) $dateurl = $repository->RemositoryImageURL('calendar.gif', $this->diconsize, $this->diconsize);
			else $dateurl = '';
			$time = strtotime($file->filedate);
			if ($repository->Set_date_locale) setlocale(LC_TIME, $repository->Set_date_locale);
			$datetext = strftime($this->date_format, $time);
		}
		if ($this->maxchars) {
			if (strlen($file->smalldesc) > $this->maxchars-3) $desc = substr($file->smalldesc,0,$this->maxchars-3).'...';
			else $desc = $file->smalldesc;
		}
		$link = $repository->RemositoryFunctionURL('fileinfo', $file->id);
		$ampencode = '/(&(?!(#[0-9]{1,5};))(?!([0-9a-zA-Z]{1,10};)))/';
		$link = preg_replace($ampencode, '&amp;', $link);
		
		$this->html .= "\n<div>";
		$this->html .= "\n<div>$iconurl$link$file->filetitle</a></div>";
		if ($this->showcat) $this->html .= "\n\t<div>($caturl$file->name</a>)</div>";
		if ($this->showthumb) $this->html .= "\n\t".$thumbnails->displayOneThumbnail();
		if (strtolower($this->date_format) != 'none') $this->html .= "\n\t<div>".$dateurl.$datetext.'</div>';
		if ($this->maxchars) $this->html .= "\n\t<p>$desc</p>";
		$this->html .= "\n</div>";
	}
	
	private function displayRSS () {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		$repository = remositoryRepository::getInstance();
		$url = "index.php?option=com_remository&func=rss&no_html=1&rtype=$this->modtype&max=$this->max&days=$this->days&Itemid=";
		if (isset($GLOBALS['remosef_itemids']['com_remository'])) $Itemid = $GLOBALS['remosef_itemids']['com_remository'];
		else {
			$database->setQuery("SELECT id FROM #__menu WHERE link = 'index.php?option=com_remository'");
			$GLOBALS['remosef_itemids']['com_remository'] = $Itemid = $database->loadResult();
		}
		$url .= intval($Itemid);
		if ($this->category) $url .= "&id=".$this->category;
		$sefurl = $interface->sefRelToAbs($url);
		$sefurl = preg_replace('/(&)([^#]|$)/','&amp;$2', $sefurl);
		if ($this->showcat) {
			$caturl = $repository->RemositoryFunctionURL('select', $this->category);
			$database->setQuery("SELECT name FROM #__downloads_containers WHERE id=$this->category");
			$catname = $database->loadResult();
		}
		
		$this->html .= "\n<div style='text-align:center'>";
		if ($this->showcat) $this->html .= "\n\t<h4>$caturl$catname</a></h4>";
		$this->html .= "\n\t<a href='$sefurl'>";
		$rssimage = $repository->RemositoryImageURL('feed-icon-32x32.gif').' RSS</a>';
		$rssimage = str_replace('img', 'img style="border:0"', $rssimage);
		$this->html .= $rssimage;
		$this->html .= "\n</div>";
	}

}
