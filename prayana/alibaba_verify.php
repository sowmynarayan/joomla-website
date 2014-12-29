<?php

error_reporting(0);
session_start();
if($s_status == 1)
{
	include 'config.php';
	include 'magic_quote.php';

	$query= "select password from users where username='$s_uname'";
	$res = mysql_query($query);
	$ans = mysql_fetch_array($res);
	$p = cleanQuery($ans['password']);		// actual answer fetched from mysql
	$a = cleanQuery($_POST["answer_1"]);		// user entered answer
	
	if (strcmp($a,$p) == 0 && $s_status==1 )
		$s_count=8;
	header('Location: loggedin.php');	
	
}
else
{
	header('Location: login.php');
}

?>
