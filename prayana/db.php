<?php
$username = "root";
$password = "sample";
$database = "wth";

$name=$_POST['name'];
$phone=$_POST['phone'];
$email=$_POST['email'];
$college=$_POST['college'];

mysql_connect($localhost,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

$query="insert into users values ('$name','$phone','$email','$college')";
mysql_query($query);

mysql_close();
?>
