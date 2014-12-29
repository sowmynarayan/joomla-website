<?php
/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2009 JoomlaShine.com. All rights reserved.
* @license   Copyrighted Commercial Software
* This file may not be redistributed in whole or significant part.
*/

defined( '_JEXEC' ) or die( 'Restricted index access' );

class JElementJSNParams extends JElement
{
	
	var $_name = null;
	var $_value = null;
	var $_template_path_of_base;
	var $_template_name;
	var $_node;
	var $_control_name;
	var $_url = null;
	var $_modal_window = null;
	
	function JElementJSNParams()
	{
		$this->_setTempPathOfBase();
		$this->_setTempName();
		require_once( $this->_template_path_of_base.DS.'php'.DS.'lib'.DS.'jsn_utils.php' );
		//$this->_initAccordion();	
	}
	
	function fetchElement( $name, $value, &$node, $control_name )
	{
		$this->_name 			= $name;
		$this->_value 			= $value;
		$this->_node 			= $node;
		$this->_control_name 	= $control_name;
		$this->_url 			= $node->attributes( 'url' );
		$this->_modal_window 	= $node->attributes( 'modalwindow' );
		$subtype 				= $node->attributes( 'subtype' );
		
		return $this->$subtype();
	}
	function _initAccordion()
	{
		$document 		=& JFactory::getDocument();
		$jsAccordion 	= "window.addEvent('domready', function(){ new Accordion($$('.panel h3.jpane-toggler'), $$('.panel div.jpane-slider'), {onActive: function(toggler, i) { toggler.addClass('jpane-toggler-down'); toggler.removeClass('jpane-toggler'); },onBackground: function(toggler, i) { toggler.addClass('jpane-toggler'); toggler.removeClass('jpane-toggler-down'); },duration: 300,opacity: false,alwaysHide: true}); });";
		$document->addScriptDeclaration($jsAccordion);
	}
	function _setTempPathOfBase()
	{
		$template_name 					= explode( DS, str_replace( array( '\elements', '/elements' ), '', dirname(__FILE__) ) );
		$template_name 					= $template_name [ count( $template_name ) - 1 ];
		$path_base 						= str_replace( DS."templates".DS.$template_name.DS.'elements', "", dirname(__FILE__) );
		$this->_template_path_of_base 	= $path_base . DS . 'templates' .  DS . $template_name;	
	}
	
	function _setTempName()
	{
		$template_name 			= explode( DS, str_replace( array( '\elements', '/elements' ), '', dirname(__FILE__) ) );
		$template_name 			= $template_name[ count( $template_name ) - 1 ];
		$this->_template_name	= $template_name;
	}
	function _buildModalWindow ($name, $url)
	{
		JHTML::_('behavior.modal', 'a.jsn-modal');
		$url 	= JURI::root().'templates/'.$this->_template_name.'/'.$url;
		$link 	= '<a class="jsn-modal" rel="{handler: \'iframe\', size: {x: 600, y: 450}}" href="'.$url.'">'.$name.'</a>';
		return $link;
	}
	function jsnVersion()
	{
		$path_base 				= $this->_template_path_of_base;
		$template_name			= $this->_template_name;					
		$obj_jsn_utils 			= new JSNUtils();
		$result 				= $obj_jsn_utils->getTemplateDetails( $path_base, $template_name );
		$version 				=  $result->version;
		if(!is_null($this->_url) && $this->_url !='')
		{
			$version = '<strong>'.$result->version.'</strong>'.'&nbsp;-&nbsp;<a href="'.$this->_url.'" target="_blank" class="link-action">'.JText::_('CHECK_FOR_UPDATE').'</a>';
		}
		return $version;
	}
	function jsnLabel()
	{
		$tag 		= ( $this->_node->attributes( 'tag' ) ? $this->_node->attributes( 'tag' ) : 'div' );
		$class 		= ' class="'.( $this->_node->attributes( 'class' ) ? $this->_node->attributes('class') : 'jsn-label' ).'"';
		$alt_class 	= ( $this->_node->attributes( 'altclass' ) ? ' class="'.$this->_node->attributes( 'altclass' ).'"' : '' );
		
		if ( $this->_value )
		{
			return "<$tag$class>"."<span$class>".JText::_($this->_value)."</span></$tag>";
		}
		else
		{
			return '<hr />';
		}
	}
	function jsnPanel()
	{
		JHTML::stylesheet( 'jsn_admin.css', JURI::root().'templates/'.$this->_template_name.'/css/' );
		JHTML::script('jsn_admin.js', JURI::root().'templates/'.$this->_template_name.'/js/');
		static $count = 1;
		$output = "";
		$alt_class 				= ( $this->_node->attributes( 'altclass' ) ? $this->_node->attributes( 'altclass' ) : 'none' );
		$class 					= 'class="'. ( $this->_node->attributes( 'class' ) ? $this->_node->attributes('class') : 'title jsn-pane-toggler' ).'"';
		$opens_table 			= '<table class="paramlist admintable" width="100%" cellspacing="1" border="0"><tbody><tr><td>';
		$close_table 			= '</td></tr></tbody></table>';
		$surround_opens 		= '<div class="jsn-panel">';
		$surround_close 		= '</div>';
		$title 					= '<h3 id="panel-'.$count.'" '.$class.'><span class="toggle-arrow"><span class="param-icon"><span class="'.$alt_class.'">'.JText::_($this->_value).'</span></span></span></h3>';
		$inner_opens 			= '<div class="jsn-pane-slider content">';
		$inner_close 			= '</div>';
		$accordion_opens 		= '';
		$accordion_close 		= '';
		$accordion_condition 	= $this->_node->attributes('position');
		$controller		 		= '';
		if(!defined ('PANEL_CONTROLLER'))
		{
			$controller = '<div class="jsn-controller">';
			$controller .= '<a id="expand-all" href="#" title="'.JText::_('EXPAND_ALL_PANELS').'">'.JText::_('EXPAND_ALL').'</a>';
			$controller .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
			$controller .= '<a id="collapse-all" href="#" title="'.JText::_('COLLAPSE_ALL_PANELS').'">'.JText::_('COLLAPSE_ALL').'</a>';
			$controller .= '</div>';
			
			define('PANEL_CONTROLLER', 1);
		}
		if($accordion_condition == 'first')
		{
			$accordion_opens 	= '<div class="jsn-pane-sliders" id="content-pane" style="width:100%;">';
		}
		$output .= $surround_close;
		if($accordion_condition == 'last')
		{
			$accordion_close 	= '</div>';
		}
		$output = $accordion_close . $close_table . $inner_close . $surround_close . $accordion_opens . $controller . $surround_opens . $title . $inner_opens . $opens_table;
		$count++;
		return $output;	
	}
	function jsnList()
	{
		$class 		= 'class="'. ( $this->_node->attributes( 'class' ) ? $this->_node->attributes( 'class' ) : 'inputbox' ).'"';
		$disabled 	= ( $this->_node->attributes( 'disabled' ) ? 'disabled="'.$this->_node->attributes( 'disabled' ).'"' : '' );
		$options 	= array ();
		
		if( count( $this->_node->children() ) )
		{
			foreach ( $this->_node->children() as $option )
			{
				$val		= $option->attributes( 'value' );
				$text		= $option->data();
				$options[] 	= JHTML::_( 'select.option', $val, JText::_( $text ) );
			}
		}

		return JHTML::_( 'select.genericlist',  $options, $this->_control_name.'['.$this->_name.']', $class.' '.$disabled, 'value', 'text', $this->_value, $this->_control_name.$this->_name );
	}
	
 	function jsnRadio()
	{
		$class 		= 'class="'. ( $this->_node->attributes( 'class') ? $this->_node->attributes( 'class' ) : 'inputbox' ).'"';
		$disabled 	= ( $this->_node->attributes( 'disabled' ) ? 'disabled="'.$this->_node->attributes( 'disabled' ).'"' : '' );
		$options 	= array ();
		
		if( count( $this->_node->children() ) )
		{
			foreach ( $this->_node->children() as $option )
			{
				$val		= $option->attributes( 'value' );
				$text		= $option->data();
				$options[] 	= JHTML::_( 'select.option', $val, JText::_( $text ) );
			}
		}

		return JHTML::_( 'select.radiolist', $options, ''.$this->_control_name.'['.$this->_name.']', $class.' '.$disabled, 'value', 'text', $this->_value, $this->_control_name.$this->_name );
	}
	
	function jsnText()
	{
		$size 		= ( $this->_node->attributes( 'size' ) ? 'size="'.$this->_node->attributes( 'size' ).'"' : '' );
		$class 		= 'class="'. ( $this->_node->attributes( 'class' ) ? $this->_node->attributes( 'class' ) : 'text_area' ).'"';
		$disabled 	= ( $this->_node->attributes( 'disabled' ) ? 'disabled="'.$this->_node->attributes( 'disabled' ).'"' : '' );
		$value		= htmlspecialchars( html_entity_decode( $this->_value, ENT_QUOTES ), ENT_QUOTES );

		return '<input type="text" name="'.$this->_control_name.'['.$this->_name.']" id="'.$this->_control_name.$this->_name.'" value="'.$value.'" '.$class.' '.$size.' '.$disabled.' />';
	}
	function jsnCredits()
	{
		$value = JText::_($this->_value);
		if(!is_null($this->_url) && $this->_url !='')
		{
			$value = '<a href="'.$this->_url.'" target="_blank" class="link-action">'.$value.'</a>';
		}
		return $value;
	}
	function jsnLicense()
	{
		$path_base 				= $this->_template_path_of_base;
		$template_name			= $this->_template_name;
		$obj_jsn_utils 			= new JSNUtils();
		$result 				= $obj_jsn_utils->getTemplateDetails( $path_base, $template_name );
		return $result->license;
	}
	function jsnCopyright()
	{
		$path_base 				= $this->_template_path_of_base;
		$template_name			= $this->_template_name;
		$obj_jsn_utils 			= new JSNUtils();
		$result 				= $obj_jsn_utils->getTemplateDetails( $path_base, $template_name );
		return $result->copyright;
	}
	function jsnAuthor()
	{
		$path_base 				= $this->_template_path_of_base;
		$template_name			= $this->_template_name;
		$obj_jsn_utils 			= new JSNUtils();
		$result 				= $obj_jsn_utils->getTemplateDetails( $path_base, $template_name );
		return '<a target="_blank" title="JoomlaShine.com" href="'.$result->authorUrl.'" class="link-action">'.$result->author.'</a>';
	}
	function jsnSampleData()
	{
		$template_name	= $this->_template_name;
		$template_name_demo  = explode('_', $template_name);
		return '<a class="link-action" href="../index.php?template='.$template_name.'&tmpl=jsn_installsample">'.JText::_('Install sample data').'</a> '.JText::_('as seen on').'<a href="http://demo.joomlashine.com/joomla-templates/'.@$template_name_demo[0].'_'.@$template_name_demo[1].'/" target="_blank"> '.JText::_('demo website').'</a>';
	}	
}
?>