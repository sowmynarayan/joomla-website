<?php
session_start();
error_reporting(0);
if($s_status != 1)
{
	session_unregister($s_auth);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<title>Prayana'10</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<!--<script type="text/javascript" src="unitpngfix.js"></script>-->
</head>
<body>

<div id="main_container">
	<div class="top_leafs"><img src="images/top_leafs.png" alt="" title=" "/></div>
	<div id="header">
    	<div class="logo">
       <a href="index.html">Prayana'10</a>
        </div>

        <div class="menu">
        	<ul>                            
       		<li class="selected"><a href="index.php">home</a></li>
		<li><a href="http://tecuthsav.tce.edu/forum" target="_blank">forum</a></li>
                <li><a href="#">contact us</a></li>
		<?php
		if($s_status == 0)
		{?>
		<li><a href="login.php">login</a></li>
		<?php
		}
		else
		{?>	
			<li><a href="logout.php">logout</a></li>
		<p>Welcome,</p><?php } 
		if($s_status == 1)
		echo $s_uname;
		?>	 
        	</ul>
        </div>
        
    </div>
    
    
    <div id="center_content">
    		<div class="left_content">
            
                    <h1>Contact Us</h1>
                    <h2> Developers : </h2>
			<ul>
			<li>Sai Santhosh, V.A (saisanthoshdav@gmail.com) </li>
			<li>Sarvesh Ghautham, M.K (sarvesh.kaladhar@gmail.com)</li>
			<li>Vishnu Gowthem, T (vichugoutam@gmail.com)</li>
			<li>Sowmy Narayan, S (sowmynarayan@yahoo.in)</a></li>
			</ul>
                
                 
                     <div class="photos_block"> 
                    
                    	  <div class="photo_box">
                        	 
                               
                    </div>  
            
            
            </div>
            
            
           
            
            </div>
            
            
            <div class="bottom_content">
            	
           
                
                <div class="footer">
                
                &copy; Prayana 2010. All Rights Reserved
		<br />
		 CSS designed by <a href="http://csstemplatesmarket.com"> CSStemplatesmarket.com </a>
                </div> 
            
            
            </div>
    
    
    
    </div>
    
    

    
</div>
<!-- end of main_container -->

</body>
</html>
