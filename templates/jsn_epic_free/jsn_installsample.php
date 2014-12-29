<?php
/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2009 JoomlaShine.com. All rights reserved.
* @license   Copyrighted Commercial Software
* This file may not be redistributed in whole or significant part.
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
require_once('php'.DS.'lib'.DS.'jsn_backup.php');
class JSNHTTPRequest 
{ 
    var $_fp;        // HTTP socket 
    var $_url;        // full URL 
    var $_host;        // HTTP host 
    var $_protocol;    // protocol (HTTP/HTTPS) 
    var $_uri;        // request URI 
    var $_port;        // port 
    
    // scan url 
    function _scan_url() 
    { 
        $req = $this->_url; 
        
        $pos = strpos($req, '://'); 
        $this->_protocol = strtolower(substr($req, 0, $pos)); 
        
        $req = substr($req, $pos+3); 
        $pos = strpos($req, '/'); 
        if($pos === false) 
            $pos = strlen($req); 
        $host = substr($req, 0, $pos); 
        
        if(strpos($host, ':') !== false) 
        { 
            list($this->_host, $this->_port) = explode(':', $host); 
        } 
        else 
        { 
            $this->_host = $host; 
            $this->_port = ($this->_protocol == 'https') ? 443 : 80; 
        } 
        
        $this->_uri = substr($req, $pos); 
        if($this->_uri == '') 
            $this->_uri = '/'; 
    } 
    
    // constructor 
    function JSNHTTPRequest($url) 
    { 
        $this->_url = $url; 
        $this->_scan_url(); 
    } 
    
    // download URL to string 
    function DownloadToString() 
    { 
        $crlf = "\r\n"; 
        
        // generate request 
        $req = 'GET ' . $this->_uri . ' HTTP/1.0' . $crlf 
            .    'Host: ' . $this->_host . $crlf 
            .    $crlf; 
        
        // fetch 
        $this->_fp = fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port); 
        fwrite($this->_fp, $req); 
        while(is_resource($this->_fp) && $this->_fp && !feof($this->_fp)) 
            @$response .= fread($this->_fp, 1024); 
        fclose($this->_fp); 
        
        // split header and body 
        $pos = strpos($response, $crlf . $crlf); 
        if($pos === false) 
            return($response); 
        $header = substr($response, 0, $pos); 
        $body = substr($response, $pos + 2 * strlen($crlf)); 
        
        // parse headers 
        $headers = array(); 
        $lines = explode($crlf, $header); 
        foreach($lines as $line) 
            if(($pos = strpos($line, ':')) !== false) 
                $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1)); 
        
        // redirection? 
        if(isset($headers['location'])) 
        { 
            $http = new JSNHTTPRequest($headers['location']); 
            return($http->DownloadToString($http)); 
        } 
        else 
        { 
            return($body); 
        } 
    } 
}
class JSNReadXmlDetails
{
	var $_fileName='templateDetails.xml';
	function parserXMLDetails()
	{
		$array_result 		= array();
		$file_details 		= dirname(__FILE__).DS.$this->_fileName;
		$parser_details 	= JFactory::getXMLParser('Simple');
		$result_load_file_details = $parser_details->loadFile($file_details);
		if($result_load_file_details == false)
		{
			return $array_result;
		}
		$document_details 		=& $parser_details->document;
		$nod_name 	 			=& $document_details->getElementByPath('name');
		if($nod_name != false)
		{
			$arr_name = explode('_', $nod_name->data());
			$array_result['name'] 				= strtolower(@$arr_name[0].'_'.@$arr_name[1]);
			$array_result['name_uppercase'] 	= @$arr_name[0].' '.@$arr_name[1];
			$array_result['full_name'] 			= @$arr_name[0].'_'.@$arr_name[1].'_'.@$arr_name[2];
		}
		return $array_result;
	}
}
global $error;
$obj_read_xml_detail 	= new JSNReadXmlDetails();
$name 					= $obj_read_xml_detail->parserXMLDetails();
define("FILE_URL",'http://demo.joomlashine.com/joomla-templates/sample-data/'.$name['name'].'.zip');
define("FILE_ZIP", $name['name'].'.zip');
define("FILE_SQL", $name['name'].'_sample_data.sql');
define("FOLDER_IMAGE", $name['name']);
define("PREFIX_FOLDER_NAME", $name['name'].'_sample_data_');
define('JAVASCRIPT_MOOTOOL', '<script type="text/javascript" src="'.JURI::base(true).'/media/system/js/mootools.js"></script>');
////////////////////////////////////////////////////////////////////////////////////////PROTOTYPE//////////////////////////////////////////////////////////////////
function moveFolderImageSample($path)
{	
	$path 			= JPath::clean($path.DS.FOLDER_IMAGE);
	$dest_path 		= JPath::clean(JPATH_ROOT.DS.'images'.DS.FOLDER_IMAGE);
	$result_move 	= JFolder::copy($path, $dest_path, '', true);
	if(!$result_move)
	{
		return false;
	}
	return true;
}

function getPackageFromUrl()
{
	$url	= FILE_URL;
	$p_file = downloadPackage($url);
	if (!$p_file)
	{
		return false;
	}
	return $p_file;
}

function unpackPackage($p_file)
{
	$tmp_dest 		= JPATH_ROOT.DS.'tmp';
	$tmpdir			= uniqid(PREFIX_FOLDER_NAME);
	$archive_name 	= $p_file;
	$extract_dir 	= JPath::clean($tmp_dest.DS.dirname($p_file).DS.$tmpdir);
	$archive_name 	= JPath::clean($tmp_dest.DS.$archive_name);
	$result 		= JArchive::extract( $archive_name, $extract_dir);
	if($result)
	{
		$path = $tmp_dest.DS.$tmpdir;
		return $path;
	}
	return false;
}
function getFilenameFromURL($url)
{
	if (is_string($url))
	{
		$parts = explode('/', $url);
		return $parts[count($parts) - 1];
	}
	return false;
}	
function downloadPackage($url)
{	
	ini_set('user_agent', "JSN Template Installer Sample Data");
		
	$path_temp 		= JPATH_ROOT.DS.'tmp';	
	
	$target 	= $path_temp.DS.getFilenameFromURL($url);
	$contents 	= null;	
	if(ini_get('allow_url_fopen') == 1){
		$input_handle 	= @ fopen($url, "r");
		if (!$input_handle)
		{
			$error 	= 'Failed to open stream: HTTP request failed';	
			returnError('false', $error);
			return false;
		}
		while (!feof($input_handle))
		{
			$contents .= fread($input_handle, 4096);
			if ($contents == false)
			{			
				$error 	= 'Failed reading network resource: File Not Found';
				returnError('false', $error);
				return false;
			}
		}
		
		JFile::write($target, $contents);
		fclose($input_handle);
		
	}else{
		$r = new JSNHTTPRequest($url);	
		$contents =  $r->DownloadToString();
		JFile::write($target, $contents); 	
	}
	return basename($target);	
}
function splitSql($sql)
{
	$db =& JFactory::getDBO();
	return $db->splitSql($sql);
}
function executeInstallSampleData($path)
{
	$db			=& JFactory::getDBO();
	$path 		= JPath::clean($path.DS.FILE_SQL);	
	$buffer 	= JFile::read($path);
	$queries 	= splitSql($buffer);	
	if (count($queries) != 0)
	{
		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '' && $query{0} != '#')
			{
				$db->setQuery($query);
				if (!$db->query())
				{
					return false;
				}
			}
		}
		return true;
	}
	return false;	
}
function deleteTempFolderISD($path)
{
	$path = JPath::clean($path);
	if (JFolder::exists($path))
	{	
		JFolder::delete($path);
		return true;
	}
	return false;	
}
function deleteISDFile()
{
	$path = JPATH_ROOT.DS.'tmp'.DS.FILE_ZIP;
	if(JFile::exists($path))
	{
		JFile::delete($path);
		return true;
	}
	return false;
}
function returnError($result, $msg)
{
	global $error;
	$error = $msg;
}
function getModuleIsNotBasic(){
	$db					=& JFactory::getDBO();
	$str_query 			= '';
	$str_field 			= '';
	$arrayValue			= array();
	$field_module 		= array();
	$queries			= array();
	
	$table_info_module 	= $db->getTableFields('#__modules', false);
	foreach ($table_info_module['#__modules'] as $key =>$value){			
		$field_module [] = $value->Field;		
	}
	$str_field 			= implode(',', $field_module);	
	$query 				= "SELECT " . implode(',', $field_module) . " FROM #__modules WHERE `module` NOT IN ('mod_mainmenu',  'mod_login', 'mod_popular', 'mod_latest', 'mod_stats', 'mod_unread', 'mod_online', 'mod_toolbar', 'mod_quickicon', 'mod_logged', 'mod_footer','mod_menu', 'mod_submenu', 'mod_status', 'mod_title', 'mod_poll', 'mod_mainmenu', 'mod_latestnews', 'mod_whosonline', 'mod_mostread', 'mod_archive', 'mod_sections', 'mod_newsflash', 'mod_related_items', 'mod_search', 'mod_random_image', 'mod_banners', 'mod_wrapper', 'mod_feed', 'mod_breadcrumbs', 'mod_syndicate', 'mod_custom') AND `client_id` = 0"; 	
	$db->setQuery($query);	
	$rows_module_query = $db->loadAssocList();
	
	foreach ($rows_module_query as $value)
	{
		reset($field_module);
		foreach ($field_module as $field_value)
		{
			if($value[$field_value] == '')
			{
				$str_query .= '"", ';
			}
			else
			{
				if($field_value == 'id')
				{
					$str_query .= '"null", ';	
				}
				elseif ($field_value == 'published'){
					$str_query .= '"0", ';
				}
				else
				{
					$str_query .= '"'.$value[$field_value].'", ';	
				}
			}						
		}		
		$str_query = substr($str_query, 0 , -2);		
		$queries [] = 'INSERT INTO #__modules ('.$str_field.') VALUES ('.$str_query.' )';
		$str_query = '';
	}
	return $queries;
}
function getModuleAdminIsNotBasic(){
	$db					=& JFactory::getDBO();
	$str_query 			= '';
	$str_field 			= '';
	$arrayValue			= array();
	$field_module 		= array();
	$queries			= array();
	
	$table_info_module 	= $db->getTableFields('#__modules', false);
	foreach ($table_info_module['#__modules'] as $key =>$value){			
		$field_module [] = $value->Field;		
	}
	$str_field 			= implode(',', $field_module);	
	$query 				= "SELECT " . implode(',', $field_module) . " FROM #__modules WHERE `id` NOT IN (2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15) AND `client_id` = 1"; 	
	$db->setQuery($query);	
	$rows_module_query = $db->loadAssocList();
	
	foreach ($rows_module_query as $value)
	{
		reset($field_module);
		foreach ($field_module as $field_value)
		{
			if($value[$field_value] == '')
			{
				$str_query .= '"", ';
			}
			else
			{
				if($field_value == 'id')
				{
					$str_query .= '"null", ';	
				}
				else
				{
					$str_query .= '"'.$value[$field_value].'", ';	
				}
			}						
		}		
		$str_query = substr($str_query, 0 , -2);		
		$queries [] = 'INSERT INTO #__modules ('.$str_field.') VALUES ('.$str_query.' )';
		$str_query = '';
	}
	return $queries;
}
function deleteModuleAdminIsNotBasic()
{
	$db	=& JFactory::getDBO();
	$query = 'DELETE FROM #__modules WHERE `id` NOT IN (2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15) AND `client_id` = 1';
	$db->setQuery($query);
	$db->query();
}
function executeQueryModuleIsNotBasic($queries){
	$db	=& JFactory::getDBO();
	if(count($queries))
	{
		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '')
			{				
				$db->setQuery($query);
				$db->query();								
			}			
		}	
	}
	return true;
}
function authenticationUsername(){
	
	jimport('joomla.user.helper');
	$error 	= 'Username and password do not match';	
	$username = JRequest::getVar('username', '', 'post', 'username');
	$password = JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
	
	$db 	=& JFactory::getDBO();
	$query 	= 'SELECT id, password, gid FROM #__users WHERE gid = 25 AND username = ' . $db->Quote( $username );
	
	$db->setQuery( $query );
	$result = $db->loadObject();	
	if($result)
	{
		$parts	= explode( ':', $result->password );
		$crypt	= $parts[0];
		$salt	= @$parts[1];
		$testcrypt = JUserHelper::getCryptedPassword($password, $salt);
		if ($crypt == $testcrypt)
		{						
			return true;
		}
		else
		{
			returnError('false', $error);
			return false;
		}
	}
	else
	{
		returnError('false', $error);
		return false;
	}
}
function setDefaultTemplate($tem_name){
	$db 	=& JFactory::getDBO();
	$query 	= 'DELETE FROM `#__templates_menu` WHERE `template` != "'.$tem_name.'" AND `client_id` = 0';
	$db->setQuery( $query );
	$db->query();
	$query 	= 'REPLACE INTO `#__templates_menu` (`template`, `menuid`, `client_id`) VALUES ("'.$tem_name.'", 0, 0)';
	$db->setQuery( $query );
	$db->query();
	return true;	
}

function checkFolderPermission()
{
	$folderpath = JPATH_ROOT.DS.'tmp';
	if(is_writable($folderpath) == false)
	{
		$error 	= 'Folder "/tmp" is currently UNWRITABLE (!). Please make it WRITABLE (chmod 777) and try again';
		returnError('false', $error);
		return false;		
	}
	return true;
}
function getDomain(){
	$pathURL = array();
	$uri	=& JURI::getInstance();
	$pathURL['prefix'] = $uri->toString( array('host'));
	return $pathURL['prefix'];
}

/////////////////////////////////////////////////////////////////////////////////////MAIN//////////////////////////////////////////////////////////////////////////////////////////////////
$task 								= JRequest::getWord('task', '', 'POST');
$back_up 							= JRequest::getInt('back_up', 0, 'POST');
$array_get_module_not_basic 		= getModuleIsNotBasic();
$array_get_admin_module_not_basic 	= getModuleAdminIsNotBasic();
$file_backup_name					= null;
$check_install_success 				= false;
$check_file_download 				= false;
$backup_obj 						=& JSNBackup::getInstance();
$domain 							=  getDomain();
$tem_name = strtolower($name['full_name']);
if($task == 'install'){
	JRequest::checkToken() or jexit( 'Invalid Token' );
	$auth = authenticationUsername();
	$perm = checkFolderPermission();
	if($auth == true){
		if($perm == true)
		{
			$result_get_package_from_url 	= getPackageFromUrl();
			$result_unpack_package 			= unpackPackage($result_get_package_from_url);		
			if($result_unpack_package != false){
				if($back_up == 1)
				{					
					$file_backup_name 		= $backup_obj->executeBackup($domain);
					$check_file_download 	= true;					
				}
				deleteModuleAdminIsNotBasic();
				executeInstallSampleData($result_unpack_package);
				executeQueryModuleIsNotBasic($array_get_module_not_basic);
				executeQueryModuleIsNotBasic($array_get_admin_module_not_basic);
				deleteTempFolderISD($result_unpack_package);
				setDefaultTemplate($tem_name);
				deleteISDFile();				
				//returnError('true', 'Sample data is successfully installed.');
				$check_install_success = true;				
			}
		}
	}
}

if($task == 'download')
{
	JRequest::checkToken() or jexit( 'Invalid Token' );
	$file_name 		= JRequest::getString('file_name', '', 'POST');
	$folder_temp	= JPATH_ROOT.DS.'tmp';
	$file_path 		= $folder_temp.DS.$file_name;
	if(JFile::exists($file_path)){
		$backup_obj->downloadFile('zip', $file_name);
		jexit();
	}else{
		returnError('true', 'File not exists');
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<?php echo JAVASCRIPT_MOOTOOL."\n"; ?>
		<title>Install Sample Data</title>
		<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/css/jsn_admin.css" type="text/css" />
		<script type="text/javascript">				
			function submitInstallSampleData()
			{
				var user_name = $('username').value;
				var password = $('password').value;
				
				if(user_name == ''){
					alert('Required field username cannot be left blank');
					return false;
				}else if(password == ''){
					alert('Required field password cannot be left blank');
					return false;
				}else{
					return true;
				}
			}
			function enableButton(){				
				if($('cb_rules_agree').checked == true){
					$('jsn-install-button').disabled = false;
				}else{
					$('jsn-install-button').disabled = true;
				}
			}
					
		</script>
	</head>
	<body id="jsn-sampledata">
		<div id="jsn-page"><div><div>
		<?php if($check_install_success == false) { ?>
		<h1><?php echo $name['name_uppercase']; ?> Sample Data Installation</h1>
		<p>By installing sample data, you will get your website looks like on <a href="http://demo.joomlashine.com/joomla-templates/<?php echo $name['name']; ?>/index.php" target="_blank"><?php echo $name['name_uppercase']; ?> live demo</a> website. Please read following information carefully before you start.</p>
		<h2 class="heading-alert">Warning!</h2>
		<ul>
			<li class="text-alert">Installing sample data will <strong>delete all following data on your current website</strong>: <em>sections</em>, <em>categories</em>, <em>articles</em>, <em>menus</em> and <em>extension's data</em>.</li>
		</ul>
		<h2 class="heading-info">Notes</h2>
		<ul>
			<li>Best if you install sample data on a <strong>fresh testing Joomla! website</strong>. It is NOT recommended to install on production website.</li>
			<li>Sample data package will be downloaded from our server and might be not available sometimes. If this happens, please run this installer again in later time.</li>
		</ul>
		<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_installsample" id="installSampleDataFrom" autocomplete="off">
		<table border="0">
			<tr>
				<td colspan="2">
					<h2>Input your Super Administrator account</h2>
					<p>To start the process, please input the <strong>Super Administrator</strong> account that you are using to access to your Joomla! website administration.</p>
				</td>
			</tr>
			<?php if($error !='' && !is_null($error)) { ?>
			<tr>
				<td colspan="2">
					<div id="jsn-sampledata-error-message">
						<p style="text-align:center;">
							<?php echo $error; ?>
						</p>
					</div>
				</td>
			</tr>
			<?php } ?>			
			<tr>
				<td width="20%">Username:</td>
				<td><input name="username" id="username" type="text" value="" /></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input name="password" id="password" type="password" value="" /></td>
			</tr>
		</table>
		<table border="0">
			<tr>
				<td width="22" valign="top">
					<input type="checkbox" value="1" id="cb_rules_agree" name="agree" onClick="enableButton();">
				</td>
				<td>
					<label for="cb_rules_agree" class="input-label">
						<strong>I agree that installing sample data will delete all content on my current website</strong>
					</label>
				</td>
			</tr>
			<tr>
				<td width="22" valign="top"><input type="checkbox" value="1" id="back_up" name="back_up" checked="checked"></td>
				<td><label for="back_up">Create for me backup of all data you are going to delete</label></td>
			</tr>
			<tr>
				<td colspan="2" align="center" valign="top">
					<button type="submit" onclick="return submitInstallSampleData();" id="jsn-install-button" disabled="disabled">Install Sample Data</button>
					<span class="install-cancel"><a id="jsn-install-cancel" href="administrator/index.php?option=com_templates&amp;task=edit&cid[]=<?php echo $this->template; ?>&amp;client=0">Cancel</a></span>
				</td>
			</tr>
		</table>
		<input type="hidden" name="task" value="install" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		<?php }else{ ?>
		<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_installsample" id="frm_download" name="frm_download" autocomplete="off">
			<h1><?php echo $name['name_uppercase']; ?> Sample Data <span id="jsn-sampledata-success-message">Successfully Installed</span></h1>
			<ul style="margin-top: 2em;">
				<?php if($check_file_download == true) { ?>
				<li>
					<a href="javascript:document.frm_download.submit();">Download backup file</a> for later restoration if necesary. <a href="http://www.joomlashine.com/docs/joomla-templates/how-to-restore-after-sample-data-installation.html" target="_blank">Read more...</a>
					<p><strong>Note:</strong><br />The backup file is located in folder <strong>&quot;tmp&quot;</strong> inside your Joomla folder. You can get the backup file anytime or delete it if you want.</p>
				</li>
				<?php } ?>
				<li><a href="administrator/index.php?option=com_templates&amp;task=edit&amp;cid[]=<?php echo strtolower($name['full_name']); ?>&amp;client=0">Return to template settings page</a> for further configuration.</li>
				<li><a href="index.php" target="_blank">Go to website front-page</a> to ensure it looks like template demo website.</li>
			</ul>
			<input type="hidden" name="task" value="download" />
			<input type="hidden" name="file_name" value="<?php echo $file_backup_name; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</form>	
		<?php } ?>
		</div></div></div>
	</body>
</html>
