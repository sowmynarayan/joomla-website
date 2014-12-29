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
	<div id="container">
		<div id="masthead">
			<?php if (($this->params->get('showSitetitle')) !=0) : ?>
		    	<div id="sitetitle">Random Rants of a Maverick</div>
			<?php endif; ?>		
		</div>
		<?php if($this->countModules('user3')) : ?>
			<div id="navigation">
	             <jdoc:include type="modules" name="user3" style="xhtml" />
			</div>
		<?php endif; ?>
		<div id="page_content">
				<?php if($this->countModules('left')) : ?>
					<div id="left_outer">
		                <div id="left_top"></div>
		                <div id="left_inner_float">
			                <div id="left_inner">
			                	<jdoc:include type="modules" name="left" style="xhtml" />
			                </div>
		                </div>
		                <div id="left_bottom"></div>
	                </div>					
       		   <?php endif; ?>		
				<?php if($this->countModules('right')) : ?>
					<div id="right_outer">
		                <div id="right_top"></div>
		                <div id="right_inner_float">
			                <div id="right_inner">
			                	<jdoc:include type="modules" name="right" style="xhtml" />
			                </div>
		                </div>
		                <div id="right_bottom"></div>
	                </div>					
				<?php endif; ?>		
			    <div id="content_out<?php echo $contentwidth; ?>">
				<div id="content_outer">
					<div id="content_up">
						<div id="content_up_left">
							<?php if($this->countModules('breadcrumb')) : ?>
								<div id="breadcrumbs">
					            	<jdoc:include type="module" name="breadcrumbs" />
							    </div>
							<?php endif; ?>
							<div id="content_up_right">
								<?php if($this->countModules('user4')) : ?>
									<div id="search">
										<div id="search_inner">
								             <jdoc:include type="modules" name="user4" style="xhtml" />
										</div>
									</div>
								<?php endif; ?>		
							</div>
						</div>
					</div>
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
					<div id="content">
						 <jdoc:include type="component" />
					</div>
					<div class="clr"></div>
					<div id="content_down">
						<div id="content_down_left">
							<div id="content_down_right"></div>
						</div>
					</div>	
				</div>
				</div>
			</div>	
		<div id="container2">
			<div id="footer">
				<div id="date"><p><?php echo JHTML::Date($this->date_field, "%A, %d. %B %Y"); ?></p></div>
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
	<!-- <div id="designed_by">
		<p>| Design by: <a href="http://www.lernvid.com" target="_blank">LernVid.com |</a></p>
	</div>
        -->
</div>
<jdoc:include type="modules" name="debug" style="xhtml" />
</body>
</html>