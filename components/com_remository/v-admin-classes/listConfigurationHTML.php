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

class listConfigurationHTML extends remositoryAdminHTML {

	private function configTextBox ($title, $name) {
		?>
		<tr>
			<td width="500"><?php echo $title; ?></td>
	        <td> <input class="inputbox" type="text" name="<?php echo $name; ?>" size="50" value="<?php echo $this->repository->$name; ?>" /></td>
	      </tr>
		<?php
	}

	private function configYesNoBox ($listitem) {
		$variablename = $listitem->variablename;

		echo <<<YES_NO
		
		<tr>
			<td width="500">
				$listitem->description
			</td>
			<td>
				{$this->repository->selectList($listitem->optionlist, $listitem->variablename, 'class="inputbox" size="1"', $this->repository->$variablename)}
			</td>
	    </tr>

YES_NO;

	}

	private function configHeading ($title) {
		$interface = remositoryInterface::getInstance();
		$live_site = $interface->getCfg('live_site');
		$headimage = $this->repository->RemositoryImageURL('header.gif',64,64);
		echo <<<HEAD_HTML

		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script type="text/javascript" src="$live_site/includes/js/overlib_mini.js"></script>
		<script type="text/javascript" src="$live_site/includes/js/tabs/tabpane.js"></script>

			   <table class="adminheading">
					<tr>
						<th>
						<div class="title header">
							<span class="sectionname">Remository - $title</span>
        				</div>
						</th>
					</tr>
				</table>

HEAD_HTML;

	}

	public function view( $newlist, $customnames ) {
		$this->configHeading(_DOWN_CONFIG_TITLE);
		$this->commonScripts('preamble');
		$this->commonScripts('Default_Licence');
		$tabs = new remosPane();
		// $this->formStart(_DOWN_CONFIG_TITLE);
		echo <<<START_FORM

		<form action="index2.php" method="post" name="adminForm">
	    <div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="act" value="{$_REQUEST['act']}" />
			<input type="hidden" name="option" value="com_remository" />
			<input type="hidden" name="repnum" value="$this->repnum" />
		</div>

START_FORM;

		$tabs->startPane('pane');
		$tabs->startTab(_DOWN_CONFIG_TITLE1, 'page1')
		?>
	    <table cellpadding="2" cellspacing="4" border="0" width="100%" class="adminform">
	    <?php
	    //$this->configTextBox(_DOWN_CONFIG58, 'name');
	    //$this->configTextBox(_DOWN_CONFIG59, 'alias');
	    $this->configTextBox(_DOWN_CONFIG65, 'Main_Page_Title');
	    $this->configTextBox(_DOWN_CONFIG1, 'tabclass');
		$this->configTextBox(_DOWN_CONFIG2, 'tabheader');
		$this->configTextBox(_DOWN_CONFIG19, 'headerpic');
		$this->configTextBox(_DOWN_CONFIG4, 'Down_Path');
		$this->configTextBox(_DOWN_CONFIG5, 'Up_Path');
		$this->configTextBox(_DOWN_CONFIG67, 'Profile_URI');
		$this->configTextBox(_DOWN_CONFIG49, 'Classification_Types');
		$this->configTextBox(_DOWN_CONFIG21, 'Large_Text_Len');
		$this->configTextBox(_DOWN_CONFIG22, 'Small_Text_Len');
		$this->configTextBox(_DOWN_CONFIG23, 'Small_Image_Width');
		$this->configTextBox(_DOWN_CONFIG24, 'Small_Image_Height');
		$this->configTextBox(_DOWN_CONFIG36, 'Large_Image_Width');
		$this->configTextBox(_DOWN_CONFIG37, 'Large_Image_Height');
		$this->configTextBox(_DOWN_CONFIG6, 'MaxSize');
		$this->configTextBox(_DOWN_CONFIG7, 'Max_Up_Per_Day');
		$this->configTextBox(_DOWN_CONFIG40, 'Max_Down_Per_Day');
		$this->configTextBox(_DOWN_CONFIG44, 'Max_Down_Reg_Day');
		$this->configTextBox(_DOWN_CONFIG41, 'Max_Down_File_Day');
		$this->configTextBox(_DOWN_CONFIG8, 'Max_Up_Dir_Space');
		$this->configTextBox(_DOWN_CONFIG55, 'Featured_Number');
		$this->configTextBox(_DOWN_CONFIG30, 'Favourites_Max');
		$this->configTextBox(_DOWN_CONFIG35, 'Max_Thumbnails');
		$this->configTextBox(_DOWN_CONFIG63, 'Main_Authors');
		$this->configTextBox(_DOWN_CONFIG64, 'Author_Threshold');
		$this->configTextBox(_DOWN_CONFIG31, 'Date_Format');
		$this->configTextBox(_DOWN_CONFIG69, 'Set_date_locale');
		$this->configTextBox(_DOWN_CONFIG32, 'Default_Version');
		$this->configTextBox(_DOWN_CONFIG68, 'Force_Language');
		?>
	    </table>
		<?php
		$tabs->endTab();
		$tabs->startTab(_DOWN_CONFIG_TITLE2, 'page2');
		?>
	    <table cellpadding="2" cellspacing="0" border="0" width="100%" class="adminform">
	    <?php
	    $this->configTextBox(_DOWN_CONFIG9, 'ExtsOk');
	    $this->configTextBox(_DOWN_CONFIG46, 'ExtsDisplay');
	    $this->configTextBox(_DOWN_CONFIG56, 'ExtsAudio');
	    $this->configTextBox(_DOWN_CONFIG57, 'ExtsVideo');
	    $this->configTextBox(_DOWN_CONFIG47, 'Scribd');
		foreach ($newlist as $listitem) {
			$this->configYesNoBox($listitem);
		}
		$this->configTextBox(_DOWN_CONFIG17, 'Sub_Mail_Alt_Addr');
		$this->configTextBox(_DOWN_CONFIG18, 'Sub_Mail_Alt_Name');
		?>
	    </table>
		<?php
		$tabs->endTab();
		$tabs->startTab(_DOWN_CONFIG_TITLE3, 'page3');
		?>
	    <table cellpadding="2" cellspacing="0" border="0" width="100%" class="adminform">
	    <?php
		$this->fileInputArea(_DOWN_DOWNLOAD_TEXT_BOX, '', 'download_text', $this->repository->download_text, 50, 100);
		?>
	    </table>
		<?php
		$tabs->endTab();
		$tabs->startTab(_DOWN_CONFIG_TITLE_PREAMBLE, 'page4');
		?>
	    <table cellpadding="2" cellspacing="0" border="0" width="100%" class="adminform">
	    <?php
		$this->fileInputArea(_DOWN_MAIN_PREAMBLE, '', 'preamble', $this->repository->preamble, 50, 100, true);
		?>
	    </table>
		<?php
		$tabs->endTab();
		$tabs->startTab(_DOWN_CONFIG_TITLE_LICENCE, 'page5');
		?>
	    <table cellpadding="2" cellspacing="0" border="0" width="100%" class="adminform">
	    <?php
		$this->fileInputArea(_DOWN_CONFIG45, '', 'Default_Licence', $this->repository->Default_Licence, 50, 100, true);
		?>
	    </table>
		<?php
		$tabs->endTab();
		$tabs->startTab(_DOWN_CUSTOM_FIELDS_TAB, 'page6');
		$cfhtml = '';
		$i = 0;
		foreach ($customnames as $name=>$data) {
			$cfhtml .= $this->requestCustomField($name, $data['title'], $data['values'], $data['upload'], $data['list'], $data['info']);
			$i++;
		}
		for ($i=$i; $i<20; $i++) {
			$cfhtml .= $this->requestCustomField();
		}
		$name_head = _DOWN_FIELD_NAME;
		$title_head = _DOWN_FIELD_TITLE;
		$option_head = _DOWN_FIELD_OPTIONS;
		$upload_head = _DOWN_CUSTOM_UPLOAD;
		$list_head = _DOWN_CUSTOM_LIST;
		$info_head = _DOWN_CUSTOM_INFO_PAGE;
		$not_implemented = _DOWN_NOT_YET_IMPLEMENTED;
		
		echo <<<CUSTOM_FIELDS
		
		<p>
			$not_implemented
		</p>
			
		<table id="remositorycustomfields">
			<thead>
				<tr>
					<th>
						$name_head
					</th>
					<th>
						$title_head
					</th>
					<th>
						$option_head
					</th>
					<th>
						$upload_head
					</th>
					<th>
						$list_head
					</th>
					<th>
						$info_head
					</th>
				</tr>
			</thead>
			<tbody>
				$cfhtml
			</tbody>
		</table>
		
CUSTOM_FIELDS;

		$tabs->endTab();
		$tabs->startTab(_DOWN_CONFIG_TITLE4, 'page7');
		$customobj = new remositoryCustomizer();
		$fields = $customobj->getFileListFields();
		$values = $customobj->getCustomSpec();
		$name_field = _DOWN_FIELD;
		$name_sequence = _DOWN_SEQUENCE;
		echo <<<START_CUSTOMIZE

	    <table cellpadding="2" cellspacing="0" border="0" width="100%" class="adminform">
	    	<tr>
	    		<th>$name_field</th>
	    		<th>$name_sequence</th>
	    		<th>A</th>
	    		<th>B</th>
	    		<th>C</th>
	    		<th>D</th>
	    	</tr>

START_CUSTOMIZE;

		foreach ($fields as $key=>$farr) {
			$legend = $farr[1];
			$achecked = empty($values['A'][$key]) ? '' : 'checked="checked"';
			$bchecked = empty($values['B'][$key]) ? '' : 'checked="checked"';
			$cchecked = empty($values['C'][$key]) ? '' : 'checked="checked"';
			$dchecked = empty($values['D'][$key]) ? '' : 'checked="checked"';
			$sequence = empty($values['S'][$key]) ? $key+1 : $values['S'][$key];
			
			echo <<<FIELD_SELECT
			
			<tr>
				<td>$legend</td>
				<td><input type="text" name="sequence[$key]" value="$sequence" size="5" /></td>
				<td><input type="checkbox" name="afield[$key]" value="1" $achecked /></td>
				<td><input type="checkbox" name="bfield[$key]" value="1" $bchecked /></td>
				<td><input type="checkbox" name="cfield[$key]" value="1" $cchecked /></td>
				<td><input type="checkbox" name="dfield[$key]" value="1" $dchecked /></td>
			</tr>
			
FIELD_SELECT;

		}
		
		$explain1 = _DOWN_CONFIG_EXPLAIN_1;
		$explain2 = _DOWN_CONFIG_EXPLAIN_2;
		$explain3 = _DOWN_CONFIG_EXPLAIN_3;
		$explain4 = _DOWN_CONFIG_EXPLAIN_4;
		
		echo <<<FINISH_CUSTOMIZE
		
			<tr>
				<td></td><td></td>
				<td colspan="3" class="remositoryexplain">$explain1</td>
			</tr>
			<tr>
				<td></td><td></td>
				<td colspan="3" class="remositoryexplain">$explain2</td>
			</tr>
			<tr>
				<td></td><td></td>
				<td colspan="3" class="remositoryexplain">$explain3</td>
			</tr>
			<tr>
				<td></td><td></td>
				<td colspan="3" class="remositoryexplain">$explain4</td>
			</tr>
	    </table>
		
FINISH_CUSTOMIZE;
		
		$tabs->endTab();
		$tabs->endPane();
		?>
		</form>
		<?php		
	}
	
	private function requestCustomField ($name='', $title='', $values='', $upload=0, $list=0, $info=0) {
		$yes = _YES;
		$no = _NO;
		return <<<CUSTOM_FIELD

				<tr>
					<td>
						<input type="text" class="inputbox" name="custom_name[]" value="$name" size="25" />
					</td>
					<td>
						<input type="text" class="inputbox" name="custom_title[]" value="$title" size="40" />
					</td>
					<td>
						<input type="text" class="inputbox" name="custom_values[]" value="$values" size="60" />
					</td>
					<td>
						<select class="inputbox" name="custom_upload[]">
							<option value="1" {$this->isSelected($upload)}>$yes</option>
							<option value="0" {$this->isSelected(!$upload)}>$no</option>
						</select>
					</td>
					<td>
						<select class="inputbox" name="custom_list[]">
							<option value="1" {$this->isSelected($list)}>$yes</option>
							<option value="0" {$this->isSelected(!$list)}>$no</option>
						</select>
					</td>
					<td>
						<select class="inputbox" name="custom_info[]">
							<option value="1" {$this->isSelected($info)}>$yes</option>
							<option value="0" {$this->isSelected(!$info)}>$no</option>
						</select>
					</td>
				</tr>
			
CUSTOM_FIELD;
					
	}
	
	private function isSelected ($value) {
		if ($value) return 'selected="selected"';
	}
	
}