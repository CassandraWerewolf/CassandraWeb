<?php // accesscontrol.php

session_start();

include_once 'common.php';
include_once 'db.php';
include_once 'mobile_device_detect.php';

$mysql = dbConnect();

$_SESSION['url'] = $_SERVER['REQUEST_URI'];

if ( isset($_POST['login']) ) {
  $uname = $_POST['uname'];
  $pwd = $_POST['pwd'];
  $sql = sprintf("Select id from Users where name=%s",quote_smart($uname));
  $result = mysqli_query($mysql, $sql);
  $uid = mysqli_result($result,0,0);
  if ( $_POST['remember'] == "on" ) {
    setcookie('cassy_uid', $uid, time()+60*60*24*365, 'samesite=none', '', true, true);
    setcookie('cassy_pwd', $pwd, time()+60*60*24*365, 'samesite=none', '', true, true);
  } else {
    setcookie('cassy_uid', $uid, 0, 'samesite=none', '', true, true);
    setcookie('cassy_pwd', $pwd, 0, 'samesite=none', '', true, true);
  }
} else {
  $uid = (isset($_SESSION['uid']) ? $_SESSION['uid'] : (isset($_COOKIE['cassy_uid']) ? $_COOKIE['cassy_uid'] : null));
  $pwd = (isset($_SESSION['pwd']) ? $_SESSION['pwd'] : (isset($_COOKIE['cassy_pwd']) ? $_COOKIE['cassy_pwd'] : null));
}

if (!isset($uid)) {
?>
<html>
<head>
<title>Cassandra Werewolf Login Page</title>
</head>
<body>
<center>
<h1>Login Required</h1>
Cookies must be enabled for the login to work.
<form name='login_cassy' method='post' action='<?=$_SERVER['PHP_SELF'];?>'>
<table border='0'>
<tr><td>User Name:</td><td><input type='text' name="uname" /></td></tr>
<tr><td>Password:</td><td><input type='password' name='pwd' /></td></tr>
<tr><td colspan='2'><input type='checkbox' name='remember'>Permanent Login (less secure)</td></tr>
<tr><td colspan='2' align='center'><input type='submit' name='login' value='Log In' /></td></tr>
</table>
<p>You must log in to access this area of the site.<br />  If you do not have a password or have forgotten your<br /> password please <a href='/signup.php'>request a password</a> for instant access.</p>
</form>
</center>
</body>
</html>
<?php
exit;
}

$_SESSION['uid'] = $uid;
$_SESSION['pwd'] = $pwd;

$sql = sprintf("select * from Users where id=%s and password = MD5(%s)",quote_smart($uid),quote_smart($pwd));
$result = mysqli_query($mysql, $sql);
if ( !$result) {
  unset ($_SESSION['uid']);
  unset ($_SESSION['pwd']);
  if ( isset($_COOKIE['cassy_uid']) ) {
    setcookie ('cassy_uid',"",time()-3600,'/','',true, true);
  }
  if ( isset($_COOKIE['cassy_pwd']) ) {
    setcookie ('cassy_pwd',"",time()-3600,'/','',true, true);
  }
  error ("Database error #300 occured while checking your login details.\\nIf this error persists, please e-mail cassandra.project@gmail.com");
}
if ( mysqli_num_rows($result) == 0 ) {
  unset ($_SESSION['uid']);
  unset ($_SESSION['pwd']);
  if ( isset($_COOKIE['cassy_uid']) ) {
    setcookie ('cassy_uid',"",time()-3600,'/','',true, true);
  }
  if ( isset($_COOKIE['cassy_pwd']) ) {
    setcookie ('cassy_pwd',"",time()-3600,'/','',true, true);
  }
?>
<html>
<head>
<title>Access Denied</title>
</head>
<body>
<h1>Access Denied</h1>
<p>Your user ID or password is incorrect, or you are not registered user on this site.  You can either <a href='<?=$_SERVER['PHP_SELF'];?>'>try again</a> or <a href='signup.php'>request a (new) password</a>.</p>
</body>
</html>
<?php
exit;
}
$username = mysqli_result($result,0,'name');
$level = mysqli_result($result,0,'level');

if ( $level == 0 ) {
?>

<html>
<head>
<script language='javascript'>
<!--
<?php
if ( $_SERVER['PHP_SELF'] != 'logout.php') {
print " alert('This user id has been blocked from the Cassandra System')\n\n";
}
?>
location.href='/logout.php'
//-->
</script>
</head>
<body>
<p>Return to <a href='/logout.php'>log out</a></p>
</body>
</html>
<?php
}

function checkLevel($level,$num) {
if ( $level <= 0 || $level > $num ) {
?>
<html>
<head>
<title>Pemission Denied</title>
</head>
<body>
<p>You do not have high enough clearence level to access this page.</p>
</body>
</html>
<?php
exit;
}
}

function upgrading() {
global $level;
if ( $level <= 0 || $level > 1 ) {
?>
<html>
<head>
<title>Page being Upgraded</title>
</head>
<body>
<p>This page is being upgraded, please be patient and try again later.</p>
</body>
</html>
<?php
exit;
}
}
?>
