<?php

include "php/accesscontrol.php";
include_once "php/common.php";

if (isset($_POST['newPass'])) {
  if ($_POST['password1'] != $_POST['password2']) {
    error("Passwords do not match, please try again.");
  }

  // Use password_hash instead of MD5 for better security
  $hashed_password = password_hash($_POST['password1'], PASSWORD_DEFAULT);
  $sql = sprintf("UPDATE Users SET password = %s WHERE id = %s",
                quote_smart($hashed_password), quote_smart($_SESSION['uid']));

  $result = mysqli_query($dbcnx, $sql);
  $_SESSION['pwd'] = $_POST['password1'];

  // Set authenticated flag to true
  $_SESSION['authenticated'] = true;

  if (isset($_COOKIE['cassy_pwd'])) {
    setcookie('cassy_pwd', $_POST['password1'], time()+60*60*24*365, '/; samesite=none', '', true, true);
  }
?>
<html>
<head>
<title>Password Changed</title>
</head>
<body>
Password Successfully Changed.
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
