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

class remositoryContainer extends remositoryAbstract {
	/** @var int ID for container record in database */
	public $id=0;
	/** @var int ID of parent container in database if a folder */
	public $parentid=0;
	/** @var string Name of container */
	public $name='';
	/** @var string Alias for container name - used in SEF */
	public $alias='';
	/** @var string Path for storing files */
	public $filepath='';
	/** @var string Container description */
	public $description='';
	/** @var bool Is the container published? */
	public $published=false;
	/** @var int Count of contained folders */
	public $foldercount=0;
	/** @var int Files in the container count */
	public $filecount=0;
	/** @var string Icon - not sure how this is used */
	public $icon='';
	/** @var Visitor options 1=upload, 2=download, 3=both, 0=neither */
	public $registered='2';
	/** @var User options 1=upload, 2=download, 3=both, 0=neither */
	public $userupload='3';
	/** @var bool Count downloads using a subscription manager if present */
	public $countdown=0;
	/** @var bool Descendants included in this container download count via subscription manager */
	public $childcountdown=0;
	/** @var bool Count uploads using a subscription manager if present */
	public $countup=0;
	/** @var bool Descendants included in this container upload count via subscription manager */
	public $childcountup=0;
	/** @var bool Is the file to be stored as a text string? */
	public $plaintext=0;
	/** @var int Group of users that has access to this container */
	public $groupid=0;
	/** @var int Editor group of users */
	public $editgroup=0;
	/** @var bool Auto-approve for Admin - Yes or No (Global applies) */
	public $adminauto=0;
	/** @var bool Auto-approve for user - Yes or No (Global applies)*/
	public $userauto=0;
	/** @var int Auto-approve group of users */
	public $autogroup=0;

	/**
	* File object constructor
	* @param int Container ID from database or null
	*/
	public function __construct ( $id=0 ) {
		$this->id = $id;
		if ($id) {
			$cmanager = remositoryContainerManager::getInstance();
			$category = $cmanager->getContainer($id);
			if ($category) $this->setValues($category);
		}
	}

	protected function tableName () {
		return '#__downloads_containers';
	}

	function delete () {
		$manager = remositoryContainerManager::getInstance();
		$manager->delete($this->id);
	}

	function deleteAll () {
		$folders = $this->getChildren(false);
		foreach ($folders as $folder) $folder->deleteAll ();
		$files = $this->getFiles(true);
		foreach ($files as $file) $file->deleteFile();
		$tempfiles = $this->getTempFiles();
		foreach ($tempfiles as $file) $file->deleteFile();
		$this->delete();
	}

	function saveValues () {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		$this->forceBools();
		if ($this->id == 0) {
			$sql = $this->insertSQL();
			remositoryRepository::doSQL ($sql);
			$this->id = $database->insertid();
		}
		else {
			$sql = $this->updateSQL();
			remositoryRepository::doSQL ($sql);
		}
	}

	function setMetaData () {
		$interface = remositoryInterface::getInstance();
		$interface->prependMetaTag('description', strip_tags($this->name));
		if ($this->keywords) $interface->prependMetaTag('keywords', $this->keywords);
		else $interface->prependMetaTag('keywords', $this->name);
	}

	function isCategory () {
		if ($this->parentid == 0) return true;
		else return false;
	}

	function getCategoryName ($showself=false) {
		$category = $this->getCategory();
		if ($this->parentid OR $showself) return $category->name;
		return '*';
    }

    function getCategory () {
		$container = $this;
		while (is_object($container)) {
			$category = $container;
			$container = $category->getParent();
		}
		return $category;
	}

    function getFamilyNames ($include=false) {
    	$names = '';
    	$parent = $this->getParent();
    	if ($parent AND $parent->parentid) {
    		$names .= '/'.$parent->name;
    		$grandparent = $parent->getParent();
    		if ($grandparent AND $grandparent->parentid) {
    			$names = '/'.$grandparent->name.$names;
				$greatgrandparent = $grandparent->getParent();
				if ($greatgrandparent->parentid) $names = '..'.$names;
			}
    	}
    	if ($include AND $this->id AND $this->parentid) $names = $names.'/'.$this->name;
    	if ($names) return $names;
    	return '-';
    }

	function downloadForbidden (&$user) {
		$authoriser = aliroAuthoriser::getInstance();
		if ($authoriser->checkPermission ('aUser', $user->id, 'download', 'remosFolder', $this->id)
		OR $authoriser->checkPermission ('aUser', $user->id, 'edit', 'remosFolder', $this->id)
		) return false;
		if ($user->isLogged()) {
			echo '<br/>&nbsp;<br/> '._DOWN_MEMBER_ONLY_WARN.$this->name;
			return true;
		}
		echo '<br/>&nbsp;<br/> '._DOWN_REG_ONLY_WARN;
		return true;
	}
	
	function isCounted () {
		if ($this->countdown) return true;
		else {
			$ancestor = $this;
			while ($ancestor = new remositoryContainer($ancestor->parentid)) {
				if ($ancestor->countdown AND $ancestor->childcountdown) return true;
			}
		}
		return false;
	}

	function getChildren ($published=true, $search='') {
		return remositoryContainerManager::getInstance()->getChildren($this->id, $published, $search);
	}

	function descendantSQL ($operation, $actions='') {
	    return "$operation #__downloads_containers AS c, #__downloads_structure AS s $actions WHERE s.item=c.id AND s.container=$this->id AND s.item!=s.container";
	}

	function getDescendants ($search='') {
		$manager = remositoryContainerManager::getInstance();
		if ($this->id) {
			$ids = $this->getDescendantIDs ($search);
			return $manager->getFromIDs($ids);
		}
		else return $manager->getFolders($search);
	}

	function getDescendantIDs ($search='') {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		if ($this->id) {
			$search = $interface->getEscaped($search);
			$sql = $this->descendantSQL('SELECT c.id FROM');
			if ($search) $sql .= " AND LOWER(name) LIKE '%$search%'";
			$sql .= ' ORDER BY name';
			$database->setQuery($sql);
			$result = $database->loadResultArray();
		}
		return empty($result) ? array() : $result;
	}

	function makeDescendantsInherit () {
	    $fields = $this->inheritableFields();
	    foreach ($fields as $field) {
	    	$value = $this->$field;
	        $update[] = "c.$field='$value'";
		}
		$setter = 'SET '.implode(', ',$update);
	    $sql = $this->descendantSQL('UPDATE', $setter);
	    remositoryRepository::doSQL($sql);
	}

	function moveFilesAsNecessary ($inherit=false) {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		if ($inherit) {
			$containerids = $this->getDescendantIDs();
			array_push($containerids, $this->id);
		}
		else $containerids = array($this->id);
		$selector = implode(',', $containerids);
		if (!$this->filepath AND !$this->plaintext) $isblob = 1;
		else $isblob = 0;
		$sql = "SELECT id, filepath, realname, isblob, plaintext, realwithid FROM #__downloads_files "
		." WHERE containerid IN($selector) AND (filepath != '$this->filepath' OR isblob != $isblob OR plaintext != $this->plaintext)";
		$database->setQuery($sql);
		$files = $database->loadObjectList();
		$physical = new remositoryPhysicalFile();
		if ($files) foreach ($files as $file) {
			$physical->setData($file->filepath.$file->realname, $file->id, $file->isblob, $file->plaintext, $file->realwithid);
			if ($physical->moveTo($this->filepath.$file->realname, $file->id, $isblob, $this->plaintext, true)) {
				$database->setQuery("UPDATE #__downloads_files "
				." SET filepath='$this->filepath', isblob=$isblob, plaintext=$this->plaintext, realwithid=1"
				." WHERE id = $file->id");
				$database->query();
			}
			else echo 'move failed';
		}
	}

	function inheritableFields () {
		return array ('filepath');
	}

	function memoContainer ($container) {
	    $fields = $this->inheritableFields();
	    foreach ($fields as $field) {
	        $this->$field = $container->$field;
	    }
	 }

	function isDownloadable (&$user) {
		if ($user->isAdmin()) return true;
		$authoriser = aliroAuthoriser::getInstance();
		return ($authoriser->checkPermission ('aUser', $user->id, 'download', 'remosFolder', $this->id)
		OR $authoriser->checkPermission ('aUser', $user->id, 'edit', 'remosFolder', $this->id)
		);
	}

	function getVisibleChildren ($user) {
		$manager = remositoryContainerManager::getInstance();
		return $manager->getVisibleChildren ($this->id, $user);
	}

	function checkFilePath () {
		if ($this->plaintext) $this->filepath = '';
		else {
			$this->filepath=trim(str_replace("\\","/",$this->filepath));
			if (!$this->filepath) {
				$repository = remositoryRepository::getInstance();
				if (!$repository->Use_Database) {
					if ($parent = $this->getParent() AND $parent->filepath) $this->filepath = $parent->filepath;
					else $this->filepath = $repository->Down_Path;
				}
			}
			if ($this->filepath) {
				$dir = new remositoryDirectory($this->filepath, true);
				$this->filepath = $dir->path;
			}
		}
	}

	function getParent () {
		$manager = remositoryContainerManager::getInstance();
		$parent = $manager->getParent($this->parentid);
		return $parent;
	}

	function increment ($by='0') {
		$parent = $this->getParent();
		if ($parent != null) $parent->increment($by);
		$this->filecount = $this->filecount+$by;
		$sql="UPDATE #__downloads_containers SET filecount=$this->filecount WHERE id=$this->id";
		remositoryRepository::doSQL($sql);
	}

	function areFilesVisible (&$user) {
		$repository = remositoryRepository::getInstance();
		if ($repository->See_Files_no_download OR $user->isAdmin()) return true;
		return $this->isDownloadable($user);
	}


	function getFiles ($published, $orderby=_REM_DEFAULT_ORDERING, $search='', $limitstart=0, $limit=0, $descendants=false) {
		$sql = remositoryFile::getFilesSQL($published, false, $this->id, $descendants, $orderby, $search, $limitstart, $limit);
		return remositoryRepository::doSQLget($sql, 'remositoryFile');
	}
	
	function getFileCount ($published, $orderby=_REM_DEFAULT_ORDERING, $search='', $descendants=false) {
		$sql = remositoryFile::getFilesSQL($published, true, $this->id, $descendants, $orderby, $search);
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		$database->setQuery($sql);
		return $database->loadResult();
	}
	
	function getFeaturedFiles ($published) {
		return remositoryFile::getFeaturedFiles($published, $this->id);
	}

	function getFilesCount ($search='', $remUser, $descendants=false) {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		if ($remUser->isAdmin()) $published = false;
		else $published = true;
		$sql = remositoryFile::getFilesSQL($published, true, $this->id, $descendants, 2, $search);
		$database->setQuery( $sql );
		return $database->loadResult();
	}

	function setFileCount ($chain=null) {
		$this->filecount = 0;
		$this->foldercount = 0;
		if (is_array($chain)) {
			$sql = "DELETE FROM #__downloads_structure WHERE item=$this->id";
			remositoryRepository::doSQL($sql);
			$chain[] = $this->id;
			foreach ($chain as $containerid) {
				$sql = "INSERT INTO #__downloads_structure (container, item) VALUES ($containerid, $this->id)";
				remositoryRepository::doSQL($sql);
			}
		}
		$children = $this->getChildren(false);
		foreach ($children as $child) {
			$counts = $child->setFileCount($chain);
			$this->filecount = $this->filecount + $counts[0];
			$this->foldercount = $this->foldercount + $counts[1];
		}
		$this->filecount = $this->filecount + remositoryFile::getCountInContainer($this->id,true);
		$this->foldercount = $this->foldercount + count($children);
		$sql="UPDATE #__downloads_containers SET filecount=$this->filecount, foldercount=$this->foldercount WHERE id=$this->id";
		remositoryRepository::doSQL($sql);
		return array($this->filecount,$this->foldercount);
	}

	function getTempFiles ($search='') {
		$interface = remositoryInterface::getInstance();
		if ($this->id == 0) return array();
		// Change for multiple repositories
		// $repnum = max(1,remositoryRepository::getParam($_REQUEST, 'repnum', 1));
		// $sql = "SELECT * FROM #__downloads_files WHERE repnum = $repnum AND containerid = $this->id AND metatype > 0";
		$sql = "SELECT * FROM #__downloads_files WHERE containerid = $this->id AND metatype > 0";
		if ($search) {
			$search = $interface->getEscaped($search);
			$sql .= " AND LOWER(filetitle) LIKE '%$search%'";
		}
		$results = remositoryRepository::doSQLget($sql,'remositoryTempFile');
		foreach ($results as $key=>$result) $results[$key]->containerid = -$result->containerid;
		return $results;
	}

	// This is used admin side, and wants all containers, whether they can accept files or not
	function getSelectList ($type, $parm, &$user, $notThis=0) {
		$repository = remositoryRepository::getInstance();
		if ($this->id) $selector[] = $repository->makeOption(0,_DOWN_NO_PARENT);
		else $selector = array();
		$this->addSelectList('',$selector,$notThis,$user);
		return $repository->selectList( $selector, $type, $parm, $this->id );
	}

	// This is used on user side for uploads, only want containers that can accept files
	function getPartialSelectList ($type, $parm, &$user, $notThis=0) {
		$repository = remositoryRepository::getInstance();
		$selector = array();
		$this->addSelectList('', $selector, $notThis, $user, true);
		return (count($selector) ? $repository->selectList( $selector, $type, $parm, $this->id ) : '');
	}

	function addSelectList ($prefix, &$selector, $notThis, &$user, $usable=false) {
		if ($notThis AND $this->id == $notThis) return;
		$repository = remositoryRepository::getInstance();
		if ($user->isAdmin()) {
			$published = false;
			$addthis = true;
		}
		else {
			$published = true;
			$authoriser = aliroAuthoriser::getInstance();
			if ($authoriser->checkPermission ('aUser', $user->id, 'upload', 'remosFolder', $this->id)
			OR $authoriser->checkPermission ('aUser', $user->id, 'edit', 'remosFolder', $this->id)
			) $addthis = true;
			else $addthis = false;
		}
		if ($usable AND $this->filepath AND (!file_exists($this->filepath) OR !is_writeable($this->filepath))) $addthis = false;
		if ($addthis AND ((0 == $notThis) OR ($this->id != $notThis))) {
			$name = $this->id ? $this->name : _DOWN_NO_PARENT;
			$selector[] = $repository->makeOption($this->id, $prefix.htmlspecialchars($name));
		}
		foreach ($this->getChildren($published) as $container) $container->addSelectList($prefix.$this->name.'/',$selector,$notThis,$user);
	}

	function getURL () {
		$func = remositoryRepository::getParam ($_REQUEST, 'func');
		$type = 'direct' == substr($func,0,6) ? 'directlist' : 'select';
		return remositoryRepository::getInstance()->remositoryFunctionURL($type, $this->id);
	}
	
	private function isLoneCategory () {
		if (0 == $this->parentid) {
			$manager = remositoryContainerManager::getInstance();
			return 1 == count($manager->getCategories()) ? true : false;
		}
		return false;
	}

	public function showPathway () {
		$interface = remositoryInterface::getInstance();
		$parent = $this->getParent();
		if ($parent != null) $parent->showPathway();
		if ($this->isLoneCategory()) return;
		?>
		<img src="<?php echo $interface->getCfg('live_site') ?>/images/M_images/arrow.png" alt="arrow" />
		<?php
		echo $this->getURL();
		echo htmlspecialchars($this->name);
		echo '</a>';
	}

	// Alternative to use the CMS pathway instead of a separate Remository one
	public function showCMSPathway () {
		$parent = $this->getParent();
		if (!is_null($parent)) $parent->showCMSPathway();
		if ($this->isLoneCategory()) return;
		$interface = remositoryInterface::getInstance();
		$link = remositoryRepository::getInstance()->RemositoryRawFunctionURL('select', $this->id);
		$interface->appendPathWay(htmlspecialchars($this->name), $link);
	}

	public function getIcons () {
		return remositoryRepository::getIcons ('folder_icons');
	}

	public function togglePublished ($idlist, $value) {
		$cids = implode( ',', $idlist );
		$sql = "UPDATE #__downloads_containers SET published=$value". "\nWHERE id IN ($cids)";
		remositoryRepository::doSQL ($sql);
	}

}