<?php

include_once "../php/accesscontrol.php";
checkLevel($level,1);
include_once "../php/db.php";
include_once "../php/bgg.php";
include_once "../menu.php";


// Typed password, or the remembered one if the field was left blank (POST only).
$bgg_password = bgg_password_from_post();

dbConnect();

if ( isset($_REQUEST['submit']) ) {
  $sql = sprintf("select thread_id from Games where automod_id is not null and status != 'Finished'");
  $result = mysql_query($sql);
  while ( $thread_id = mysql_fetch_array($result) ) {
    $message = $_REQUEST['message'];
    print "<!--";
    $bgg_user = BGG::auth($username, $bgg_password);
    $bgg_user->reply_thread_quick($thread_id['thread_id'],$message);
    print "-->";
  }
?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/'
//-->
</script>
</head>
<body>
<?=bgg_html($message);?><br />
Return to <a href='/'>Front Page</a>
</body>
</html>
<?php
exit;
}
?>
<html>
<head>
<title>Post Message to all Auto-mod Games</title>
<link rel='stylesheet' type='text/css' href='../assets/css/application.css'>
</head>
<body>
<?php display_menu();?>
<div style='padding-left:10px;'>
<h1>Post Message to All Auto-mod Games</h1>
<form method='post' action='<?=$_SERVER['PHP_SELF']?>'>
BGG Password: <input type='password' name='bggpwd' value=''<?=bgg_password_placeholder();?> />
<br />
<input type='checkbox' name='remember' />Remember BGG password.
<br />
<textarea name='message' rows='10' cols='50'></textarea>
<br />
<input type='submit' name='submit' value='Submit'/>
</form>
</div>
</body>
</html>

