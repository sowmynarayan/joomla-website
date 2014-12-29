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
<meta name="licence" content="Copywright LernVid.com - Creative Commons Sharalike 3.0" />
<link href="templates/<?php echo $this->template ?>/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="templates/<?php echo $this->template ?>/css/template.css" rel="stylesheet" type="text/css" media="all" />
	  <!--[if IE 7]>
	  <link href="templates/<?php echo $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" media="all" />
   <![endif]-->
   <!--[if lt IE 7]>
	  <link href="templates/<?php echo $this->template ?>/css/ie5x6x.css" rel="stylesheet" type="text/css" media="all" />
   <![endif]-->
	<?php
		if($this->countModules("left")&&!$this->countModules("right")){ $contentwidth="left";}
		if($this->countModules("right")&&!$this->countModules("left")){ $contentwidth="right";}
		if($this->countModules("left")&&$this->countModules("right")) {$contentwidth="middle"; }
	?>
<?php if (($this->params->get('useJavascript')) !=0) : ?>
	<script type="text/javascript" src="templates/<?php echo $this->template ?>/js/hover.js"></script>
<?php endif;?>
</head>

<body>
<div id="wrapper">
	<div id="bg_up">
		<div id="masthead_container">
			<div id="masthead">
				<div id="sitetitle"><p><?php echo $mainframe->getCfg('sitename');?></p></div>
			</div>
		</div>
		<div id="container">
		<!-- Begin Container -->
			<?php if($this->countModules('user4')) : ?>
				<div id="searchbox">
					<div id="search">
						<div id="search_inner">
				             <jdoc:include type="modules" name="user4" style="xhtml" />
						</div>
					</div>
				</div>	
			<?php endif; ?>		
			<?php if($this->countModules('user3')) : ?>
				<div id="navigation">
		             <jdoc:include type="modules" name="user3" style="xhtml" />
				</div>
			<?php endif; ?>
			<!-- Begin Page Content -->
			<div id="page_content">
				<!-- Begin Content Upside -->
				<div id="content_up">
					<div id="content_up_left">
							<div id="breadcrumbs">
				            	<jdoc:include type="module" name="breadcrumbs" />
						    </div>
						<div id="content_up_right">
						</div>
					</div>
				</div>
				<!-- End Content Upside -->
					<?php if($this->countModules('left')) : ?>
						<div id="sidebar_left">
				             <jdoc:include type="modules" name="left" style="xhtml" />
						</div>
					<?php endif; ?>		
					<?php if($this->countModules('right')) : ?>
						<div id="sidebar_right">
				             <jdoc:include type="modules" name="right" style="xhtml" />
						</div>
					<?php endif; ?>		
				<!-- Begin Content Inner -->
				    <div id="content_out<?php echo $contentwidth; ?>">
						<?php if($this->countModules('user1 or user2')) : ?>
							<div id="user_modules1">
								<?php if($this->countModules('user1')) : ?>
									<div id="user1">
							           <jdoc:include type="modules" name="user1" style="xhtml" />
									</div>
								<?php endif; ?>			
								<?php if($this->countModules('user2')) : ?>
									<div id="user2">
							           <jdoc:include type="modules" name="user2" style="xhtml" />
									</div>
								<?php endif; ?>			
							</div>					
						<?php endif; ?>		
						<?php if($this->countModules('top')) : ?>
							<div id="top">
					             <jdoc:include type="modules" name="top" style="xhtml" />
							</div>
						<?php endif; ?>		
						<div class="content">
							 <jdoc:include type="component" />
						</div>
					</div>
				</div>	
				<!-- Begin Content Downside -->
				<div class="clr"></div>
				<div id="content_down">
					<div id="content_down_left">
						<div id="content_down_right">
						</div>
					</div>
				</div>	
				<!-- End Content Downside -->
			<div id="container2">
				<!-- End Page Content -->
				<?php if($this->countModules('user5 or user6')) : ?>
					<div id="user_modules2">
							<?php if($this->countModules('user5')) : ?>
								<div id="user5">
									<jdoc:include type="modules" name="user5" style="xhtml" />
								</div>
							<?php endif; ?>				
							<?php if($this->countModules('user6')) : ?>
								<div id="user6">
									<jdoc:include type="modules" name="user6" style="xhtml" />
								</div>
							<?php endif; ?>				
					</div>
				<?php endif; ?>				
				<div id="bottom">
					<div id="date"><p><?php echo JHTML::Date($this->date_field, "%A, %d. %B %Y"); ?></p></div>
				</div>
				<div id="footer">
					<div id="copyright_info">
						<p><?php echo  $this->params->get('CopyrightInfo');  ?></p>
					</div>
					<?php if($this->countModules('footer')) : ?>
						<jdoc:include type="modules" name="footer" />
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="clr"></div>
		<div id="designed_by">
			<p>Valid <a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML</a> &amp; <a href="http://jigsaw.w3.org/css-validator/check?uri=templates/<?php echo $this->template ?>/css/template.css" target="_blank">CSS</a> | Design by: <a href="http://www.lernvid.com" target="_blank">LernVid.com</a></p>
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="xhtml" />
</body>
</html>