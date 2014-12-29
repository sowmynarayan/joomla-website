<?php

/**************************************************************
* This file is part of Remository
* Copyright (c) 2006-9 Martin Brampton
* Issued as open source under GNU/GPL
* For support and other information, visit http://remository.com
* To contact Martin Brampton, write to martin@remository.com
*
* Remository started life as the psx-dude script by psx-dude@psx-dude.net
* It was enhanced by Matt Smith up to version 2.10
* Since then development has been primarily by Martin Brampton,
* with contributions from other people gratefully accepted
*/

abstract class remositoryUserHTML extends remositoryHTML {
	protected $repnum = 0;
	protected $controller = '';
	protected $repository = '';
	protected $interface = '';
	protected $remUser = '';
	protected $submitok = false;
	protected $submit_text = '';
	protected $orderby = _REM_DEFAULT_ORDERING;
	protected $mainpicture = '';

	public function __construct ($controller) {
		$this->repnum = max(1, remositoryRepository::getParam($_REQUEST, 'repnum', 1));
		$this->controller = $controller;
		$this->interface = remositoryInterface::getInstance();
		$this->repository = remositoryRepository::getInstance();
		$this->mainpicture = $this->repository->headerpic;
		$thumb_width_x = $this->repository->Small_Image_Width + 20;
		$thumb_width_x_plus = $thumb_width_x + 20;
		$thumb_height_y = $this->repository->Small_Image_Height + 50;
		if ($this->mainpicture) $headingcss = <<<HEADING_CSS
		
#remositorypageheading {
	background-image:	url($this->mainpicture);
}
		
HEADING_CSS;

		else $headingcss = '';

		$css = <<<end_css
<style type='text/css'>
/* Remository specific CSS requiring variables */
$headingcss
.remositoryfilesummary
{
	margin-right:	{$thumb_width_x_plus}px;
}
.remositoryonethumb {
	width: {$thumb_width_x}px;
}
.remositorydelthumb {
	height:		{$thumb_height_y}px;
}
/* End of variable Remository CSS */
</style>
end_css;

		$this->interface->addCustomHeadTag($css);

		$baselink = $this->interface->getCfg('live_site').'/components/';
		$basedir = $this->interface->getCfg('absolute_path').'/components/';
		$cssfile = file_exists($basedir.'com_remository_files/custom.css') ? $baselink.'com_remository_files/custom.css' : $baselink.'com_remository/remository.css';
		// $jsfile = $baselink.'com_remository/dddropdownpanel.js';
		// Put this back below link (without the comment markers) to activate:
// <script type="text/javascript" src="$jsfile">
// /***********************************************
// * DD Drop Down Panel- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
// * This notice MUST stay intact for legal use
// * Visit Dynamic Drive at http://www.dynamicdrive.com/ for this script and 100s more
// ***********************************************/
// </script>

		$css = <<<REMOS_HEADER
		
<link href='$cssfile' rel='stylesheet' type='text/css' />

REMOS_HEADER;

		$this->interface->addCustomHeadTag($css);

		$this->remUser = $this->interface->getUser();
		$this->submitok = isset($controller->submitok) ? $controller->submitok : true;
		$this->submit_text = isset($controller->submit_text) ? $controller->submit_text : '';
		$this->orderby = isset($controller->orderby) ? $controller->orderby : _REM_DEFAULT_ORDERING;
	}
	
	protected function show ($string) {
		return htmlspecialchars($string, ENT_QUOTES, _CMSAPI_CHARSET, false);
	}

	protected function showHTML ($string) {
		$ampencode = '/(&(?!(#[0-9]{1,5};))(?!([0-9a-zA-Z]{1,10};)))/';
		return preg_replace($ampencode, '&amp;', $string);
	}

	protected function fileOutputBox ($title, $value, $suppressHTML=false) {
	    if ($suppressHTML) $value = htmlspecialchars($value);
		echo <<<OUTPUT_BOX
		<dt>$title</dt>
		<dd>
		  $value
		</dd>
OUTPUT_BOX;
	}

	protected function mainPageHeading ($belowTop) {
		$title = _DOWNLOADS_TITLE;
		if ($title OR $this->mainpicture) {
			if ($belowTop) $headlevel = 'h3';
			else $headlevel = 'h2';
			echo "\n\t<div id='remositorypageheading'>";
			// if ($this->mainpicture != '') echo "\n\t\t<img src='$this->mainpicture' alt='Header'/>";
			echo "\n\t\t<$headlevel>$title ";
			// The following three lines create RSS links - now controlled by config
			if ($this->repository->Show_RSS_feeds) {
				$rssurl = $this->repository->RemositoryBasicFunctionURL('rss');
				$this->interface->addCustomHeadTag("<link rel='alternate' type='application/rss+xml' title='RSS - "._DOWN_NEWEST."' href='$rssurl' />");
				echo "<a href='".$rssurl."'>".$this->repository->RemositoryImageURL('feedicon16.gif',16,16)." RSS</a>";
			}
			// End of RSS link code
			echo "</$headlevel>";
			echo "\n\t<!-- End of remositorypageheading-->";
			echo "\n\t</div>\n";
			if (!$belowTop AND $this->repository->preamble) echo <<<PREAMBLE

			<div id="remositorypreamble">
				{$this->repository->preamble}
			</div>

PREAMBLE;
		}
	}

	protected function folderListHeading($container){
		$cname = htmlspecialchars($container->name);
		echo "\n\t<div id='remositorycontainer'>";
		echo "\n\t\t<h2>$cname ";
		// The following three lines create the RSS link for the container - now controlled by config
		if ($this->repository->Show_RSS_feeds) {
			$rssurl = $this->repository->RemositoryBasicFunctionURL('rss', $container->id);
			$this->interface->addCustomHeadTag("<link rel='alternate' type='application/rss+xml' title='RSS - "._DOWN_NEWEST." - $cname' href='$rssurl' />");
			echo "<a href='$rssurl'>".$this->repository->RemositoryImageURL('feedicon16.gif',16,16).' RSS</a>';
		}
		// End of RSS code
		echo '</h2>';
		echo "\n\t\t<p>".$container->description.'</p>';
		echo "\n\t<!-- End of remositorycontainer -->";
		echo "\n\t</div>";
	}

	// To suppress the credits line, change the default for $show_credits to false
	// If you do this, please also consider making a donation to the Remository project!
	protected function remositoryCredits ($show_credits=true) {
		$version = _REMOSITORY_VERSION;
		echo "\n\t<div id='remositorycredits'>";
		if ($show_credits AND $this->repository->Show_Footer) echo "\n\t\t<a href='http://remository.com'>Remository $version</a> is technology by <a href='http://guru-php.com'>Guru PHP</a>";
		echo "\n\t<!-- End of remositorycredits-->";
		echo "\n\t</div>\n";
	}

	protected function pathwayHTML ($parent) {
		if (0 == ($this->repository->Remository_Pathway & 2)) return;
		echo "\n\t<div id='remositorypathway'>";
		echo "\n\t\t".$this->repository->RemositoryFunctionURL().$this->repository->RemositoryImageURL('gohome.gif').' '._MAIN_DOWNLOADS.'</a>';
		if ($parent) {
			echo "\n\t\t";
			$parent->showPathway();
		}
		echo "\n\t<!-- End of remositorypathway-->";
		echo "\n\t</div>\n";
	}

	// Extra function needed to integration pathway into CMS pathway
	protected function pathwayImage () {
		$interface = remositoryInterface::getInstance();
		$imagePath =  '/templates/'.$interface->getTemplate().'/images/arrow.png';
		if (file_exists( $interface->getCfg('absolute_path').$imgPath )) $image = '<img src="' . $interface->getCfg('live_site'). $imagePath . '" border="0" alt="arrow" />';
		else {
			$imagePath = '/images/M_images/arrow.png';
			if (file_exists( $interface->getCfg('absolute_path') . $imagePath )) $image = '<img src="' . $interface->getCfg('live_site') . $imagePath .'" alt="arrow" />';
			else $image = '&gt;';
		}
		return $image;
	}

	protected function URLDisplay ($text, $value) {
		if (!eregi(_REMOSITORY_REGEXP_URL,$value)) {
			if (eregi(_REMOSITORY_REGEXP_URL,'http://'.$value)) $value = 'http://'.$value;
			else {
				echo "\n\t\t\t<dt>$text</dt>";
				echo "\n\t\t\t<dd>$value</dd>";
				return;
			}
		}
		echo "\n\t\t\t<dt>$text</dt>";
		echo "\n\t\t\t<dd><a href='$value'>"._DOWN_CLICK_TO_VISIT.'</a></dd>';
	}
	
	protected function filesFooterHTML () {
		if (!$this->repository->Show_Footer) return;
		$fsearch = $this->footerSearchHTML();
		$fsubmit = $this->footerSubmitHTML();
		echo <<<FILES_FOOTER
		
		<div id='remositoryfooter'>
			$fsearch
			$fsubmit
		<!-- End of remositoryfooter-->
		</div>
		
FILES_FOOTER;

	}
	
	protected function footerSearchHTML () {
		$text = _DOWN_SEARCH;
		$surl = $this->repository->RemositoryFunctionURL('search');
		$simg = $this->repository->RemositoryImageURL('search.gif');
		return <<<FOOTER_SEARCH
		
		<div id='left'>
			$surl
			$simg
			$text</a>
		</div>
		
FOOTER_SEARCH;

	}
	
	protected function footerSubmitHTML () {
		if (!$this->repository->Allow_User_Sub) return '';
		if ($this->submitok) {
			$idparm = remositoryRepository::GetParam($_REQUEST, 'id', 0);
			$startlink = $this->repository->RemositoryFunctionURL('addfile', $idparm);
			$endlink = _SUBMIT_FILE_BUTTON.'</a>';
		}
		else {
			$startlink = '';
			$endlink = $this->submit_text;
		}
		$subimage = $this->repository->RemositoryImageURL('add_file.gif');
		return <<<FOOTER_SUBMIT
		
		<div id='right'>
			$startlink
			$subimage
			$endlink
		</div>
		
FOOTER_SUBMIT;

	}

	protected function fileListing ($file, $container, $downlogo, $remUser, $showContainer=false, $type='A', $downlinktype=0) {
		$thumbnails = new remositoryThumbnails($file);
		$filefunc = $downlinktype ? 'directinfo' : 'fileinfo';
		$downlink = '';
		if ($this->repository->Allow_File_Info) $downlink = $this->repository->RemositoryFunctionURL($filefunc,$file->id);
		if ($file->icon == '') $downlink .= $this->repository->RemositoryImageURL('stuff1.gif');
		else $downlink .= $this->repository->RemositoryImageURL('file_icons/'.$file->icon);
		$downlink .= $file->filetitle;
		if ($this->repository->Allow_File_Info) $downlink .= '</a>';
		if ($this->repository->Enable_List_Download AND is_object($container) AND $container->isDownloadable($this->remUser)) {
			$downword = $file->is_av() ? _DOWN_PLAY : _DOWNLOAD;
			$downlink .= $file->downloadLink($downlinktype).' '.$downlogo.' '.$downword.'</a>';
		}
		if ($showContainer AND is_object($container)) $downlink .= $this->showContainerLinks($container);
		$active_feature = $file->active_feature ? ' activefeature' : ''; 
		
		$thumbdisplay = $thumbnails->displayOneThumbnail();
		echo <<<BEFORE_DETAILS
		
			<div class="remositoryfileblock$active_feature">
				<h3>$downlink</h3>
   				<div class="remositoryonethumb">
   					$thumbdisplay
   				<!-- End of remositoryonethumb -->
   				</div>
   				<div class="remositoryfilesummary"><dl>
   			
BEFORE_DETAILS;

		$this->showFileDetails($file, $remUser, $type);
		
		echo <<<AFTER_DETAILS
		
				<!-- End of remositoryfilesummary -->
				</dl></div>
			<!-- End of remositoryfileblock -->
			</div>

AFTER_DETAILS;

	}
	
	protected function showContainerLinks ($container) {
		$links[] = $this->repository->RemositoryFunctionURL('select', $container->id).$container->name.'</a>';
		if ($this->repository->Show_all_containers) while ($container->parentid) {
			$container = $container->getParent();
			array_unshift($links, $this->repository->RemositoryFunctionURL('select', $container->id).$container->name.'</a>');
		}
		return ' ('.implode(' - ', $links).')';
	}
	
	protected function showFileDetails ($file, $remUser, $type, $dodisplay=true) {
		if ($dodisplay AND $remUser->isAdmin()) $this->fileOutputBox(_DOWN_PUB, ($file->published == 1 ? _YES : _NO), false);

		$customobj = new remositoryCustomizer();
		$fieldnames = $customobj->getFileListFields();
		$customcontrol = $customobj->getCustomSpec();
		$count = 0;
		foreach ($customcontrol['S'] as $key=>$sequence) $reseq[$sequence][] = $key;
		ksort($reseq);
		if (isset($reseq)) foreach ($reseq as $kset) foreach ($kset as $key) {
			if (!empty($customcontrol[$type][$key])) {
				$fieldname = $fieldnames[$key][0];
				$method = 'show_'.$fieldname;
				if (method_exists($this, $method)) {
					$count++;
					if ($dodisplay) $this->$method($file);
				}
			}
		}
		return $count;
	}
	
	protected function show_smalldesc ($file) {
		if ($file->smalldesc<>'') $this->fileOutputBox(_DOWN_DESC_SMALL, $file->smalldesc, !$file->autoshort);
	}
	
	protected function show_submittedby ($file) {
		if ($file->submittedby) {
		    $submitter = new remositoryUser($file->submittedby);
		    $this->fileOutputBox(_DOWN_SUB_BY, $submitter->name);
        }
	}
	
	protected function show_submitdate ($file) {
		if ($file->submitdate<>'') {
			$time = $this->controller->revertFullTimeStamp($file->submitdate);
			if ($this->repository->Set_date_locale) {
				setlocale(LC_TIME, $this->repository->Set_date_locale);
				$date = strftime($this->repository->Date_Format, $time);
			}
			else $date = date ($this->repository->Date_Format, $time);
			$this->fileOutputBox(_DOWN_SUB_DATE, $date);
		}
	}
	
	protected function show_filesize ($file) {
		if ($file->filesize<>'') $this->fileOutputBox(_DOWN_FILE_SIZE, $file->filesize);
	}

	protected function show_filedate ($file) {
		if ($file->filedate<>'') {
			$time = $this->controller->revertFullTimeStamp($file->filedate);
			if ($this->repository->Set_date_locale) {
				setlocale(LC_TIME, $this->repository->Set_date_locale);
				$date = strftime($this->repository->Date_Format, $time);
			}
			else $date = date ($this->repository->Date_Format, $time);
			$this->fileOutputBox(_DOWN_FILE_DATE, $date);
		}
	}

	protected function show_downloads ($file) {
		$this->fileOutputBox(_DOWN_DOWNLOADS, $file->downloads);
	}
	
	protected function show_license ($file) {
		if ($file->license<>'') $this->fileOutputBox(_DOWN_LICENSE, $this->translateDefinitions($file->license), false);
	}
	
	protected function show_fileversion ($file) {
		if ($file->fileversion<>'') $this->fileOutputBox(_DOWN_FILE_VER, $file->fileversion);
	}
	
	protected function show_fileauthor ($file) {
		if ($file->fileauthor<>'') $this->fileOutputBox(_DOWN_FILE_AUTHOR, $file->fileauthor);
	}
	
	protected function show_filehomepage ($file) {
		if ($file->filehomepage<>'') $this->URLDisplay (_DOWN_FILE_HOMEPAGE, $file->filehomepage);
	}
	
	protected function show_vote_value ($file) {
		if ($this->repository->Allow_Votes) {
			$this->bareShowVotes ($file);
			echo "\n\t\t\t\t</dd>";
		}
	}
	
	protected function bareShowVotes ($file) {
		echo "\n";
		?>
			<dt><?php echo _DOWN_RATING; ?></dt>
			<dd>
				<div class='remositoryrating'><?php echo $this->repository->RemositoryImageURL('stars/'.$file->evaluateVote().'.gif',64,12);
					echo _DOWN_VOTES;
					echo round($file->vote_count); ?></div>
		<?php
	}

	protected function voteDisplay (&$file, $entry, $linkfunc='fileinfo') {
		$this->bareShowVotes ($file);
		if ($entry AND $this->remUser->isLogged() AND !$file->userVoted($this->remUser)) {
			// Change for multiple repositories
			// $formurl = $this->interface->sefRelToAbs("index.php?option=com_remository&repnum=$this->repnum&Itemid=".$this->interface->getCurrentItemid()."&func=$linkfunc&id=".$file->id);
			$formurl = $this->interface->sefRelToAbs("index.php?option=com_remository&Itemid=".$this->interface->getCurrentItemid()."&func=$linkfunc&id=".$file->id);
			?>
				<div>
					<form method="post" action="<?php echo $formurl; ?>">
						<select name="user_rating" class="inputbox">
							<option value="0">?</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
						</select>
						<input class="button" type="submit" name="submit_vote" value="<?php echo _DOWN_RATE_BUTTON; ?>" />
						<input type="hidden" name="id" value="<?php echo $file->id; ?>" />
					</form>
				</div>
			<?php
		}
		echo "\n\t\t\t\t</dd>";
	}

	// Not presently used in Remository, but kept here for potential value of the code
	protected function multiOptionList ($name, $title, $options, $current, $tooltip=null) {
		$alternatives = explode(',',$options);
		$already = explode(',', $current);
		?>
		<tr>
	    <td width="30%" valign="top" align="right">
	  	<b><?php echo $title; ?></b>&nbsp;
	    </td>
	    <td valign="top">
		<?php
		foreach ($alternatives as $one) {
			if (in_array($one,$already)) $mark = 'checked="checked"';
			else $mark = '';
			$value = $name.'_'.$one;
			echo "<input type=\"checkbox\" name=\"$value\" $mark />$one";
		}
		if ($tooltip) echo '&nbsp;'.$this->tooltip($tooltip);
		echo '</td></tr>';
	}

	protected function tooltip ($text) {
		return '<a href="javascript:void(0)"  onmouseover="return escape('."'".$text."'".')">'.
		RemositoryRepository::getInstance()->RemositoryImageURL('tooltip.png').'</a>';
	}
	
	protected function translateDefinitions ($string) {
		$translators = get_defined_constants(true);
		return str_replace(array_keys($translators['user']), array_values($translators['user']), $string);
	}

	protected function simpleTickBox ($title, $name, $checked=true) {
		$checkcode = $checked ? 'checked="checked"' : '';
		return <<<TICK_BOX

				<p class="remositoryformentry">
					<label for="$name">$title</label>
					<input type="checkbox" name="$name" id="$name" value="1" $checkcode />
				</p>
				
TICK_BOX;

	}

}
