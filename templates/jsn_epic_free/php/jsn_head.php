<?php
/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2009 JoomlaShine.com. All rights reserved.
* @license   Copyrighted Commercial Software
* This file may not be redistributed in whole or significant part.
*/

// The data bellow goes between the <head></head> tags of the template

// CSS inclusion
$this->addStylesheet($this->baseurl."/templates/system/css/system.css");
$this->addStylesheet($this->baseurl."/templates/system/css/general.css");
$this->addStylesheet($template_path."/css/template.css");
$this->addStylesheet($template_path."/css/jsn_jcore.css");

// Load specific CSS file for template color
$this->addStylesheet($template_path."/css/template_".$template_color.".css");

if($template_direction == "rtl") { $this->addStylesheet($template_path."/css/template_rtl.css"); }
// JS inclusion
$this->addScript($template_path."/js/jsn_utils.js");
?>

<!--[if IE 6]>
<link href="<?php echo $template_path; ?>/css/jsn_fixie6.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $template_path; ?>/js/jsn_fixpng.js"></script>
<script>
  DD_belatedPNG.fix('#mod_search_searchword, span.breadcrumbs a, #base-mainmenu ul, #base-mainmenu span,  ul.menu-treemenu a, .author, .createdate, ul.list-arrow-red li, ul.list-arrow-blue li, ul.list-arrow-green li, .jsn-top, .jsn-top_inner, .jsn-middle, .jsn-middle_inner, .jsn-bottom, .jsn-bottom_inner');
</script>
<![endif]-->

<!--[if IE 7]>
<link href="<?php echo $template_path; ?>/css/jsn_fixie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<?php
	// Inline CSS styles for template layout
	echo '<style type="text/css">';

	// Setup template width parameter
	$twidth = 0;
	switch ($template_width) {
		case 'narrow':
			$twidth = $narrow_width;
			break;
		
		case 'wide':
			$twidth = $wide_width;
			break;

		case 'float':
			$twidth = $float_width;
			break;
	}
	
	if ($twidth > 100) {
		echo '
		#jsn-page {
			width: '.$twidth.'px;
		}
		';
	} else {
		echo '
		#jsn-page {
			width: '.$twidth.'%;
		}
		';
	}

	// Setup width of content area
	$tw = 100;
	if ($has_left) {
		$tw -= $left_width;
		echo '
			#jsn-content_inner {
				background: transparent url('.$template_path.'/images/bg/leftside'.$left_width.'-bg-full.png) repeat-y '.$left_width.'% top;
				padding: 0;
			}
		';
	}
		
	if ($has_right) {
		$tw -= $right_width;
		echo '
			#jsn-content_inner1 {
				background: transparent url('.$template_path.'/images/bg/rightside'.$right_width.'-bg-full.png) repeat-y '.(100-$right_width).'% top;
				padding: 0;
			}
		';
	}

	echo '
	#jsn-leftsidecontent {
		float: left;
		width: '.$left_width.'%;
		position: relative;
		left: -'.($tw-0.1).'%;
	}
	#jsn-maincontent {
		float: left;
		width: '.($tw-0.1).'%;
		position: relative;
		left: '.(($has_left)?$left_width.'%':0).';
	}
	#jsn-rightsidecontent {
		float: right;
		width: '.$right_width.'%;
	}
	';
	
	$tw = 100;
	if ($has_innerleft) {
		$tw -= $innerleft_width;
		echo '
		#jsn-maincontent_inner {
			background: url('.$template_path.'/images/border.png) '.$innerleft_width.'% top repeat-y;
		}
		';
	}
	if ($has_innerright) {
		$tw -= $innerright_width;
		echo '
		#jsn-maincontent_inner2 {
			background: url('.$template_path.'/images/border.png) '.(100 - $innerright_width).'% top repeat-y;
		}
		';
	}

	echo '
	#jsn-pcentercol {
		width: '.($tw-0.1).'%;
		float: left;
	}
	#jsn-pinnerright {
		width: '.$innerright_width.'%;
		float: right;
	}
	#jsn-pinnerleft {
		width: '.$innerleft_width.'%;
		float: left;
	}
	';
	
	echo '</style>';
?>

<!-- JS Includes -->
<?php
	echo '<script type="text/javascript"><!--'."\n";
	echo 'var templatePath = "'.$template_path.'";'."\n";
	echo 'var enableRTL = '.(($template_direction == "rtl")?'true':'false').';'."\n";
	echo 'var rspAlignment = "'.$rsp_alignment.'";'."\n";
	echo 'var lspAlignment = "'.$lsp_alignment.'";'."\n";
	echo 'var isFloat = '.($template_width == 'float'?'true':'false').';'."\n";
	echo '--></script>'."\n";
	echo '<script type="text/javascript" src="'.$template_path.'/js/jsn_template.js"></script>';
?>