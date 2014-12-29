<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$site_url_old	= $_SERVER['HTTP_HOST'];
$site_url_raw 	= str_replace('http://','',$site_url_old);
$site_url 		= 'http://'.$site_url_raw;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>

<jdoc:include type="head" />

<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/b59-tpl16/css/layout.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/b59-tpl16/css/template.css" type="text/css" />

<!--[if lte IE 7]>
<style type="text/css">
div#content_area {
	margin:-20px 190px 0 195px;
}
</style>
<![endif]-->

</head>
<body>
<div id="body_container">
	<?php if($this->countModules('user3')) : ?>
		<div id="top_container">
        	<jdoc:include type="modules" name="user3" style="top" />
        </div>
	<?php endif; ?>

	<div id="site_header">
    	<a href="<?php echo $site_url; ?>" target="_self" class="logo_link"></a>
        <?php if ($this->params->get('sitesloganactive') == 'yes') { ?>
        	<h1 class="site_slogan"><?php echo $this->params->get('siteslogantext'); ?></h1>
        <?php } ?>
    </div>

	<?php if($this->countModules('breadcrumb or user4')) : ?>
    	<div id="panel_container">
        	<div class="search_container">
            	<jdoc:include type="modules" name="user4" style="raw" />
            </div>
            <div class="breadcrumb_container">
            	<jdoc:include type="modules" name="breadcrumb" />
            </div>
        </div>
    <?php endif; ?>

	<div id="mainarea">
    	<div id="left_colm">
        	<?php if($this->countModules('left')) : ?>
				<jdoc:include type="modules" name="left" style="leftright" />
			<?php endif; ?>
        </div>
        <div id="right_colm">
        	<?php if($this->countModules('right')) : ?>
				<jdoc:include type="modules" name="right" style="leftright" />
			<?php endif; ?>
        </div>
        <div id="content_area">
        	<div class="message_container">
            	<jdoc:include type="message" />
            </div>
        	<div class="content_frame">
            	<div class="content_top"></div>
                <div id="content_main">
                	<div class="content_main">
						<jdoc:include type="component" />
            		</div>
    			</div>
                <div class="content_bottom"></div>
            </div>

            <?php if($this->countModules('user1 or user2')) {
            	$count_bottom_mod = '1';
				if($this->countModules('user1 and user2')) {
					 $count_bottom_mod++;
				} ?>
			<div id="bottommod_container">
            	<div class="bottommod_top"></div>
            	<div class="bottommod_content">
                	<?php if ($count_bottom_mod < '2') {
								if($this->countModules('user1')) { ?>
									 <jdoc:include type="modules" name="user1" style="bottom" />
								<?php }
								if($this->countModules('user2')) { ?>
									 <jdoc:include type="modules" name="user2" style="bottom" />
								<?php }
					} else { ?>
						<div class="bottommod_left">
                        	<jdoc:include type="modules" name="user1" style="bottom" />
                        </div>
                        <div class="bottommod_right">
                        	<jdoc:include type="modules" name="user2" style="bottom" />
                        </div>
						<div class="clear"></div>
					<?php } ?>
                </div>
            	<div class="bottommod_bottom"></div>
            </div>
            <?php } ?>

        </div>
    </div>

</div>

<div id="bottom2">

	<div class="link_bottom">
    	<a class="link_bottom" href="http://www.golfagentur.de/" target="_blank">Golfshop</a> auf golfagentur.de.
    </div>
	<div class="copyright">
    	<?php if($this->countModules('footer')) : ?>
			<jdoc:include type="modules" name="footer" style="raw" />
		<?php endif; ?></div>
    
</div>

</body>
</html>