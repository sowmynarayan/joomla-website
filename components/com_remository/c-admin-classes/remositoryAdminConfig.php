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

class configSelector {
	var $description='';
	var $variablename='';

	function configSelector ($name, $desc, $optionlist) {
		$this->variablename = $name;
		$this->description = $desc;
		$this->optionlist = $optionlist;
	}
}

class remositoryAdminConfig extends remositoryAdminControllers {

	function remositoryAdminConfig ($admin) {
		remositoryAdminControllers::remositoryAdminControllers ($admin);
	    $_REQUEST['act'] = 'config';
	}

	function listTask (){
		// make a generic yes no list
		$yesno[] = $this->repository->makeOption( 0, _NO );
		$yesno[] = $this->repository->makeOption( 1, _YES );
		$yesnoboth[] = $this->repository->makeOption( 1, _NO );
		$yesnoboth[] = $this->repository->makeOption( 2, _YES );
		$yesnoboth[] = $this->repository->makeOption( 3, _DOWN_BOTH );
		// build the html select lists
		$newlist[] = new configSelector ('Use_Database', _DOWN_CONFIG39, $yesno, $yesno);
		$newlist[] = new configSelector ('Count_Down', _DOWN_CONFIG54, $yesno, $yesno);
		$newlist[] = new configSelector ('Allow_Up_Overwrite', _DOWN_CONFIG11, $yesno);
		$newlist[] = new configSelector ('Allow_User_Sub', _DOWN_CONFIG12, $yesno);
		$newlist[] = new configSelector ('Allow_User_Edit', _DOWN_CONFIG13, $yesno);
		$newlist[] = new configSelector ('Allow_User_Delete', _DOWN_CONFIG42, $yesno);
		$newlist[] = new configSelector ('Allow_User_Up', _DOWN_CONFIG14, $yesno);
		$newlist[] = new configSelector ('Allow_Comments', _DOWN_CONFIG15, $yesno);
		$newlist[] = new configSelector ('Allow_Votes', _DOWN_CONFIG25, $yesno);
		$newlist[] = new configSelector ('Activate_AEC', _DOWN_CONFIG66, $yesno);
		$newlist[] = new configSelector ('Send_Sub_Mail', _DOWN_CONFIG16, $yesno);
		$newlist[] = new configSelector ('Enable_Admin_Autoapp', _DOWN_CONFIG26, $yesno);
		// $newlist[] = new configSelector ('Enable_User_Autoapp', _DOWN_CONFIG27, $yesno);
		$newlist[] = new configSelector ('Enable_List_Download', _DOWN_CONFIG28, $yesno);
		$newlist[] = new configSelector ('Audio_Download', _DOWN_CONFIG60, $yesno);
		$newlist[] = new configSelector ('Video_Download', _DOWN_CONFIG61, $yesno);
		$newlist[] = new configSelector ('User_Remote_Files', _DOWN_CONFIG29, $yesno);
		$newlist[] = new configSelector ('See_Containers_no_download', _DOWN_CONFIG33, $yesno);
		$newlist[] = new configSelector ('See_Files_no_download', _DOWN_CONFIG34, $yesno);
		$newlist[] = new configSelector ('Show_RSS_feeds', _DOWN_CONFIG48, $yesno);
		$newlist[] = new configSelector ('Make_Auto_Thumbnail', _DOWN_CONFIG43, $yesno);
		$newlist[] = new configSelector ('Allow_Large_Images', _DOWN_CONFIG38, $yesno);
		$newlist[] = new configSelector ('Remository_Pathway', _DOWN_CONFIG50, $yesnoboth);
		$newlist[] = new configSelector ('Allow_File_Info', _DOWN_CONFIG51, $yesno);
		$newlist[] = new configSelector ('Show_Footer', _DOWN_CONFIG52, $yesno);
		$newlist[] = new configSelector ('Show_File_Folder_Counts', _DOWN_CONFIG53, $yesno);
		$newlist[] = new configSelector ('Show_all_containers', _DOWN_CONFIG70, $yesno);
		
		$customnames = $this->repository->custom_names ? unserialize(base64_decode($this->repository->custom_names)) : array();

		$view = $this->admin->newHTMLClassCheck ('listConfigurationHTML', $this, 0, '');
		if ($view AND $this->admin->checkCallable($view, 'view')) $view->view($newlist, $customnames);
	}
	
	function saveTask () {
		$this->repository->addPostData();
		$customobj = new remositoryCustomizer();
		$fields = $customobj->getFileListFields();
		foreach ($fields as $key=>$farr) {
			$values['A'][$key] = empty($_POST['afield'][$key]) ? 0 : 1;
			$values['B'][$key] = empty($_POST['bfield'][$key]) ? 0 : 1;
			$values['C'][$key] = empty($_POST['cfield'][$key]) ? 0 : 1;
			$values['D'][$key] = empty($_POST['dfield'][$key]) ? 0 : 1;
			$values['S'][$key] = empty($_POST['sequence'][$key]) ? 5 : (int) $_POST['sequence'][$key];
		}
		/*
		foreach ($values['S'] as $key=>$sequence) $reseq[$sequence][] = $key;
		$sequence = 10;
		if (isset($reseq)) foreach ($reseq as $kset) foreach ($kset as $key) {
			$values['S'][$key] = $sequence;
			$sequence += 10;
		}
		*/
		unset ($_POST['afield'], $_POST['bfield'], $_POST['cfield'], $_POST['dfield'], $_POST['sequence']);
		$custom_names = remositoryRepository::getParam($_POST, 'custom_name', array());
		$custom_titles = remositoryRepository::getParam($_POST, 'custom_title', array());
		$custom_values = remositoryRepository::getParam($_POST, 'custom_values', array());
		$custom_uploads = remositoryRepository::getParam($_POST, 'custom_upload', array());
		$custom_lists = remositoryRepository::getParam($_POST, 'custom_list', array());
		$custom_infos = remositoryRepository::getParam($_POST, 'custom_info', array());
		foreach ($custom_names as $sub=>$name) if ($name AND $custom_titles[$sub]) {
			$customfields[$name]['title'] = $custom_titles[$sub];
			$customfields[$name]['values'] = $custom_values[$sub];
			$customfields[$name]['upload'] = $custom_uploads[$sub];
			$customfields[$name]['list'] = $custom_lists[$sub];
			$customfields[$name]['info'] = $custom_infos[$sub];
		}
		$this->repository->custom_names = isset($customfields) ? base64_encode(serialize($customfields)) : '';
		$customobj->saveCustomSpec($values);
		$this->backTask( _DOWN_CONFIG_COMP );
	}

}