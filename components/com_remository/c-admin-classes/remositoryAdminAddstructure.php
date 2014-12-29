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

class remositoryMiniFile {
	var $id = 0;
	var $realname = '';
}

class remositoryAdminAddstructure extends remositoryAdminControllers {

	function listTask () {
	    $containerID = 0;
		$clist = $this->repository->getSelectList(false,$containerID,'cfid','class="inputbox"',$this->remUser);
		$view = $this->admin->newHTMLClassCheck ('listAddstructureHTML', $this, 0, $clist);
		if ($view AND $this->admin->checkCallable($view, 'view')) $view->view();
	}

	function setFileCommonData (&$file) {
		$file->license = remositoryRepository::getParam($_POST, 'license', '');
		$file->licenseagree = remositoryRepository::getParam($_POST, 'licenseagree', '');
		$file->fileversion = remositoryRepository::getParam($_POST, 'fileversion', $this->repository->Default_Version);
		$file->fileauthor = remositoryRepository::getParam($_POST, 'fileauthor', '');
		$file->filehomepage = remositoryRepository::getParam($_POST, 'filehomepage', '');
		$file->icon = remositoryRepository::getParam($_POST, 'icon', '');
	}

	function mkSubFolder ($filepath) {
	    if (!file_exists($filepath)) $newdir = new remositoryDirectory ($filepath, true);
	}

	function stdpath ($path) {
		if (substr($path,-1) != '/') $path .= '/';
		$filepath=str_replace("\\","/",$path);
		$filepath=str_replace("\\","/",$path);
		return $filepath;
	}

	function addOneLevel ($path, &$container, &$extensions, $extensiontitle, $delete, $recurse) {
		if (substr($path,-1) != '/') $path .= '/';
		$existing = remositoryRepository::doSQLget("SELECT id, realname FROM #__downloads_files WHERE filepath = '$path'", 'remositoryMiniFile');
		$existingfiles = array();
		foreach ($existing as $file) $existingfiles[] = remositoryPhysicalFile::basicNameWithID($file->id, $file->realname);
		unset($existing);
		$directory = new remositoryDirectory($path);
		$files = $directory->listFiles();
		$newfile = new remositoryFile ();
		$newfile->containerid = $container->id;
		$newfile->memoContainer($container);
		$newfile->published = 1;
		$newfile->submittedby = $this->remUser->id;
		$this->setFileCommonData($newfile);
		$chosenicon = $newfile->icon;
		// Extensions are prechecked
		$newfile->validate(false);
		foreach ($files as $file) {
			@set_time_limit(25);
			$ext = remositoryAbstract::lastPart($file,'.');
			if ($extensions != '*' AND !in_array($ext, $extensions)) continue;
			if (in_array($file, $existingfiles)) continue;
			$filepath = $path.$file;
			$physical = new remositoryPhysicalFile ();
			$physical->setData($filepath, 0, 0, 0, false);
			$newfile->id = 0;
			$newfile->filetitle = '';
			$newfile->icon = $chosenicon;
			$newfile->storePhysicalFile ($physical, $extensiontitle, false);
			if ($delete) @unlink($filepath);
		}
		unset($existing, $files, $newfile);
		if ($recurse) {
			$directories = $directory->listFiles('','dir');
			foreach ($directories as $newdir) {
				$dirpath = $path.$newdir;
				$children = $container->getChildren(false);
				foreach ($children as $child) {
					if ($newdir == $child->name) {
						$folder =& $child;
						break;
					}
				}
				if (!isset($folder)) {
					$folder = new RemositoryContainer ();
					$folder->parentid = $container->id;
					$folder->name = $newdir;
					$folder->plaintext = $container->plaintext;
					if ($container->filepath) $folder->filepath = $container->filepath.$newdir.'/';
					if (!file_exists($folder->filepath))$newdir = new remositoryDirectory ($folder->filepath, true);
					$folder->published = 1;
					$folder->saveValues();
					$this->savePermissions($folder);
				}
				if (1 < $recurse) $this->addOneLevel ($dirpath, $folder, $extensions, $extensiontitle, $delete, $recurse);
				unset($folder);
			}
		}
	}

	function saveTask () {
	    $basedir = str_replace("'", '', remositoryRepository::getParam ($_REQUEST, 'basedir', ''));
	    $interface = remositoryInterface::getInstance();
	    $basedir = $interface->getEscaped($basedir);
		$dir = new remositoryDirectory($basedir);
		// Change for multiple repositories
		// if (!$dir->isDirectory()) $this->interface->redirect( "index2.php?option=com_remository&act=addstructure&repnum=".$this->repnum, _DOWN_STRUCT_NO_DIR );
		if (!$dir->isDirectory()) $this->interface->redirect( "index2.php?option=com_remository&act=addstructure", _DOWN_STRUCT_NO_DIR );
	    $recurse = remositoryRepository::getParam($_REQUEST, 'recurse', 0);
	    $extensionlist = remositoryRepository::getParam($_REQUEST, 'extensionlist', '');
	    if (trim($extensionlist) == '*') {
			$extensions = '*';
			$badfiles = $dir->findBadExtension($recurse);
			$view = $this->admin->newHTMLClassCheck ('listAddstructureHTML', $this, 0, '');
			if (count($badfiles)) {
				if ($view AND $this->admin->checkCallable($view, 'badfiles')) $view->badfiles($badfiles);
				else die('Bad file extensions');	
				return true;
			}
		}
	    else {
			$extensions = explode(',', $extensionlist);
	    	$extensions = array_map('trim', $extensions);
	    }
		$extensiontitle = remositoryRepository::getParam($_POST, 'extensiontitle', '');
	    $container = new remositoryContainer($this->admin->currid);
	    if ($basedir AND $this->admin->currid) $this->addOneLevel ($basedir, $container, $extensions, $extensiontitle, False, $recurse);
		$_SESSION['remositoryResetCounts'] = 1;
		$this->backTask( _DOWN_STRUCTURE_ADDED );
	}
	
	// Private function for tidiness
	function savePermissions ($container) {
		$defaults = array(
		'upload' => 'Registered',
		'edit' => 'Nobody'
		);
		$authoriser = aliroAuthorisationAdmin::getInstance();
		foreach ($defaults as $action=>$role) {
			$authoriser->permit ($role, 2, $action, 'remosFolder', $container->id);
		}
	}

}