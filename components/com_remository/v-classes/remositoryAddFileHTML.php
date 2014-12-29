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

class remositoryAddFileHTML extends remositoryUserHTML {

	function fileInputBox ($title, $name, $value, $width) {
		echo <<<INPUT_BOX
		
			<p>
				<label for="$name">$title</label>
				<input class="inputbox" type="text" id="$name" name="$name" size="$width" value="$value" />
			</p>
		
INPUT_BOX;

	}

	function fileInputArea ($title, $maxsize, $name, $value, $rows, $cols, $editor) {
		echo "\n\t\t\t\t<p><label for='$name'>".$title;
		echo '</label>';
		if ($editor) {
			if ($maxsize) echo '<em>'.$maxsize.'</em>';
			echo "\n\t\t\t</p><div id='remositoryeditor'>";
			$interface = remositoryInterface::getInstance();
			$interface->editorArea( 'description', $value, $name, 500, 200, $rows, $cols );
			echo "\n\t\t\t</div>";
		}
		else {
			echo "<textarea class='inputbox' id='$name' name='$name' rows='$rows' cols='$cols'>$value</textarea>";
			echo '</p>';
			if ($maxsize) echo "<p class='remositorymax'><em>".$maxsize.'</em></p>';
		}
	}

	function uploadFileBox ($title, $suffix='') {
		echo "\n\t\t\t<p>";
		echo "<label for='userfile$suffix'>$title</label>";
		echo "\n\t\t\t\t<input class='text_area' type='file' id='userfile$suffix' name='userfile$suffix' />";
		echo "\n\t\t\t</p>";
	}

	function tickBoxField ($object, $property, $title) {
		if (is_object($object) AND $object->$property) $checked = "checked='checked'";
		else $checked = '';
		echo "\n\t\t\t<p>";
		echo "<label for='$property'>$title</label>";
		echo "\n\t\t\t\t<input type='checkbox' id='$property' name='$property' value='1' $checked />";
		echo "\n\t\t\t</p>";
	}

	function autoShortHandling ($file) {
		echo "\n\t\t\t<p>";
		echo "<label for='autoshort'>"._DOWN_AUTO_SHORT."</label>";
		if ($file->autoshort) {
			echo "\n\t\t\t<input type='checkbox' name='autoshort'id='autoshort' checked='checked' onclick='clearshort()' value='1' />";
			echo "\n\t\t\t<script type='text/javascript'>clearshort()</script>";
		}
		else echo "\n\t\t\t<input type='checkbox' name='autoshort' id='autoshort' onclick='clearshort()' value='1' />";
		echo "\n\t\t\t</p>";
	}

	function displayIcons ($object, $iconList) {
		if (is_object($object)) $currenticon = $object->icon;
		else $currenticon = '';
		?>
		<script type="text/javascript">
		function paste_strinL(strinL){
			var input=document.forms["adminForm"].elements["icon"];
			input.value=strinL;
		}
		</script>
		<p id='remositoryiconlist'>
			<label for='icon'><?php echo _DOWN_ICON; ?></label>
			<input class="inputbox" type="text" name="icon" id='icon' size="25" value="<?php echo $currenticon; ?>" />
			<p><?php echo $iconList; ?></p>
  		</p>
  		<?php
	}

	function addfileHTML($clist, &$file)
	{
		$this->pathwayHTML(null);
		if ($clist == '') {
			echo _DOWN_FILE_SUBMIT_NOCHOICES;
			return;
		}
		$this->addFileScripts();
		$interface = remositoryInterface::getInstance();
		if (!$this->remUser->isLogged()) $interface->initEditor();
		$formurl = remositoryRepository::getInstance()->RemositoryBasicFunctionURL('savefile');
		echo "\n\t<form name='adminForm' enctype='multipart/form-data' action='$formurl' method='post'>";
		echo "\n\t<div id='remositoryupload'>\n";
		echo <<<HIDDEN
		
			<input type="hidden" name="repnum" value="$this->repnum" />
			<input type="hidden" name="oldid" value="$file->id" />

HIDDEN;

		$iconList = remositoryFile::getIcons();
		if ( $this->remUser->isAdmin() OR ($this->repository->User_Remote_Files)) {
			$remoteok = true;
			$instruct1 = _SUBMIT_INSTRUCT1;
			$instruct2 = _SUBMIT_INSTRUCT2;
		}
		else {
			$remoteok = false;
			$instruct1 = _SUBMIT_INSTRUCT3;
		}
		echo "\n\t\t<h2>"._SUBMIT_HEADING.'</h2>';
		echo "\n\t\t<p>".$this->repository->RemositoryFunctionURL('addmanyfiles')._DOWN_ADD_NUMBER_FILES.'</a></p>';
		echo "\n\t<div id='remositoryuplocal'>";
		echo "\n\t\t<p>".$instruct1.'</p>';
		$this->uploadFileBox(_SUBMIT_NEW_FILE);
		echo "\n\t</div>";
		if ($remoteok) {
			echo "\n\t<div id='remositoryupremote'>";
			echo "\n\t\t<p>".$instruct2.'</p>';
			$this->fileInputBox(_DOWNLOAD_URL, 'url', ($file->url ? $file->url : 'http://'), 50);
			$this->fileInputBox(_DOWN_FILE_DATE,'filedate',$file->filedate,25);
			$this->fileInputBox(_DOWN_FILE_SIZE,'filesize',$file->filesize,25);
			echo "\n\t</div>";
		}
		echo "\n\t<div id='remositoryuploadinfo'>";
		$thumbs = new remositoryThumbnails($file);
		if ($freecount = $thumbs->getFreeCount()) {
			for ($i = 0; $i < $freecount; $i++) {
				$this->uploadFileBox(sprintf(_DOWN_ADDFILE_THUMBNAIL ,$i+1), $i+1);
			}
		}
		echo "\n\t\t<dl>";
		$this->fileOutputBox(_DOWN_SUGGEST_LOC, $clist, false);
		echo "\n\t\t</dl>";
		$this->fileInputBox(_DOWN_FILE_TITLE,'filetitle',$file->filetitle,25);
		$this->fileInputArea(_DOWN_DESC, _DOWN_DESC_MAX, 'description', $file->description, 10, 50, true);
		$this->fileInputArea(_DOWN_DESC_SMALL, _DOWN_DESC_SMALL_MAX, 'smalldesc', $file->smalldesc, 3, 50, false);
		$this->autoShortHandling($file);
		$this->fileInputArea(_DOWN_LICENSE, _DOWN_DESC_MAX, 'license', $file->license, 4, 50, false);
		$this->tickBoxField($file, 'licenseagree', _DOWN_LICENSE_AGREE);
		$this->fileInputBox(_DOWN_FILE_VER,'fileversion',$file->fileversion,25);
		$this->fileInputBox(_DOWN_FILE_AUTHOR,'fileauthor',$file->fileauthor,25);
		$this->fileInputBox(_DOWN_FILE_HOMEPAGE,'filehomepage',$file->filehomepage,50);
		if (0 == $thumbs->getMaxCount()) $this->fileInputBox(_DOWN_SCREEN,'screenurl',$file->screenurl,50);
		$this->displayIcons($file, $iconList);
		$buttontext = _SUBMIT_FILE_BUTTON;
		$cancelbutton = _DOWN_CANCEL_UPLOAD;
		echo "\n\t<input class='button' type='submit' name='submit' value='$buttontext' />";
		echo "\n\t<input class='button' type='submit' name='submit' value='$cancelbutton' />";
		echo "\n\t</div></div></form>";
	}

	function addFileScripts () {
		$interface = remositoryInterface::getInstance();
		?>
		<script type="text/javascript">
		function clearshort(){

				if (document.adminForm.autoshort.checked==true){
					if (document.adminForm.description.value!=""){
						if (document.adminForm.description.value.length>=(<?php echo $this->repository->Small_Text_Len-3; ?>)){
							document.adminForm.smalldesc.value=document.adminForm.description.value.substr(0,<?php echo $this->repository->Small_Text_Len-3; ?>) + "...";
						} else {
							document.adminForm.smalldesc.value=document.adminForm.description.value;
						}
					} else {
						document.adminForm.smalldesc.value="";
					}
					document.adminForm.smalldesc.disabled=true;
				} else {
					document.adminForm.smalldesc.value="";
					document.adminForm.smalldesc.disabled=false;
				}
			}
		</script>
		<script type="text/javascript">
        function submitbutton(pressbutton) {
                <?php $interface->getEditorContents( 'description' ); ?>
                submitform( pressbutton );
        }
        </script>
        <?php
	}

}