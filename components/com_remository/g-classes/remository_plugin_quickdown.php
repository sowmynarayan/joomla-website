<?php

/**
 * Quickdown plugin for Remository 3.50+ 
 * License : http://www.gnu.org/copyleft/gpl.html ver 2
 * @ originally by Mamboaddons.com DEV, later Martin Brampton
 * @Copyright (C) 2004 - 2005 http://www.mamboaddons.com, 2006-9 Martin Brampton
 * martin@remository.com
 * http://remository.com
 * Special Thanks to wolfi from http://www.mamboport.de for preparing version
 * 1.1b for Mambo 4.5.1
 */

if (defined('_ALIRO_IS_PRESENT')) {
	class bot_remositoryQuickdown extends aliroPlugin {
		
		public function onPrepareContent ($article) {
		 	$worker = new remository_plugin_quickdown();
		 	return $worker->onPrepareContent ($this->params, $article);
		}
			
	}		
}

class remository_plugin_quickdown {
	private $params = null;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */

	function onPrepareContent ($params, $row, $published=true) {

		$this->params = $params;
		$interface = remositoryInterface::getInstance();
		$mosConfig_lang = $interface->getCfg('lang');

		// How do we handle published?
		$published = true;
		if (!$published) {
			$row->text = preg_replace('/{quickdown:.+?}/', '', $row->text);
			$row->text = preg_replace('/{quickcat:.+?}/', '', $row->text);
			$row->text = preg_replace('/{quickfolder:.+?}/', '', $row->text);
			return;
		}

		$database = $interface->getDB();

		$content = $row->text;
		$matches = array ();

		// Get ids from Quickdown command
		if (preg_match_all('/{quickdown:.+?}/', $content, $matches, PREG_PATTERN_ORDER)) {

			//Get IDS
			foreach ($matches as $fmatch) {
				foreach ($fmatch as $match) {
					$match = str_replace("{quickdown:", "", $match);
					$match = str_replace("}", "", $match);
					$output = $this->createLink($match, $this->params);
					$content = preg_replace("/{quickdown:$match}/", $output, $content);
				}
			}
			unset($matches);
		}

		if (preg_match_all('/{quickcat:.+?}/', $content, $matches, PREG_PATTERN_ORDER)) {
			//Get IDS from quickcat cómmand
			foreach ($matches as $fmatch) {
				foreach ($fmatch as $match) {
					$match = str_replace("{quickcat:", "", $match);
					$match = str_replace("}", "", $match);
					$match = intval($match);
					$sql = "SELECT f.id from #__downloads_files AS f, #__downloads_structure AS s WHERE s.container=$match AND s.item=f.containerid AND f.published=1";
					$database->setQuery($sql);
					$catrows=$database->loadObjectList();
					$output = '';
					if ($catrows) {
						foreach ($catrows as $catrow){
							$output.=$this->createLink($catrow->id, $this->params).$this->params->get("delimiter","<br><br>");
						}
					}
					$content = preg_replace("/{quickcat:$match}/", $output, $content);
				}
			}
			unset($matches);
		}


		if (preg_match_all('/{quickfolder:.+?}/', $content, $matches, PREG_PATTERN_ORDER)) {
			//Get IDS from quickcat cómmand
			foreach ($matches as $fmatch) {
				foreach ($fmatch as $match) {
					$match = str_replace("{quickfolder:", "", $match);
					$match = str_replace("}", "", $match);
					$match = intval($match);
					$sql = "SELECT id from #__downloads_files WHERE containerid=$match AND published='1'";
					$database->setQuery($sql);
					$folderrows=$database->loadObjectList();
					$output = '';
					if ($folderrows) {
						foreach ($folderrows as $folderrow){
							$output.=$this->createLink($folderrow->id, $this->params).$this->params->get("delimiter","<br><br>");
						}
					}
					$content = preg_replace("/{quickfolder:$match}/", $output, $content);
				}
			}
			unset($matches);
		}

		$row->text = $content;
		return true;
	}

	/**
	 * This function create the output for the file with the given id
	 * @param int $id - The id of the file
	 * @param object $param - The parameter object, that hold the parameter
	 * @return String - The generated table with the Information for the file
	 */
	function createLink($id, &$param){

		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		// Find out $Itemid
		$base_url = 'index.php?option=com_remository&Itemid='.remositoryRepository::getInstance()->getItemid();        	// Base URL string

		// Generate download
		//Getting File-info from Database
		$id = intval($id);
		$database->setQuery("SELECT * FROM #__downloads_files WHERE id=$id");
		$filelist = $database->loadObjectList();
		if ($filelist) {
			$file = $filelist[0];
			$allvotes = 0;
			$rating = 0;
			$sql = "SELECT COUNT(id) as allvotes, AVG(value) as rating FROM #__downloads_log WHERE type=3 AND fileid=$id";
			$database->setQuery($sql);
			$result = $database->query();
			$votes = $database->loadObjectList();
			$allvotes = $votes[0]->allvotes;
			if ($votes[0]->rating) $rating = round($votes[0]->rating);
			else $rating = 0;
			$output = "<table>";

			// short description on top position
			if ($param->get("show_shortdesc","0") AND $param->get("show_shortdesc_pos")=='top'){
				$output .= "<tr><td>$file->smalldesc<br><br></td></tr>";
			}

			// full description on top position
			if ($param->get("show_desc","0") AND $param->get("show_desc_pos")=='top'){
				$output .= "<tr><td>$file->description<br><br></td></tr>";
			}
			//downloadlink
			if ($param->get("show_download","0")){
				$linkurl = $interface->sefRelToAbs($base_url."&func=startdown&id=$id");
				$output .= "<tr><td><a href=\"$linkurl\"><img src=\"components/com_remository/images/download_trans.gif\"border=\"0\">&nbsp;<b>"._DOWNLOAD."&nbsp;$file->filetitle</b></a></td></tr>";
			}

			$output .= "<tr><td><table>";

			//filetitle and link to entry
			if ($param->get('show_title', "0")){
				$output .= "<tr><td><b>"._DOWN_FILE_TITLE."</b></td><td>$file->filetitle";

				if ($param->get("show_ltentry","0")){
					$linkurl = $interface->sefRelToAbs($base_url."&func=fileinfo&id=$id");
					$output .= "&nbsp;<a href=\"$linkurl\"><i>(Details)</i></a>";
				}
				$output .= "</td></tr>";
			}
			//filetype
			if ($param->get('show_type', "0")){
				$output .= "<tr><td><b>"._DOWN_FILE_TYPE."</b></td><td>$file->filetype</td></tr>";
			}
			//file version
			if ($param->get('show_version', "0")){
				$output .= "<tr><td><b>"._DOWN_FILE_VER."</b></td><td>$file->fileversion</td></tr>";
			}
			//filesize
			if ($param->get('show_size', "0")){
				$output .= "<tr><td><b>"._DOWN_FILE_SIZE."</b></td><td>$file->filesize</td></tr>";
			}
			//license
			if ($param->get('show_license', "0")){
				$output .= "<tr><td><b>"._DOWN_LICENSE."</b></td><td>$file->license</td></tr>";
			}
			//author
			if ($param->get('show_author', "0")){
				$output .= "<tr><td><b>"._DOWN_FILE_AUTHOR."</b></td><td>$file->fileauthor</td></tr>";
			}
			//homepage
			if ($param->get('show_homepage', "0")){
				//check if http is given in url
				if(!strpos($file->filehomepage,"ttp://")){$nurl="http://$file->filehomepage";}
				else {$nurl="$file->filehomepage";}
				$output .= "<tr><td><b>"._DOWN_FILE_HOMEPAGE."</b></td><td><a href=\"$nurl\" target=\"_blank\">$file->filehomepage</a></td></tr>";
			}
			//download count
			if ($param->get('show_count', "0")){
				$output .= "<tr><td><b>"._DOWN_DOWNLOADS."</b></td><td>$file->downloads</td></tr>";
			}
			//show rating
			if ($param->get('show_rating', "0")){
				$output .= "<tr><td><b>"._DOWN_RATING."</b></td><td><img src=\"components/com_remository/images/stars/$rating.gif\">&nbsp;($allvotes "._DOWN_VOTES_TITLE.")</td></tr>";
			}
			/* form for frontend Voting */
			if ($param->get('allow_rating', "0")){
				$link = $interface->sefRelToAbs('index.php?option=com_remository&func=fileinfo&id='.$id);
				$output .= "<tr><td><b>"._DOWN_YOUR_VOTE."</b></td><td><form method=\"post\" action=\"".$link."\">";
				$output .= "<select name=\"user_rating\" class=\"inputbox\">";
				$output .= "<option value=\"0\">?</option>";
				$output .= "<option value=\"1\">1</option>";
				$output .= "<option value=\"2\">2</option>";
				$output .= "<option value=\"3\">3</option>";
				$output .= "<option value=\"4\">4</option>";
				$output .= "<option value=\"5\">5</option>";
				$output .= "</select>";
				$output .= "<input class=\"button\" type=\"submit\" name=\"submit_vote\" value="._DOWN_RATE_BUTTON.">";
				$output .= "</form> </td></tr>";
				/* end form */
			}

			$output .= "</table></td></tr><tr><td>";

			// short description on bottom position
			if ($param->get("show_shortdesc","0") AND $param->get("show_shortdesc_pos")=='bottom'){
				$output .= "<tr><td colspan=\"2\"><br>$file->smalldesc</td></tr>";
			}

			// full description on top position
			if ($param->get("show_desc","0") AND $param->get("show_desc_pos")=='bottom'){
				$output .= "<tr><td colspan=\"2\"><br>$file->description</td></tr>";
			}

			$output .= "</td></tr></table>";

		}
		return isset($output) ? $output : '';
	}

}
