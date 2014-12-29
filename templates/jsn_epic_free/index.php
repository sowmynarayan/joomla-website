<?php
/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2009 JoomlaShine.com. All rights reserved.
* @license   Copyrighted Commercial Software
* This file may not be redistributed in whole or significant part.
*/

	// no direct access
	defined( '_JEXEC' ) or die( 'Restricted index access' );
	define( 'YOURBASEPATH', dirname(__FILE__) );

	// template setup
	require(YOURBASEPATH.'/php/lib/jsn_utils.php');
	require(YOURBASEPATH.'/php/lib/jsn_mobile.php');
	require(YOURBASEPATH.'/php/jsn_setup.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- <?php echo $template_details->name; ?> <?php echo $template_details->version; ?> -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language ?>" dir="<?php echo $template_direction; ?>">
<head>
<jdoc:include type="head" />
<?php require(YOURBASEPATH.'/php/jsn_head.php'); ?>
</head>
<body id="jsn-master" class="jsn-textstyle-<?php echo $template_textstyle; ?> jsn-textsize-<?php echo $template_textsize; ?> jsn-color-<?php echo $template_color; ?> jsn-direction-<?php echo $template_direction; ?><?php echo ($pageclass != "")?" ".$pageclass:""; ?>">
<a name="top" id="top"></a>
<div id="jsn-page">
	<?php if ($this->countModules( 'stickleft' ) > 0) { ?>
	<div id="jsn-pstickleft">
		<jdoc:include type="modules" name="stickleft" style="jsnxhtml" />
	</div>
	<?php } ?>
	<?php if ($this->countModules( 'stickright' ) > 0) { ?>
	<div id="jsn-pstickright">
		<jdoc:include type="modules" name="stickright" style="jsnxhtml" />
	</div>
	<?php } ?>
	<div id="jsn-header">
		<div id="jsn-logo">
			<?php if ($this->countModules( 'logo' ) > 0) { ?>
			<jdoc:include type="modules" name="logo" style="jsnxhtml" />
			<?php } else { ?>
			<?php if ($logo_link != "") echo '<a href="'.$logo_link.'" title="'.$logo_slogan.'">'; ?>
			<img src="<?php echo $template_path."/images/".($enable_colored_logo?$template_color."/":""); ?>logo.png" alt="<?php echo $logo_slogan; ?>" />
			<?php if ($logo_link != "") echo '</a>'; ?>
			<?php } ?>
		</div>
		<?php if ($this->countModules( 'inset' ) > 0) { ?>
		<div id="jsn-pinset">
			<jdoc:include type="modules" name="inset" style="jsnxhtml" />
		</div>
		<?php } ?>
	</div>
	<div id="jsn-body">
		<?php if ($jsnutils->countPositions($this, array('mainmenu', 'toolbar'))) { ?>
		<div id="jsn-menu">
			<?php if ($this->countModules( 'mainmenu' ) > 0) { ?>
			<div id="jsn-pmainmenu">
				<jdoc:include type="modules" name="mainmenu" style="jsnxhtml" />
			</div>
			<?php } ?>
			<?php if ($this->countModules( 'toolbar' ) > 0) { ?>
			<div id="jsn-ptoolbar">
				<jdoc:include type="modules" name="toolbar" style="jsnxhtml" />
			</div>
			<?php } ?>
			<div class="clearbreak"></div>
		</div>
		<?php } ?>
		<?php if ($this->countModules( 'promo' ) > 0) { ?>
		<div id="jsn-featured">
			<div id="jsn-ppromo" class="jsn-column">
				<jdoc:include type="modules" name="promo" style="jsntrio" />
			</div>
			<div class="clearbreak"></div>
		</div>
		<?php } ?>
		<div id="jsn-content">
			<div id="jsn-content_inner">
				<div id="jsn-content_inner1">
					<div id="jsn-content_inner2">
						<div id="jsn-maincontent">
							<div id="jsn-maincontent_inner">
								<div id="jsn-maincontent_inner2">
									<?php if ($this->countModules( 'innerleft' ) > 0) { ?>
									<div id="jsn-pinnerleft">
										<div id="jsn-pinnerleft_inner">
											<jdoc:include type="modules" name="innerleft" style="jsntrio" />
										</div>
									</div>
									<?php } ?>
									<div id="jsn-pcentercol">
										<div id="jsn-pcentercol_inner">
											<?php if ($this->countModules( 'breadcrumbs ' ) > 0) { ?>
											<div id="jsn-pbreadcrumbs">
												<jdoc:include type="modules" name="breadcrumbs" />
											</div>
											<?php } ?>
											<?php
											$positionCount = $jsnutils->countPositions($this, array('user1', 'user2'));
											if($positionCount){
												$grid_suffix = "_grid".$positionCount;
										?>
											<div id="jsn-usermodules1">
												<div id="jsn-usermodules1_inner<?php echo $grid_suffix; ?>">
													<?php if ($this->countModules( 'user1' ) > 0) { ?>
													<div id="jsn-puser1<?php echo $grid_suffix; ?>" class="jsn-column">
														<div id="jsn-puser1">
															<jdoc:include type="modules" name="user1" style="jsntrio" />
														</div>
													</div>
													<?php } ?>
													<?php if ($this->countModules( 'user2' ) > 0) { ?>
													<div id="jsn-puser2<?php echo $grid_suffix; ?>" class="jsn-column">
														<div id="jsn-puser2">
															<jdoc:include type="modules" name="user2" style="jsntrio" />
														</div>
													</div>
													<?php } ?>
													<div class="clearbreak"></div>
												</div>
											</div>
											<?php } ?>
											<?php if ($show_frontpage) { ?>
											<div id="jsn-mainbody">
												<jdoc:include type="message" />
												<jdoc:include type="component" />
											</div>
											<?php } ?>
											<?php if ($this->countModules( 'banner' ) > 0) { ?>
											<div id="jsn-banner">
												<jdoc:include type="modules" name="banner" style="jsntrio" />
											</div>
											<?php } ?>
										</div>
									</div>
									<?php if ($this->countModules( 'innerright' ) > 0) { ?>
									<div id="jsn-pinnerright">
										<div id="jsn-pinnerright_inner">
											<jdoc:include type="modules" name="innerright" style="jsntrio" />
										</div>
									</div>
									<?php } ?>
									<div class="clearbreak"></div>
								</div>
							</div>
						</div>
						<?php if ($this->countModules( 'left' ) > 0) { ?>
						<div id="jsn-leftsidecontent" class="jsn-column">
							<div id="jsn-pleft">
								<jdoc:include type="modules" name="left" style="jsntrio" />
							</div>
						</div>
						<?php } ?>
						<?php if ($this->countModules( 'right' ) > 0) { ?>
						<div id="jsn-rightsidecontent" class="jsn-column">
							<div id="jsn-pright">
								<jdoc:include type="modules" name="right" style="jsntrio" />
							</div>
						</div>
						<?php } ?>
						<div class="clearbreak"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
				$positionCount = $jsnutils->countPositions($this, array('footer', 'bottom'));
				if($positionCount){
					$grid_suffix = "_grid".$positionCount;
			?>
		<div id="jsn-footer">
			<?php if ($this->countModules( 'footer' ) > 0) { ?>
			<div id="jsn-pfooter<?php echo $grid_suffix; ?>" class="jsn-column">
				<div id="jsn-pfooter">
					<jdoc:include type="modules" name="footer" style="jsnxhtml" />
				</div>
			</div>
			<?php } ?>
			<?php if ($this->countModules( 'bottom' ) > 0) { ?>
			<div id="jsn-pbottom<?php echo $grid_suffix; ?>" class="jsn-column">
				<div id="jsn-pbottom">
					<jdoc:include type="modules" name="bottom" style="jsnxhtml" />
				</div>
			</div>
			<?php } ?>
			<div class="clearbreak"></div>
		</div>
		<?php } ?>
	</div>
</div>
	<?php
		/*** REMOVAL OR MODIFICATION COPYRIGHT TEXT BELLOW IS VIOLATION OF JOOMLASHINE.COM TERMS & CONDITIONS AND DEPRIVES OF ANY KIND OF SUPPORTS ***/
		$copyright_text = '<div id="jsn-copyright"><a href="http://www.joomlashine.com">Free Joomla Templates by JoomlaShine.com</a></div>';
		echo $copyright_text;
	?>
<jdoc:include type="modules" name="debug" />
<?php require( YOURBASEPATH.DS."php/jsn_debug.php"); ?>
</body>
</html>