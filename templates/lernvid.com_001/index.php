<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >

<head>

<jdoc:include type="head" />

<?php require("head_includes.php"); ?>

</head>

<body>
    <div id="left_border">
        <div id="right_border">
            <div id="bottom">
                <div id="bottom_right"> 
                    <div id="bottom_left">
                    	<div id="container">

                                <div id="banner_out">
                                <div id="banner_in">
									<?php if($this->countModules('banner')) : ?>
                                    	<div id="banner"><jdoc:include type="module" name="banners" style="xhtml" /></div>
                                    <?php endif; ?>
                                    <div class="sitetitle">Random Rants</div>
                                </div>           
                            </div>
                            <div id="header">
                                <div id="date"><?php echo JHTML::Date( 'now', '%A, %d.%m.%Y' ); ?></div>
                                <div id="pathway"><jdoc:include type="module" name="breadcrumbs" /></div>
                                <?php if($this->countModules('user4')) : ?>
                                <div id="search_out">
                                	<div id="search"><jdoc:include type="modules" name="user4" style="xhtml" /></div>
                                </div>
							  <?php endif; ?>
                            </div>

                            <div id="topmenu_out">
                                <div id="topmenu_left">
                                    <div id="topmenu_right">
                                    	<div id="topmenu"><jdoc:include type="modules" name="user3" style="xhtml" /></div>    
                                    </div>
                                </div>
                            </div>
                            <div id="maincontent">
								<?php if($this->countModules('left')) : ?>
                                	<div id="left_out"><jdoc:include type="modules" name="left" style="rounded" /></div>
                                <?php endif; ?>
                                <?php if($this->countModules('right')) : ?>
                                	<div id="right_out"><jdoc:include type="modules" name="right" style="rounded" /></div>
                                <?php endif; ?>
                                <div id="content_out<?php echo $contentwidth; ?>">
									<?php if($this->countModules('user1 or user2')) : ?>
                                        <div class="user_bg">
                                            <div class="user_left">
                                                <div class="user_right">
													<?php if($this->countModules('user1')) : ?>
                                                        <div class="topmodule_user<?php echo $topuserwidth; ?>">
                                                        	<div class="user_inside1"><jdoc:include type="modules" name="user1" style="xhtml" /></div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if($this->countModules('user2')) : ?>
                                                        <div class="separator"></div>
                                                        <div class="topmodule_user<?php echo $topuserwidth; ?>">
                                                        	<div class="user_inside"><jdoc:include type="modules" name="user2" style="xhtml" /></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div id="content">
                                        <div id="content_border_right">
                                            <div id="content_border_left">
                                                <div id="content_bottom_right">
                                                	<div id="content_bottom_left"><jdoc:include type="component" /></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clr"></div>
							<?php if($this->countModules('user5 or user6 or user7')) : ?>
                            <div id="user_bottom">
                                    <div class="user_bg">
                                        <div class="user_left">
                                            <div class="user_right">
												<?php if($this->countModules('user5')) : ?>
                                                    <div class="bottom_user<?php echo $userwidth; ?>">
                                                    	<div class="user_inside1"><jdoc:include type="modules" name="user5" style="xhtml" /></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if($this->countModules('user6')) : ?>
                                                    <div class="separator"></div>
                                                    <div class="bottom_user<?php echo $userwidth; ?>">
                                                    	<div class="user_inside"><jdoc:include type="modules" name="user6" style="xhtml" /></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if($this->countModules('user7')) : ?>
                                                    <div class="separator"></div>
                                                    <div class="bottom_user<?php echo $userwidth; ?>">
                                                    	<div class="user_inside"><jdoc:include type="modules" name="user7" style="xhtml" /></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                            	</div>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <div id="footer">
            <div id="othermenu">
                <div id="othermenu_in"><jdoc:include type="modules" name="footer" style="xhtml" /></div>
            </div>
            <div id="copy">&copy; Designed by <a href="http://www.lernvid.com" target="_blank">LernVid.com</a></div>
        </div>
        </div>
    </div>
</body>
</html>