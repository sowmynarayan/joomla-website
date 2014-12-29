<?php
/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2009 JoomlaShine.com. All rights reserved.
* @license   Copyrighted Commercial Software
* This file may not be redistributed in whole or significant part.
*/

	defined( '_JEXEC' ) or die( 'Restricted index access' );

	class JSNUtils {

		function JSNUtils() {}

		function &getInstance() {
			static $instance;

			if ($instance == null) {
				$instance = new JSNUtils();
			}

			return $instance;
		}

		function countPositions($t, $positions) {
			$positionCount = 0;
			for($i=0;$i < count($positions); $i++){
				if ($t->countModules($positions[$i])) $positionCount++;
			}
			return $positionCount;
		}

		function addToQueryString($qs) {
			$cqs = $_SERVER['QUERY_STRING'];
			if (empty($cqs)) {
				return '?'.$qs;
			} else {
				return '?'.$cqs."&amp;".$qs;
			}
		}

		function getCurrentUrl() {
			$pageURL = 'http';
			if (!empty($_SERVER['HTTPS'])) {
				$pageURL .= "s";
			}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return JFilterOutput::ampReplace($pageURL);
		}

		function getTemplateAttributes($attrs_array, $template_prefix, $pageclass) {
			$template_attrs = null;

			foreach ($attrs_array as $attr_name => $attr_values) {
				$t_attr = null;

				// Get color settings from page class suffix
				if(!empty($pageclass)){
					$pc = 'custom-'.$attr_name.'-';
					$pc_len = strlen($pc);
					$pclasses = explode(" ", $pageclass);
					foreach($pclasses as $pclass){
						if(substr($pclass, 0, $pc_len) == $pc) {
							$t_attr = substr($pclass, $pc_len, strlen($pclass)-$pc_len);
						}
					}
				}

				// Apply color settings from cookies
				if (isset($_COOKIE[$template_prefix.$attr_name])) {
					$t_attr = $_COOKIE[$template_prefix.$attr_name];
				}

				// Apply color settings from permanent request parameters
				if (isset($_GET['set_'.$attr_name])) {
					setcookie($template_prefix.$attr_name, $_GET['set_'.$attr_name], time() + 3600, '/');
					$t_attr = $_GET['set_'.$attr_name];
				}
				
				$template_attrs[$attr_name] = (in_array($t_attr, $attr_values))?$t_attr:null;
			}

			return $template_attrs;
		}
		
		function getTemplateDetails($templateBaseDir, $templateDir)
		{
			// Check of the xml file exists
			if (!is_file($templateBaseDir.DS.'templateDetails.xml')) {
				return false;
			}

			$xml = $this->parseXMLInstallFile($templateBaseDir.DS.'templateDetails.xml');

			if ($xml['type'] != 'template') {
				return false;
			}

			$data = new StdClass();
			$data->directory = $templateDir;

			foreach($xml as $key => $value) {
				$data->$key = $value;
			}

			$data->checked_out = 0;
			$data->mosname = JString::strtolower(str_replace(' ', '_', $data->name));

			return $data;
		}
		function wrapFirstWord( $value )
		{
		 	$processed_string =  null;
		 	$explode_string = explode(' ', trim( $value ) );
		 	for ( $i=0; $i < count( $explode_string ); $i++ )
		 	{
		 		if( $i == 0 )
		 		{
		 			$processed_string .= '<span>'.$explode_string[$i].'</span>';
		 		}
		 		else
		 		{
		 			$processed_string .= ' '.$explode_string[$i];
		 		}
		 	}
		 	
		 	return $processed_string;
		 }
		function parseXMLInstallFile($path)
		{
			$xml = & JFactory::getXMLParser('Simple');
			
			if (!$xml->loadFile($path)) {
				unset($xml);
				return false;
			}
			if ( !is_object($xml->document) || ($xml->document->name() != 'install' && $xml->document->name() != 'mosinstall')) {
				unset($xml);
				return false;
			}
			
			$data = array();
			$data['legacy'] = $xml->document->name() == 'mosinstall';
			
			$element = & $xml->document->name[0];
			$data['name'] = $element ? $element->data() : '';
			$data['type'] = $element ? $xml->document->attributes("type") : '';
			
			$element = & $xml->document->creationDate[0];
			$data['creationdate'] = $element ? $element->data() : JText::_('Unknown');
			
			$element = & $xml->document->author[0];
			$data['author'] = $element ? $element->data() : JText::_('Unknown');
			
			$element = & $xml->document->copyright[0];
			$data['copyright'] = $element ? $element->data() : '';
			
			$element = & $xml->document->authorEmail[0];
			$data['authorEmail'] = $element ? $element->data() : '';
			
			$element = & $xml->document->authorUrl[0];
			$data['authorUrl'] = $element ? $element->data() : '';
			
			$element = & $xml->document->version[0];
			$data['version'] = $element ? $element->data() : '';
			
			$element = & $xml->document->license[0];
			$data['license'] = $element ? $element->data() : '';
			
			$element = & $xml->document->description[0];
			$data['description'] = $element ? $element->data() : '';
			
			$element = & $xml->document->group[0];
			$data['group'] = $element ? $element->data() : '';
			
			return $data;
		}		 
	}

?>