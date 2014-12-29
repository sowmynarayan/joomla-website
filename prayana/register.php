<?php

// Parameters to open DB

$username = "root";
$password = "sample";
$database = "contest";

// Details got from HTML page

$name=$_POST['name'];
$phone=$_POST['phone'];
$email=$_POST['email'];
$college=$_POST['college'];

//Connecting to DB

mysql_connect($localhost,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

//Inserting info abt user

$query="insert into users values ('$name','$phone','$email','$college',0)";  // 0 is added for count value.. So add extra column in user table
mysql_query($query);

mysql_close();
?>
