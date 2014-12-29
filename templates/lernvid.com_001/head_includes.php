<meta http-equiv="Content-Type" content="text/html; <?php echo _ISO; ?>" />

<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template?>/css/template.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template?>/css/<?php echo $this->params->get('colorVariation'); ?>.css" rel="stylesheet" type="text/css" />

<link rel="shortcut icon" href="<?php echo $this->baseurl; ?>/images/favicon.ico" />

<?php
if($this->countModules("left")&&!$this->countModules("right")){ $contentwidth="left";}
if($this->countModules("right")&&!$this->countModules("left")){ $contentwidth="right";}
if($this->countModules("left")&&$this->countModules("right")) {$contentwidth="middle"; }
if($this->countModules("user1")&&!$this->countModules("user2")) {$topuserwidth="one";}
if($this->countModules("user1")&&$this->countModules("user2")) {$topuserwidth="two";}
if($this->countModules("user5")&&$this->countModules("user6")&&$this->countModules("user7")) {$userwidth="3";}
if($this->countModules("user5")&&$this->countModules("user6")&&!$this->countModules("user7")) {$userwidth="2";}
if($this->countModules("user5")&&!$this->countModules("user6")&&$this->countModules("user7")) {$userwidth="2";}
if($this->countModules("user6")&&!$this->countModules("user5")&&$this->countModules("user7")) {$userwidth="2";}
if($this->countModules("user5")&&!$this->countModules("user6")&&!$this->countModules("user7")) {$userwidth="1";}
if($this->countModules("user6")&&!$this->countModules("user5")&&!$this->countModules("user7")) {$userwidth="1";}
if($this->countModules("user7")&&!$this->countModules("user5")&&!$this->countModules("user6")) {$userwidth="1";}
?>

<!--[if lte IE 6]>
<style type="text/css">
#content {width: 99%;}
.user_bg {width: 99%;}
</style>
<![endif]-->

<!--
author: Juergen Koller
web: http://www.lernvid.com
copyright: Lernvid.com - Lernvideos Tutorials Templates PSD-Files und mehr...
-->