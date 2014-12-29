<?php
	
	
	session_start();
	error_reporting(0);	
	include 'config.php';
	include 'magic_quote.php';
	
	// Getting data from HTML page
	$uname = cleanQuery($_POST['username']);
	$passwd_user = cleanQuery($_POST['password']);
	
	$query = "select * from users where username = '$uname'" ;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	$passwd_db = $row['password'];
	if($passwd_user == $passwd_db && $passwd_user != "" )
	{
		//Setting authentication variable
		$s_auth=1;
		session_register("s_uname");
		session_register("s_count");
		session_register("s_status");
		session_register("s_desc");
		session_register("s_nearcheck");
		$s_uname = $row['username'];
		$s_count = $row['count'];
		$s_status=1;
		
		mysql_close();	
		
		header('Location: loggedin.php'); 
	}
	else
	{
		header('Location: login.php');
	}
?>
