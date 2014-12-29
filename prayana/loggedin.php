<?php
session_start();
error_reporting(0);

switch($s_count)		
{	
	case 0:
		//echo "jj";
		header('Location: click.php');
		break;
	case 1:
		header('Location: start.php');
		break;
	case 2:
		header('Location: worldsend.php');
		break;
	case 3:
		header('Location: numbers.php');
		break;
	case 4:
		header('Location: english.php');
		break;
	case 5:
		header('Location: php.php');
		break;
	case 6:
		header('Location: physics.php');
		break;
	case 7:
		header('Location: alibaba.php');
		break;
	case 8:
		header('Location: volks.php');
		break;
	case 9:
		header('Location: codmw.php');
		break;
	case 10:
		header('Location: levelix.php');
		break;
	case 11:
		header('Location: telepathy.php');
		break;
	case 14:
		header('Location: binary.php');
		break;
	case 15:
		header('Location: whatsit.php');
		break;


		

} 

?>
