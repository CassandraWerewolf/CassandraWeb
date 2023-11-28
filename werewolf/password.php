<?php

include "php/accesscontrol.php";
include_once "php/common.php";

if ( isset($_POST['newPass']) ) {
if ( $_POST['password1'] != $_POST['password2'] ) {
  error("Passwords do not match, please try again.");
}
$sql = "Update Users set password = MD5('".$_POST['password1']."') where id = '".$_SESSION['uid']."'";
$result = mysqli_query($mysql, $sql);
$_SESSION['pwd'] = $_POST['password1'];
if ( isset($_COOKIE['cassy_pwd']) ) { $_COOKIE['cassy_pwd'] = $_POST['password1']; }
?>
<html>
<head>
<title>Password Changed</title>
</head>
<body>
Password Succesfully Changed.
Return to <a href='index.php'>Main Page</a>
</body>
</html>
<?php
} else {
?>
<html>
<head>
<title>Change Password</title>
</head>
<body>
<center>
<form method='post' action='<?=$_SERVER['PHP_SELF'];?>' >
Please Enter a New Password: <input type='password' name='password1' /><br />
Confirm Password: <input type='password' name='password2' /><br />
<input type='submit' name='newPass' value=' Change Password ' />
</form>
</center>
</body>
</html>
<?php
}
?>
