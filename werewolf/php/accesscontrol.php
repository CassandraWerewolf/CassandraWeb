<?php // accesscontrol.php

session_start();

include_once 'common.php';
include_once 'db.php';
include_once 'mobile_device_detect.php';

dbConnect();

$_SESSION['url'] = $_SERVER['REQUEST_URI'];

if (isset($_POST['login'])) {
  $uname = $_POST['uname'];
  $pwd = $_POST['pwd'];
  $sql = sprintf("Select id, password from Users where name=%s", quote_smart($uname));
  $result = mysqli_query($dbcnx, $sql);

  if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $uid = $user['id'];
    $stored_hash = $user['password'];

    // Check if the password is stored as MD5 (legacy) or using password_hash
    if (strlen($stored_hash) == 32) { // MD5 hash is 32 characters
      $authenticated = (md5($pwd) == $stored_hash);

      // If using old MD5 hash and authentication successful, update to new password_hash
      if ($authenticated) {
        $new_hash = password_hash($pwd, PASSWORD_DEFAULT);
        $update_sql = sprintf("UPDATE Users SET password=%s WHERE id=%s",
                             quote_smart($new_hash), quote_smart($uid));
        mysqli_query($dbcnx, $update_sql);
      }
    } else {
      // Using modern password hashing
      $authenticated = password_verify($pwd, $stored_hash);
    }*/

    if ($authenticated) {
      $options = [
        'expires' => time()+60*60*24*365,
        'path' => '/', 
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None' // None || Lax  || Strict
      ];
      if ($_POST['remember'] == "on") {
        setcookie('cassy_uid', $uid, $options);
        setcookie('cassy_pwd', $pwd, $options);
      } else {
        $options['expires'] = 0;
        setcookie('cassy_uid', $uid, $options);
        setcookie('cassy_pwd', $pwd, $options);
      }
      $_SESSION['uid'] = $uid;
      $_SESSION['pwd'] = $pwd;
    } else {
      $uid = null;
    }
  } else {
    $uid = null;
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

// If we have a UID but no authenticated session yet, verify credentials
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
  $sql = sprintf("SELECT * FROM Users WHERE id=%s", quote_smart($uid));
  $result = mysqli_query($dbcnx, $sql);

  if (!$result) {
    unset($_SESSION['uid']);
    unset($_SESSION['pwd']);
    if (isset($_COOKIE['cassy_uid'])) {
      setcookie('cassy_uid', "", time()-3600, '/', '', true, true);
    }
    if (isset($_COOKIE['cassy_pwd'])) {
      setcookie('cassy_pwd', "", time()-3600, '/', '', true, true);
    }
    error("Database error #300 occurred while checking your login details.\\nIf this error persists, please e-mail cassandra.project@gmail.com");
  }

  if (mysqli_num_rows($result) == 0) {
    unset($_SESSION['uid']);
    unset($_SESSION['pwd']);
    if (isset($_COOKIE['cassy_uid'])) {
      setcookie('cassy_uid', "", time()-3600, '/', '', true, true);
    }
    if (isset($_COOKIE['cassy_pwd'])) {
      setcookie('cassy_pwd', "", time()-3600, '/', '', true, true);
    }
    ?>
    <html>
    <head>
    <title>Access Denied</title>
    </head>
    <body>
    <h1>Access Denied</h1>
    <p>Your user ID or password is incorrect, or you are not registered user on this site. You can either <a href='<?=$_SERVER['PHP_SELF'];?>'>try again</a> or <a href='signup.php'>request a (new) password</a>.</p>
    </body>
    </html>
    <?php
    exit;
  }

  $user = mysqli_fetch_assoc($result);
  $stored_hash = $user['password'];

  // Verify password
  if (strlen($stored_hash) == 32) { // MD5 hash
    $authenticated = (md5($pwd) == $stored_hash);

    // Update to modern hash if login successful
    if ($authenticated) {
      $new_hash = password_hash($pwd, PASSWORD_DEFAULT);
      $update_sql = sprintf("UPDATE Users SET password=%s WHERE id=%s",
                           quote_smart($new_hash), quote_smart($uid));
      mysqli_query($dbcnx, $update_sql);
    }
  } else {
    $authenticated = password_verify($pwd, $stored_hash);
  }

  if (!$authenticated) {
    unset($_SESSION['uid']);
    unset($_SESSION['pwd']);
    if (isset($_COOKIE['cassy_uid'])) {
      setcookie('cassy_uid', "", time()-3600, '/', '', true, true);
    }
    if (isset($_COOKIE['cassy_pwd'])) {
      setcookie('cassy_pwd', "", time()-3600, '/', '', true, true);
    }
    ?>
    <html>
    <head>
    <title>Access Denied</title>
    </head>
    <body>
    <h1>Access Denied</h1>
    <p>Your user ID or password is incorrect, or you are not registered user on this site. You can either <a href='<?=$_SERVER['PHP_SELF'];?>'>try again</a> or <a href='signup.php'>request a (new) password</a>.</p>
    </body>
    </html>
    <?php
    exit;
  }

  $_SESSION['authenticated'] = true;
  $username = $user['name'];
  $level = $user['level'];
} else {
  $sql = sprintf("SELECT name, level FROM Users WHERE id=%s", quote_smart($uid));
  $result = mysqli_query($dbcnx, $sql);
  $user = mysqli_fetch_assoc($result);
  $username = $user['name'];
  $level = $user['level'];
}

if ($level == 0) {
?>
<html>
<head>
<script language='javascript'>
<!--
<?php
if ($_SERVER['PHP_SELF'] != 'logout.php') {
  print "alert('This user id has been blocked from the Cassandra System')\n\n";
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
exit;
}

function checkLevel($level, $num) {
  if ($level <= 0 || $level > $num) {
  ?>
  <html>
  <head>
  <title>Permission Denied</title>
  </head>
  <body>
  <p>You do not have high enough clearance level to access this page.</p>
  </body>
  </html>
  <?php
  exit;
  }
}

function upgrading() {
  global $level;
  if ($level <= 0 || $level > 1) {
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
