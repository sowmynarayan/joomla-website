<?php
error_reporting(0);
session_start();
if($s_status == 1)
{
	$s_count =1;
	header('Location: start.php');
}
else
{
	header('Location: login.php');
}

?>

