<?php
error_reporting(0);
session_start();
if($s_status == 1 && $s_count >=10)
{
	$s_count =11;
	header('Location: loggedin.php');
}
else
{
	header('Location: login.php');
}

?>

