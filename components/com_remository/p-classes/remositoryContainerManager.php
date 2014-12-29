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

class remositoryContainerManager {
	private static $instance = null;
	private $remository_links = array();
	private $remository_containers = array();
	private $familytree = array();

	private function __construct () {
		$sql = 'SELECT * FROM #__downloads_containers ORDER BY sequence, name';
		$this->remository_containers = remositoryRepository::doSQLget($sql,'remositoryContainer');
		foreach ($this->remository_containers as $i=>$container) {
			$this->remository_links[$container->id] = $i;
			$this->familytree[$container->parentid][$container->id] = $i;
		}
	}

    public static function getInstance () {
    	return is_object(self::$instance) ? self::$instance : self::$instance = new self();
    }

    function count () {
        return count($this->remository_containers);
    }

    public function getAll () {
    	return $this->remository_containers;
	}

    function getFromIDs ($ids) {
    	$result = array();
    	foreach ($ids as $id) $result[] = $this->remository_containers[$this->remository_links[$id]];
    	return $result;
    }

	function getChildren ($id, $published=true, $search='') {
		if (isset($this->familytree[$id])) foreach ($this->familytree[$id] as $i) {
			$container = $this->remository_containers[$i];
			if (($published AND $container->published == 0) OR ($search AND strpos($container->name, $search) === false)) continue;
			$children[] = $container;
		}
		return isset($children) ? $children : array();
	}

	function getVisibleChildren ($id, &$user) {
		$repository = remositoryRepository::getInstance();
		if (isset($this->familytree[$id])) foreach ($this->familytree[$id] as $i) {
			if ($user->isAdmin()) $children [] =& $this->remository_containers[$i];
			else {
				$container = $this->remository_containers[$i];
				if (0 != $container->published AND ($repository->See_Containers_no_download OR $user->canDownloadContainer($container->id))) {
					$children[] =& $this->remository_containers[$i];
				}
			}
		}
		return isset($children) ? $children : array();
	}
	
	public function getVisibleDescendants ($id, $user) {
		$children = $this->getVisibleChildren($id, $user);
		foreach ($children as $child) {
			$grandfamily = $this->getVisibleDescendants($child->id, $user);
			$descendants = empty($descendants) ? $grandfamily : (empty($grandfamily) ? $descendants : array_merge($grandfamily, $descendants));
		}
		return empty($descendants) ? $children : array_merge($children, $descendants);
	}

	public function getContainer ($id) {
		if ($id AND isset($this->remository_links[$id])) $container = $this->remository_containers[$this->remository_links[$id]];
		else {
			$container = new remositoryContainer();
			$container->name = _INVALID_ID;
		}
		return $container;
	}

	public function getParent ($id) {
		if ($id AND isset($this->remository_links[$id])) $parent = $this->remository_containers[$this->remository_links[$id]];
		else $parent = null;
		return $parent;
	}

	public function getFullPath ($id) {
		$container = $this->getContainer($id);
		return $container->parentid ? $this->getFullPath($container->parentid).' / '.$container->name : $container->name;
	}

	public function delete ($id) {
		$sql = "DELETE FROM #__downloads_containers WHERE id=$id";
		remositoryRepository::doSQL($sql);
		if (isset($this->remository_links[$id]) AND isset($this->remository_containers[$this->remository_links[$id]])) {
			$container = $this->remository_containers[$this->remository_links[$id]];
			$parentid = $container->parentid;
			unset($container);
			unset($this->remository_containers[$this->remository_links[$id]]);
			unset($this->remository_links[$id]);
			if (isset($this->familytree[$parentid][$id])) unset($this->familytree[$parentid][$id]);
		}
	}

	public function makeSelectedList ($containers, $type, $parm) {
		$repository = remositoryRepository::getInstance();
		$ids = explode(',', $containers);
		foreach ($ids as $id) {
			$id = intval($id);
			if (isset($this->remository_links[$id])) $selector[] = $repository->makeOption($id, $this->remository_containers[$this->remository_links[$id]]->name);
		}
		if (isset($selector)) return $repository->selectList ($selector, $type, $parm);
		else return '';
	}

	public function getFilePathData ($path='') {
		$defaultdown = remositoryRepository::getInstance()->Down_Path.'/';
		foreach ($this->remository_containers as $container) {
			if ($path == '' OR ($path AND ($container->filepath == $path OR $container->filepath == ''))) {
				if ($container->filepath) $results[$container->filepath][] = $container->id;
				else $results[$defaultdown][] = $container->id;
			}
		}
		return empty($results) ? array() : $results;
	}
	
	public function getFolders ($search='') {
		$folders = array();
		foreach ($this->remository_containers as $i=>$container) {
			if ($search AND strpos(strtolower($container->name), $search) === false) continue;
			$folders[] = $this->remository_containers[$i];
		}
		return $folders;
	}

	public function getCategories ($published=false, $search='') {
		return $this->getChildren(0, $published, $search);
	}

	public function getCategoryIDs ($published=false, $search='') {
		$categories = $this->getCategories($published, $search);
		foreach ($categories as $category) $results[] = $category->id;
		return isset($results) ? $results : array();
	}

	public function getTreeAdds ($user, $treename='remostree', $addfiles=false, $root=0) {
		$repository = remositoryRepository::getInstance();
		$home = $repository->RemositoryBasicFunctionURL();
		$maintitle = $root ? $this->getContainer($root)->name : $repository->Main_Page_Title;
		$adds = <<<TREE_ROOT
		
		$treename.add(0, -1, '$maintitle', '$home');
		
TREE_ROOT;

		$max = 0;
		$descendants = $this->getVisibleDescendants($root, $user);
		foreach ($descendants as $container) {
			$max = max($max, $container->id);
			$parent = $container->parentid ? $container->parentid : 0;
			$link = $repository->RemositoryBasicFunctionURL('select', $container->id);
			$adds .= <<<ADD_ONE

		$treename.add($container->id, $parent, '$container->name', '$link');

ADD_ONE;

		}
		
		if ($addfiles AND !empty($descendants)) {
			foreach ($descendants as $descendant) $dids[] = $descendant->id;
			$didlist = implode(',', $dids);
			$interface = remositoryInterface::getInstance();
			$database = $interface->getDB();
			// Change for multiple repositories
			// $repnum = max(1, remositoryRepository::getParam($_REQUEST, 'repnum', 1));
			// $database->setQuery("SELECT id, filetitle, containerid FROM #__downloads_files WHERE repnum = $repnum");
			$database->setQuery("SELECT id, filetitle, containerid FROM #__downloads_files WHERE containerid IN ($didlist)");
			$files = $database->loadObjectList();
			if ($files) foreach ($files as $file) {
				$max++;
				$link = $repository->RemositoryBasicFunctionURL('fileinfo', $file->id);
				$adds .= <<<ADD_FILE
				
		$treename.add($max, $file->containerid, '$file->filetitle', '$link');
			
ADD_FILE;

			}

		}
		return $adds;
	}

}
