<?php
/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2009 JoomlaShine.com. All rights reserved.
* @license   Copyrighted Commercial Software
* This file may not be redistributed in whole or significant part.
*/

	// no direct access
	defined('_JEXEC') or die('Restricted access');

	// Include MooTools libs
	JHTML::_('behavior.mootools');
	
	/* PRIVATE TEMPLATE PARAMETERS */
	/****************************************************************/
	//Global Variable
	global $jsn_richmenu_separator;
	
	// Include JSN Utils
	$jsnutils = &JSNUtils::getInstance();

	/* Get template details */
	$template_details = $jsnutils->getTemplateDetails(YOURBASEPATH, $this->template);
	$template_prefix = $template_details->name.'-';
	$template_path = $this->baseurl.'/templates/'.$this->template;
	$template_direction = $this->direction;
	
	$has_right = $this->countModules('right');
	$has_left = $this->countModules('left');
	$has_innerleft = $this->countModules('innerleft');
	$has_innerright = $this->countModules('innerright');

	$site_name = $mainframe->getCfg('sitename');
	$pageclass = '';
	$menus = &JSite::getMenu();
	$menu = $menus->getActive();
	if (is_object($menu)) {
		$params = new JParameter( $menu->params );
		$pageclass = $params->get( 'pageclass_sfx', '');
	}
	$not_homepage = ($menu != $menus->getDefault());

	/****************************************************************/
	/* TEMPLATE PARAMETERS */

	/* Path to logo image starting from the Joomla! root folder (! without preceding slash !). */
	$enable_colored_logo = ($this->params->get("enableColoredLogo", 0) == 1)?true:false;

	/* URL where logo image should link to (! without preceding slash !).
	 * Leave this box empty if you want your logo to be clickable. */
	$logo_link = $this->params->get("logoLink", "");
	$logo_link = ($logo_link != "")?$this->baseurl."/".$logo_link:"";

	/* Slogan text to be attached to the logo image ALT text for SEO purpose. */
	$logo_slogan = $this->params->get("logoSlogan", "");

	/* Overall template width. */
	$template_width = $this->params->get("templateWidth", "narrow");

	/* Define custom width for template in narrow mode */
	$narrow_width = intval($this->params->get("narrowWidth", "960"));
	
	/* Define custom width for template in wide mode */
	$wide_width = intval($this->params->get("wideWidth", "1150"));
	
	/* Define custom width for template in float mode */
	$float_width = intval($this->params->get("floatWidth", "90"));
	$float_width = ($float_width > 100)?100:$float_width;

	/* Left column width specified in percentage within range (18% - 33%).
	   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
	$left_width = intval($this->params->get("leftWidth", "23"));
	$left_width = ($left_width > 33)?33:$left_width;
	$left_width = ($left_width < 18)?18:$left_width;

	/* Right column width specified in percentage within range (18% - 33%).
	   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
	$right_width = intval($this->params->get("rightWidth", "23"));
	$right_width = ($right_width > 33)?33:$right_width;
	$right_width = ($right_width < 18)?18:$right_width;

	/* InnerLeft column width specified in percentage within range (18% - 33%).
	   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
	$innerleft_width = intval($this->params->get("innerleftWidth", "23"));
	$innerleft_width = ($innerleft_width > 33)?33:$innerleft_width;
	$innerleft_width = ($innerleft_width < 18)?18:$innerleft_width;

	/* InnerRight column width specified in percentage within range (18% - 33%).
	   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
	$innerright_width = intval($this->params->get("innerrightWidth", "23"));
	$innerright_width = ($innerright_width > 33)?33:$innerright_width;
	$innerright_width = ($innerright_width < 18)?18:$innerright_width;

	/* Define vertical position of module in Leftstick position */
	$lsp_alignment = $this->params->get("lspAlignment", "middle");

	/* Define vertical position of module in Rightstick position */
	$rsp_alignment = $this->params->get("rspAlignment", "middle");

	/* Definition whether to show mainbody on frontpage page or not */
	$show_frontpage = ($this->params->get("showFrontpage", 1) == 1)?true:$not_homepage;

	/* Template color */
	// blue | red | green | violet | orange | grey
	$template_color = $this->params->get("templateColor", "blue");

	/* Template text style */
	// 1 - Business / Corporation | 2 - Personal / Blog | 3 - News / Magazines;
	$template_textstyle = $this->params->get("templateTextStyle", "business");

	/* Template text size */
	$template_textsize = $this->params->get("templateTextSize", "medium");
	
	/*Definition separate menu*/
	$jsn_richmenu_separator = '(=)';	
	/*Check if have user-agent that it is mobile */
	//$jsnmobile =& JSNMobile::getInstance(); 
	//$jsnmobile->runCheckMobile();
?>