<?php
defined( '_JEXEC' ) or die( 'Restricted index access' ); 
echo '<?xml version="1.0" encoding="utf-8"?'.'>'; 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
xml:lang="<?php echo $this->language; ?>"
lang="<?php echo $this->language ?>" dir="<?php echo $this->direction; ?>">
<head>
<jdoc:include type="head" />
<?php
$collspan_offset = ( $this->CountModules( 'right', 'left' ) + $this->CountModules( 'user2' ) ) ? 2 : 1;
//script to determine which div setup for layout to use based on module configuration
$user1 = 0;
$user2 = 0;
$colspan = 0;
$right = 0;
$left = 0;
$banner = 0;
$user3 = 0;
$user4 = 0;
$user5 = 0;
$top = 0;
// banner combos
//user1 combos
if ( $this->CountModules( 'user1' ) + $this->CountModules( 'user2' ) == 2) {
	$user1 = 2;
	$user2 = 2;
	$colspan = 3;
} elseif ( $this->CountModules( 'user1' ) == 1 ) {
	$user1 = 1;
	$colspan = 1;
} elseif ( $this->CountModules( 'user2' ) == 1 ) {
	$user2 = 1;
	$colspan = 1;
}
//banner based combos
if ( $this->CountModules( 'banner' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
	$banner = 1;
}
//right based combos
if ( $this->CountModules( 'right' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
	$right = 1;
}
//left based combos
if ( $this->CountModules( 'left' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
      $left = 1;
}
//top based combos
if ( $this->CountModules( 'top' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
      $top = 1;
}
//user4 based combos
if ( $this->CountModules( 'user4' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
      $user4 = 1;
}
//user3 based combos
if ( $this->CountModules( 'user3' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
      $user3 = 1;
}
//user5 based combos
if ( $this->CountModules( 'user5' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
      $user5 = 1;
}
?>
<meta name="designer" content="Juergen Koller - http://www.lernvid.com - Code teilweise by http://www.ah-68.de" />
<meta name="licence" content="Creative Commons ShareAlike 3.0" />
<link href="templates/<?php echo $this->template ?>/css/template.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="templates/<?php echo $this->template ?>/js/hover.js"></script>
</head>
<body>
<div id="wrapper">
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td><div id="header">
      <div class="sitetitle"><?php echo $mainframe->getCfg('sitename');?></div>
          <?php
							if ( $banner > 0 ) {
		  				?>
          <div id="banner">
            <div id="banner_inner">
              <jdoc:include type="modules" name="banner" style="raw" />
            </div>
          </div>
          <?php
		  			}
		  			?>
        </div></td>
    </tr>
    <?php
							if ( $user3 > 0 ) {
		  				?>
    <tr>
      <td><div id="top_menu">
          <div id="top_menu_inner">
            <jdoc:include type="modules" name="user3" style="xmhtl" />
          </div>
        </div></td>
    </tr>
    <?php
		  			}
		  			?>
    <?php
							if ( $user4 > 0 ) {
		  				?>
    <tr>
      <td><div id="top_menu_top_two">
          <div id="top_menu_top_two_inner">
            <div id="search_inner">
              <jdoc:include type="modules" name="user4" style="xhtml" />
            </div>
          </div>
        </div></td>
    </tr>
    <?php
		  			}
		  			?>
    <?php
							if ( $top > 0 ) {
		  				?>
    <tr>
      <td><div id="top_menu_top">
          <div id="top_menu_top_inner">
            <jdoc:include type="modules" name="top" style="xhtml" />
          </div>
        </div></td>
    </tr>
    <?php
		  			}
		  			?>
    <tr>
      <td id="content_outer" valign="top"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="content_table">
          <tr valign="top">
            <?php
							if ( $left > 0 ) {
		  				?>
            <td><div id="left_outer">
                <div id="left_top"></div>
                <div id="left_inner_float">
                  <div id="left_inner">
                    <jdoc:include type="modules" name="left" style="xhtml" />
                  </div>
                </div>
                <div id="left_bottom"></div>
              </div></td>
            <?php
		  			}
		  			?>
            <td align="center" width="100%" id="content"><div align="center">
                <div id="content_top_bar">
                  <div id="content_top">
                    <div id="content_right_top"></div>
                  </div>
                </div>
              </div>
              <table border="0" align="center" cellpadding="0" cellspacing="0" class="content">
                <tr>
                  <td colspan="<?php echo $colspan; ?>"><div id="topcontent">
                      <jdoc:include type="message" />
                      <?php if($this->countModules('user5')) : ?>
                      <div id="user5">
                        <jdoc:include type="modules" name="user5" style="raw" />
                      </div>
                      <?php endif; ?>
                    </div>
                    <div id="breadcrumbs">
                      <!-- PATHWAY -->
                      <div class="breadcrumbs">
                        <jdoc:include type="module" name="breadcrumbs" />
                      </div>
                      <!-- END PATHWAY-->
                    </div>
                    <div id="main_content">
                      <jdoc:include type="component" />
                    </div></td>
                </tr>
                <?php
								if ($colspan > 0) {
								?>
                <tr valign="top">
                  <?php
				  					if ( $user1 > 0 ) {
				  						?>
                  <td width="50%"><div id="user1_outer">
                      <div class="user1_inner">
                        <jdoc:include type="modules" name="user1" style="xhtml" />
                      </div>
                    </div></td>
                  <?php
				  					}
				  					if ( $colspan == 3) {
										 ?>
                  <?php
										}
				  					if ( $user2 > 0 ) {
				  						?>
                  <td width="50%"><div id="user2_outer">
                      <div class="user2_inner">
                        <jdoc:include type="modules" name="user2" style="xhtml" />
                      </div>
                    </div></td>
                  <?php
				  					}
										?>
                </tr>
                <tr>
                  <td colspan="<?php echo $colspan; ?>"></td>
                </tr>
                <?php
									}
								?>
              </table>
              <div align="center">
                <div id="content_bottom_bar">
                  <div id="content_bottom">
                    <div id="content_right_bottom"></div>
                  </div>
                </div>
              </div></td>
            <?php
							if ( $right > 0 ) {
		  				?>
            <td><div id="right_outer">
                <div id="right_top"></div>
                <div id="right_inner_float">
                  <div id="right_inner">
                    <jdoc:include type="modules" name="right" style="xhtml" />
                  </div>
                </div>
                <div id="right_bottom"></div>
              </div></td>
            <?php
		  			}
		  			?>
          </tr>
        </table>
        <div align="center">
          <div id="copy">
            <div id="copy_inner" class="copy_inner">Valid <a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML</a> &amp; <a href="http://jigsaw.w3.org/css-validator/check?uri=templates/<?php echo $this->template ?>/css/template.css" target="_blank">CSS</a> <strong> | </strong> Powered by <strong><a href="http://www.joomla.org" target="_blank">Joomla!</a> and <a href="http://www.foss.in" target="_blank">FOSS</a></strong></div>
          </div>
        </div></td>
    </tr>
  </table>
</div>
 <jdoc:include type="modules" name="debug" style="xhtml" /> 
</body>
</html>