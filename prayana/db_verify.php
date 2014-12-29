<?php
session_start();
error_reporting(0);

if ( $s_status == 1)
{
include 'config.php';
include 'magic_quote.php';

$query= "select * from answer where qid='$s_count'";
$res = mysql_query($query);
$ans = mysql_fetch_array($res);
$p = cleanQuery($ans['answer']);		// actual answer fetched from mysql
$n = cleanQuery($ans['near']);			// Nearest answer
$desc = cleanQuery($ans['desc']);		// Nearest Description
$a = cleanQuery($_POST["answer_1"]);		// user entered answer




	if (strcmp($a,$p) == 0 && $s_status==1 )
	{
			$s_count = $s_count+1;
			$today = date("F j, Y, g:i a");		// retrieving date od last answer
			// changing count
			$query1= "update users set count = '$s_count' where username = '$s_uname' AND '$s_count' > count";
			$res1 = mysql_query($query1);
			// date when he finishes a particular level
			$query2= "update users set finish_date = '$today' where username = '$s_uname' AND '$s_count' >= count";
			$res2 = mysql_query($query2);
			// date when he first finished level 1
			if($s_count == 2 )
			{
				$query2= "update users set start_date = '$today' where username = '$s_uname'";
				$res2 = mysql_query($query2);
			}
			mysql_close();
			header('Location: loggedin.php'); 
	}
	elseif (strcmp($a,$n) == 0 && $s_status==1 )
	{
		
		$s_desc = $desc;
		mysql_close();
		$s_nearcheck = 1;
		header('Location: near.php'); 
	}
	else
	{
		mysql_close();
		header('Location: loggedin.php'); 
	}
}	
else
{
	header('Location: login.php');
}
?>
