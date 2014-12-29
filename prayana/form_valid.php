
<! -- Same as your index.php. No changes made except in line mentioned->

<html>
<head>
	<title> Client Side Form Validation Using Javascript </title>
	<script type="text/javascript" >

	function validate_form()
	{	
		var i;
		var valid=true;
		var j = document.forms[0].length;
		for(i=0;i<j-1;i++)
		{
			if(document.forms[0].elements[i].value=="")
			{
			alert("Fill in all details")
				valid=false;
			exit();
			}
		}
	}
	
	</script>

</head>
<body>
	<form action="register.php" onsubmit=" validate_form()" method="post">  <! -- changed insert.php to register.php ->
	Name :
	<input type="text" name="name">
	<br /><br />
	Username :
	<input type="text" name="username">
	<br /><br />
	Password :
	<input type="password" name="password">
	<br /><br />
	Phone no :
	<input type="text" name="phone">
	<br /><br />
	E-Mail Id :
	<input type="text" name="email">
	<br /><br />
	College :
	<input type="text" name="college">
	<br /><br />
	<input type="submit" name="sub" value="Register">
	</form>


</body>
</html>
