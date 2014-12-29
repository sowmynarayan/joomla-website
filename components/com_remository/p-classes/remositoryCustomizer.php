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

class remositoryCustomizer {
	private $fileListFields = array();
	private $values = array();
	private $repository = null;
	
	public function __construct () {
		$this->fileListFields = array(
		array ('smalldesc', _DOWN_DESC_SMALL, 1),
		array ('submittedby', _DOWN_SUB_BY, 1),
		array ('submitdate', _DOWN_SUBMIT_DATE, 1),
		array ('filesize', _DOWN_FILE_SIZE, 1),
		array ('downloads', _DOWN_DOWNLOADS, 1),
		array ('vote_value', _DOWN_RATING, 1),
		array ('license', _DOWN_LICENSE, 0),
		array ('fileversion', _DOWN_FILE_VER, 0),
		array ('fileauthor', _DOWN_FILE_AUTHOR, 0),
		array ('filehomepage', _DOWN_FILE_HOMEPAGE, 0),
		array ('filedate', _DOWN_FILE_DATE, 0)
		);
		$this->repository = remositoryRepository::getInstance();
		$this->checkCustomizer();
	}
	
	public function getFileListFields () {
		return $this->fileListFields;
	}
	
	public function getCustomSpec () {
		return $this->values;
	}
	
	public function saveCustomSpec ($values) {
		$this->values = $values;
		$this->repository->customizer = serialize($this->values);
		$this->repository->saveValues();
	}

	private function checkCustomizer () {
		if ($this->repository->customizer) $this->values = unserialize($this->repository->customizer);
		else $this->values = array();
		$fields = $this->getFileListFields();
		if (!isset($this->values['S']) OR in_array(0, $this->values['S'])) {
			foreach ($fields as $key=>$farr) $this->values['S'][$key] = 10 + 10*$key;
			$changed = true;
		}
		if (!isset($this->values['A'])) {
			foreach ($fields as $key=>$farr) $this->values['A'][$key] = $farr[2];
			$changed = true;
		}
		if (!isset($this->values['B'])) {
			foreach ($fields as $key=>$farr) $this->values['B'][$key] = $farr[2];
			$changed = true;
		}
		if (!isset($this->values['C'])) {
			foreach ($fields as $key=>$farr) $this->values['C'][$key] = $farr[2];
			$changed = true;
		}
		if (!isset($this->values['D'])) {
			foreach ($fields as $key=>$farr) $this->values['D'][$key] = 1;
			$changed = true;
		}
		if (!empty($changed)) $this->saveCustomSpec($this->values);
	}
    
}