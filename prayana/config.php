<?php
//error_reporting(1);
// DB Connectivity
        $localhost = "mysql-cossacks.x10hosting.com";
	$username = "sowmynar_wth";
	$password = "prayana";
	$database = "sowmynar_wth";

	mysql_connect($localhost,$username,$password);
	@mysql_select_db($database) or die( "Unable to select database");
	// End of DB Connectivity
?>
