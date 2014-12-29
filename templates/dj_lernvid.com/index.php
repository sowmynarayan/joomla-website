<?php
defined( '_JEXEC' ) or die( 'Access to this location is RESTRICTED.' );
echo '<?xml version="1.0" encoding="utf-8"?'.'>'; 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />
<?php
	// inserting mootools
		JHTML::_('behavior.mootools');
?>
<meta name="designer" content="Juergen Koller - http://www.lernvid.com" />
<meta name="licence" content="Copywright http://www.xpellshop.com/" />
<link href="templates/<?php echo $this->template ?>/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="templates/<?php echo $this->template ?>/css/template.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template?>/css/<?php echo $this->params->get('colorVariation'); ?>.css" rel="stylesheet" type="text/css" />
	  <!--[if IE 7]>
	  <link href="templates/<?php echo $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" media="all" />
   <![endif]-->
   <!--[if lt IE 7]>
	  <link href="templates/<?php echo $this->template ?>/css/ie5x6x.css" rel="stylesheet" type="text/css" media="all" />
   <![endif]-->
<?php if (($this->params->get('useJavascript')) !=0) : ?>
	<script type="text/javascript" src="templates/<?php echo $this->template ?>/js/hover.js"></script>
<?php endif;?>
</head>
<body>
<div id="wrapper">
	<div id="bg_up">
		<div id="masthead_container">
			<div id="navigation_container">
				<?php if($this->countModules('user3')) : ?>
					<div id="navigation">
			             <jdoc:include type="modules" name="user3" style="xhtml" />
					</div>
				<?php endif; ?>
			</div>
			<div id="masthead">
					<div id="searchbox">
						<div id="search">
							<div id="search_inner">
					             <jdoc:include type="modules" name="user4" style="xhtml" />
							</div>
						</div>
					</div>	
			</div>
		</div>
		<div id="container">
			<div id="page_content">
				<?php if($this->countModules('left')) : ?>
					<div id="sidebar_left">
			             <jdoc:include type="modules" name="left" style="rounded" />
					</div>
				<?php endif; ?>		
				<div id="content">
					 <jdoc:include type="component" />
				</div>
				<div class="clr"></div>
			</div>	
			<div id="container2">
						<?php if($this->countModules('user1 or user2')) : ?>
							<div id="user_modules1">
								<?php if($this->countModules('user1')) : ?>
									<div id="user1">
							           <jdoc:include type="modules" name="user1" style="rounded" />
									</div>
								<?php endif; ?>			
								<?php if($this->countModules('user2')) : ?>
									<div id="user2">
							           <jdoc:include type="modules" name="user2" style="rounded" />
									</div>
								<?php endif; ?>			
							</div>					
						<?php endif; ?>		
				<div id="footer">
					<jdoc:include type="modules" name="footer" />
				</div>
				<div id="designed_by">
			  		<p>Valid <a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML</a> &amp; <a href="http://jigsaw.w3.org/css-validator/check?uri=templates/<?php echo $this->template ?>/css/template.css" target="_blank">CSS</a> | designed: <a href="http://www.lernvid.com" target="_blank">-LernVid.com-</a></p>
				</div>
				<div class="clr"></div>
			</div>
		</div>
	</div>
	<div id="bottom"></div>
</div>
<jdoc:include type="modules" name="debug" style="xhtml" />
</body>
</html>