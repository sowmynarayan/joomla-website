<?php
session_start();
error_reporting(0);

if($s_count >= 0 && $s_status ==1 )
{
$s_count = 0;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<title>Level 0</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<script type="text/javascript" src="unitpngfix.js"></script>
<script type = "text/javascript">
function err()
{
	alert("We won't let you win that easily!!");
}
function redir()
{
	window.location="click_verify.php";
}
</script>
</head>
<body>


	<!-- all that matters doesn't start here --> 
	
		<!-- Play By My Rules -->
			
	<!-- all that matters doesn't end here --> 		


<div id="main_container">
	<div class="top_leafs"><img src="images/top_leafs.png" alt="" title=" "/></div>
	<div id="header">
    	<div class="logo">
       <a href="index.php">Prayana'10</a>
        </div>

        <div class="menu">
        	<ul>                            
        	<li>
        	<a>Welcome,<?php 
		if($s_status == 1)
		echo " ".$s_uname;
		?></a></li>
		<li>
		<a href="index.php" >home</a></li>
                <li><a href="http://tecuthsav.tce.edu/forum" target="_blank">forum</a></li>
                <li><a href="contact.php">contact us</a></li>
                <li><a href="logout.php">logout</a>
                </li>
              	</ul>
        </div>
        
    </div>
    
    
    <div id="center_content">
    		<div class="left_content">
            
                    <h1>Tic Tac Toe</h1>
                    <div style="margin-left:30%">
                    <img src="images/tictactoe.jpg" usemap="#tic" />
                    <map name="tic">
                    <area shape="rect" coords="292,17,417,125" onclick = "err()"/>
   		    <area shape="rect" coords="13,126,147,239"  onclick = "redir()" />
                    	  
                    </map>  
                    <br /><br /><br />
                                       
                   </div>
                    	
                   <div style="margin-left:55%">
                   <h1>Answer : </h1> 	   
                   <input type ="text" name ="tic">
              
                 
                    <br /><br />
                    <center><input type = "submit" value = "GO"></center>
 	</div>
                      	  <div class="photo_box">
                        	 
                               
                    </div>  
             		 <div class="photos_block"> 
                    
                    	  <div class="photo_box">
                        	 
            
            </div>
            
            
            </div>
            
            </div>
            
            
            <div class="bottom_content">
            	
               
                
                <div class="footer">
                
                &copy; Prayana '10. All Rights Reserved
		<br />
		 CSS designed by <a href="http://csstemplatesmarket.com"> CSStemplatesmarket.com </a>
                </div> 
            
            
            </div>
    
    
    
    </div>
    
    

    
</div>
<!-- end of main_container -->

</body>
</html>
<?php }
else
{
	header('Location : login.php');
}			
?>
