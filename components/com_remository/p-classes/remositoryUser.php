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

class remositoryUser {
	/** @var int ID for the user in the database */
	public $id=0;
	/** @var bool Is the current user of administrator status? */
	public $admin=false;
	/** @var bool Is the current user logged in?  */
	public $logged=false;
	/** @var string User name if loggged in */
	public $name='';
	/** @var string User full name if logged in */
	public $fullname='';
	/** @var string User type if logged in */
	public $usertype='';
	/** @var string User current IP address */
	public $currIP='';
	/** @var array Downloads so far today by file ID */
	public $downloads = array();
	/** @var int Total downloads all files today */
	public $totaldown = 0;
	/** @var array Container IDs where user does not have permission */
	public $refused=array();

	/**
	* File object constructor
	* @param int Directory full path
	*/
	public function __construct ( $id=0, $my=null ) {
		$interface = remositoryInterface::getInstance();
		$this->id = $id;
		if ($id) {
			if (!$my) $my = $interface->getIdentifiedUser($id);
			if ($my->gid) {
				$this->name = $my->username;
				$this->fullname = $my->name;
				$this->usertype = $my->usertype;
				$this->logged = true;
				if ((strtolower($my->usertype)=='administrator') OR (strtolower($my->usertype)=='superadministrator')
						OR (strtolower($my->usertype)=='super administrator')){
					$this->admin = true;
				}
			}
			if (isset($my->jaclplus)) $this->jaclplus = $my->jaclplus;
		}
		$this->currIP = getenv('REMOTE_ADDR');
		$authoriser = aliroAuthoriser::getInstance();
		$this->refused = $authoriser->getRefusedList ('aUser', $this->id, 'remosFolder', 'download,edit');
	}
	
	public function isAdmin () {
		return $this->admin;
	}
	public function isUser () {
		if ($this->isAdmin()) return false;
		return $this->isLogged();
	}
	public function isLogged () {
		return $this->logged;
	}
	public function fullname () {
		return $this->fullname;
	}
	public function canDownloadContainer($id) {
		return (!in_array($id, $this->refused));
	}
	public function uploadsToday () {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();

		$today = date('Y-m-d');
		// Change for multiple repositories
		// $repnum = max(1, remositoryRepository::getParam($_REQUEST, 'repnum', 1));
		// $sql="SELECT COUNT(*) from #__downloads_files WHERE repnum = $repnum AND submittedby=".$this->id." AND submitdate LIKE '".$today."%'";
		$sql="SELECT COUNT(*) from #__downloads_files WHERE submittedby=".$this->id." AND submitdate LIKE '".$today."%'";
		$database->setQuery($sql);
		return $database->loadResult();
	}

	public function allowUploadCheck ($container, $controller) {
		if ($this->isAdmin()) return;
		$repository = remositoryRepository::getInstance();
		if (!$repository->Allow_User_Sub) {
			echo "<script> alert('"._DOWN_NOT_AUTH."'); window.history.go(-1); </script>\n";
			exit();
		}
		$authoriser = aliroAuthoriser::getInstance();
		if ($authoriser->checkPermission ('aUser', $this->id, 'upload', 'remosFolder', $container->id)
		OR $authoriser->checkPermission ('aUser', $this->id, 'edit', 'remosFolder', $container->id)) {
			if ($this->logged) {
				if ($this->uploadsToday() > $repository->Max_Up_Per_Day) {
					$view = new remositoryErrorDisplaysHTML($controller);
					$view->uploadLimit();
					exit;
				}
			}
			return;
		}
		$view = new remositoryErrorDisplaysHTML($controller);
		$view->noaccess($container);
		exit;

	}

	public function hasAutoApprove ($container) {
		// Remove this line if visitors can self-approve - 
		// containers must also be set with self approve permission for Visitor
		if (!$this->isLogged()) return false;
		$repository = remositoryRepository::getInstance();
		if ($this->isAdmin()) {
			if ($repository->Enable_Admin_Autoapp) return true;
		}
		$authoriser = aliroAuthoriser::getInstance();
		if ($authoriser->checkPermission ('aUser', 0, 'selfApprove', 'remosFolder', $container->id)) return false;
		if ($authoriser->checkPermission ('aUser', $this->id, 'selfApprove', 'remosFolder', $container->id)) return true;
		return false;
	}

	public static function superAdminMail () {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		$sql="select email, name from #__users where usertype='superadministrator' or usertype='super administrator'";
		$database->setQuery( $sql );
		$row=null;
		$result = $database->loadObject( $row );
		if (is_object($result)) $row = $result;
		if ($row) return $row->email;
    	else return null;
	}

	private function getDownloadInfo () {
		static $gotinfo = false;
		if ($gotinfo) return;
		$results = $this->id ? $this->loggedCount() : $this->nonLoggedCount();
		if ($results) foreach ($results as $result) {
			$this->downloads[$result->fileid] = $result->number;
			$this->totaldown += $result->number;
		}
		$gotinfo = true;
	}

	private function nonLoggedCount () {
		$interface = remositoryInterface::getInstance();
		$ipaddress = getenv('REMOTE_ADDR');
		$database = $interface->getDB();
		$type = _LOG_DOWNLOAD;
		$database->setQuery("SELECT fileid, COUNT(fileid) AS number FROM #__downloads_log WHERE type = $type AND ipaddress = '$ipaddress' AND date > SUBDATE(NOW(), INTERVAL 24 HOUR) GROUP BY fileid");
		return $database->loadObjectList();
	}

	private function loggedCount () {
		$interface = remositoryInterface::getInstance();
		$database = $interface->getDB();
		$type = _LOG_DOWNLOAD;
		$database->setQuery("SELECT fileid, COUNT(fileid) AS number FROM #__downloads_log WHERE type = $type AND userid = $this->id AND date > SUBDATE(NOW(), INTERVAL 24 HOUR) GROUP BY fileid");
		return $database->loadObjectList();
	}

	public function downloadCount ($id) {
		$this->getDownloadInfo();
		return isset($this->downloads[$id]) ? $this->downloads[$id] : 0;
	}

	public function totalDown () {
		$this->getDownloadInfo();
		return $this->totaldown;
	}
	
	public static function mailPeopleViewingContainer ($containerid, $subject, $body, $fileid=0) {
		$interested = aliroAuthoriser::getInstance()->listAccessorsToSubject ('remosFolder', $containerid, 'aUser', 'download');
		if (!empty($interested)) {
			$interface = remositoryInterface::getInstance();
			$repository = remositoryRepository::getInstance();
			if ($fileid) $body .= sprintf(_DOWN_LINK_TO_FILE, $repository->remositoryBasicFunctionURL('fileinfo', $fileid));
			else $body .= sprintf(_DOWN_LINK_TO_CONTAINER, $repository->remositoryBasicFunctionURL('select', $containerid));
			$listed = implode(',', $interested);
			$database = $interface->getDB();
			$database->setQuery("SELECT name, email FROM #__users WHERE id IN ($listed)");
			$people = $database->loadObjectList();
			if (!empty($people)) {
				foreach ($people as $person) {
					$body = sprintf($body, $person->name);
					$interface->sendMail($person->email, $subject, $body);
					sleep(1);
				}
			}
		}
	}
}