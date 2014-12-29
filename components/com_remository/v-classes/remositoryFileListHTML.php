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

class remositoryFileListHTML extends remositoryUserHTML {
	var $tabcnt=0;

	private function fileListHeading($orderby, $idparm, $func) {
		$downfiles = _DOWN_FILES;
		$downorder = _DOWN_ORDER_BY;
		$ordername = array ('zero', _DOWN_ID, _DOWN_FILE_TITLE_SORT, _DOWN_DOWNLOADS_SORT, _DOWN_SUB_DATE_SORT, _DOWN_SUB_ID_SORT, _DOWN_AUTHOR_ABOUT, _DOWN_RATING_TITLE);
		for ($by = 1, $n=count($ordername); $by < $n; $by++) {
			if ($orderby<>$by) $option[] = "\n\t\t\t".$this->repository->RemositoryFunctionURL($func,$idparm,null,$by).$ordername[$by].'</a>';
			else $option[] = $ordername[$by];
		}
		echo "\n\t<div id='remositoryfilelisthead'>";
		echo "\n\t\t<h3>$downfiles</h3>";
		echo "\n\t\t<span id='remositoryorderby'><em>$downorder </em>";
		echo implode (' | ', $option);
		echo "\n\t\t</span>";
		echo "\n\t<!-- End of remositoryfilelisthead -->";
		echo "\n\t</div>";
	}

	private function displayContainer ($container, $func) {
		echo "\n\t\t<tr>";
		echo "\n\t\t\t<td><h3>".$this->repository->RemositoryFunctionURL($func, $container->id);
		if ($container->icon == '') echo $this->repository->RemositoryImageURL('folder_icons/folder_yellow.gif');
		else echo $this->repository->RemositoryImageURL('folder_icons/'.$container->icon);
		echo ' '.htmlspecialchars($container->name).'</a></h3></td>';
		if ($this->repository->Show_File_Folder_Counts) echo "\n\t\t\t<td>($container->foldercount/$container->filecount)</td>";
		echo "\n\t\t</tr>";
		if ($container->description) {
			echo "\n\t\t<tr class='remositoryfolderinfo'>";
			echo "\n\t\t\t<td>$container->description</td>";
			echo "\n\t\t</tr>";
		}
	}

	public function fileListHTML( $id, &$container, &$folders, &$files, &$page, $func, $directlink ) {
		if ($container->id) {
			$container->setMetaData();
			$container->showCMSPathway();
			$this->pathwayHTML($container->getParent());
		}
		$this->mainPageHeading($container->id);
		if ($container->id) $this->folderListHeading($container);
		if ($folders){
			$title = _DOWN_CONTAINERS;
			$ff = _DOWN_FOLDERS_FILES;
			echo "\n\t<div id='remositorycontainerlist'>";
		    echo "\n\t\t<table>";
		    echo "\n\t\t<thead><tr>";
		    echo "\n\t\t\t<th id='remositorycontainerhead'>$title</th>";
		    if ($this->repository->Show_File_Folder_Counts) echo "\n\t\t\t<th>$ff</th>";
		    echo "\n\t\t</tr></thead><tbody>";
			foreach ($folders as $folder) {
				$this->displayContainer($folder, $func);
 				$this->tabcnt = ($this->tabcnt+1) % 2;
			}
			echo "\n\t\t</tbody></table>";
			echo "\n\t<!-- End of remositorycontainerlist -->";
			echo "\n\t</div>\n";
		}
		if ($files){
			$this->tabcnt = 0;
			$downlogo = $this->repository->RemositoryImageURL('download_trans.gif');
			$this->fileListHeading($this->orderby, $id, $func);
			$page->showNavigation();
			echo "\n\t<div id='remositoryfilelisting'>";
			foreach ($files as $file) {
				$this->fileListing ($file, $container, $downlogo, $this->remUser, false, 'A', $directlink);
				$this->tabcnt = ($this->tabcnt+1) % 2;
			}
			echo "\n\t<!-- End of remositoryfilelisting -->";
			echo "\n\t</div>\n";
			$page->showNavigation();
			?>
			<script type="text/javascript">
			function download(url){window.location = url}
			</script>
			<?php
		}
		$this->filesFooterHTML ();
		$this->remositoryCredits();
	}

	public function emptyHTML () {
		$mosConfig_sitename = remositoryInterface::getInstance()->getCfg('sitename');
		echo <<<EMPTY_HTML
		
		<div id="remositoryoverview">
			<h2>{$this->show(_DOWN_EMPTY_REPOSITORY)}</h2>
			<p>
				{$this->showHTML(_DOWN_NO_CATS)}
			</p>
		</div>
		
EMPTY_HTML;

	}
}
