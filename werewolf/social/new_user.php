<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../menu.php";

dbConnect();

$sql = sprintf("select * from Social_sites where site_name=%s",quote_smart($_REQUEST['site_name']));
$result = mysql_query($sql);
$site = mysql_fetch_array($result);

if ( $_REQUEST['mode'] == "delete" ) {
  $sql = sprintf("delete from Social_users where site_id=%s and user_id=%s",quote_smart($site['id']),quote_smart($uid));
  $result = mysql_query($sql);
?> 
<html>
<head>
<script language='javascript'>
<!--
location.href='/social/'
//-->
</script>
</head>
<body>
<a href="/social">Go To Social Page</a>
</body>
</html>
<?php
}

if ( isset($_POST['submit']) ) {
  if ( $_POST['user_info'] == "" ) { error("Your info can't be blank."); }
  if ( $_POST['mode'] == "add" ) {
    $sql = sprintf("insert into Social_users (site_id, user_id, user_info)values ( %s, %s, %s)",quote_smart($site['id']),quote_smart($uid),quote_smart($_POST['user_info']));
  } elseif ($_POST['mode'] == "edit")  {
    $sql = sprintf("update Social_users set user_info=%s where site_id=%s and user_id=%s",quote_smart($_POST['user_info']),quote_smart($site['id']),quote_smart($uid));
  }
  $result = mysql_query($sql);
?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/social/'
//-->
</script>
</head>
<body>
<a href="/social">Go To Social Page</a>
</body>
</html>
<?php
} else {
?>
<html>
<head>
<title>Add Your Information for <?=$site['site_name']?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php print display_menu(); ?>
<div style='padding:10px;'>
<h1>Add Your Information for 
<?php
$a_start = "";
$a_end = "";
if ( $site['url'] != "" ) {
  $a_start = "<a href='".$site['url']."'>";
  $a_end = "</a>";
}
print $a_start.$site['site_name'].$a_end;
?>
</h1>
<form method='POST' action='<?=$_SERVER['PHP_SELF'];?>'>
<table class='forum_table'>
<?php
$userinfo = "";
if ( $_REQUEST['mode'] == "edit" ) {
  $sql = sprintf("select * from Social_users where site_id=%s and user_id=%s",quote_smart($site['id']),quote_smart($uid));
  $result = mysql_query($sql);
  $user = mysql_fetch_array($result);
  $userinfo = $user['user_info'];
}
?>
<tr><td><b>User Information</b></td><td><input type='text' name='user_info' value='<?=$userinfo?>' /></td></tr>
<input type='hidden' name='site_name' value='<?=$site['site_name']?>' />
<input type='hidden' name='mode' value='<?=$_REQUEST['mode']?>' />
<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>
</table>
</form>
</div>
</body>
</html>
<?php
exit;
}
?>
