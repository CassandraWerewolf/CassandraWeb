<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";
include_once "php/common.php";
include_once "menu.php";

$cache = init_cache();

dbConnect();

if ( isset($_POST['submit']) ) {
  if ( $_POST['action'] == "new" ) {
    $sql = sprintf("insert into Games (id, start_date, title, status, description, aprox_length) values(NULL, %s, %s, 'Scheduled', %s, %s)",quote_smart($_POST['start_date']),quote_smart($_POST['title']),quote_smart($_POST['description']),quote_smart($_POST['aprox_length']));
    $result = mysql_query($sql);
    $game_id = mysql_insert_id();
    $sql = sprintf("insert into Moderators (user_id, game_id) values (%s, %s)",quote_smart($_SESSION['uid']),quote_smart($game_id));
    $result = mysql_query($sql);
  } elseif ( $_POST['action'] == "old" ) {
    $sql = sprintf("update Games set start_date=%s, title=%s, description=%s, aprox_length=%s where id=%s",quote_smart($_POST['start_date']),quote_smart($_POST['title']),quote_smart($_POST['description']),quote_smart($_POST['aprox_length']),quote_smart($_POST['game_id']));
    $result = mysql_query($sql);
  }
?>
<html>
<head>
<script language='javascript'>
<!--
window.location.href='/mystuff.php'
//-->
</script>
</head>
<body>
If page does not re-direct <a href='/mystuff.php'>click here</a>.
</body>
</html>
<?php
exit;
}
if ( isset($_REQUEST['game_id']) ) {
  $pgTitle = "Edit Scheduled Game";
  $hidden = "<input type='hidden' name='action' value='old' />\n";
  $hidden .= "<input type='hidden' name='game_id' value='".$_REQUEST['game_id']."' />\n";
  $format = "%Y-%m-%d";
  $sql = sprintf("select title, date_format(start_date,'%s') as start_date, aprox_length, description from Games where id=%s",$format,quote_smart($_REQUEST['game_id']));
  #print "$sql <br />";
  $result = mysql_query($sql);
  $game = mysql_fetch_array($result);
} else {
  $pgTitle = "Add a New Game - Scheduled Mode";
  $hidden = "<input type='hidden' name='action' value='new' />\n";
  $game['title'] = "";
  $game['start_date'] = "yyyy-mm-dd";
  $game['aprox_length'] = "14";
  $game['description'] = "";
}
?>
<html>
<head>
<title><?=$pgTitle;?></title>
<link rel='stylesheet' type='text/css' href='<?=$here;?>bgg.css'>
<script src='validation.js'></script>
<script language='javascript'>
<!--
function validate_form() {
myform = document.create_new
g_name = myform.title.value
if ( g_name == "" ) {
  alert("You need to have a game name - even if it is just temporary. You can change it later.")
  return false
}
s_date = myform.start_date.value
if ( s_date == "" || s_date == "yyyy-mm-dd" || s_date == "0000-00-00" ) {
  alert("You need to have a Start Date")
  return false
}

if ( ! isDate(s_date,'yyyy-MM-dd') ) {
  alert("The start date is not in the proper format it must be yyyy-mm-dd")
  return false
}
return true
}
//-->
</script>
</head>
<body>
<?php display_menu(); ?>
<center>
<h1><?=$pgTitle;?></h1>
<p>Use this form to add/edit a new game in scheduled mode.  This just means you <br />
claim the spot on the calendar.  You don't have to have a BGG thread, or a rule<br />
set yet.  This is for Moderators only, you can not post someone elses game.</p>
<table border='0' class='thin_table'>
<form name='create_new' method='post' action='<?=$_SERVER['PHP_SELF'];?>' onSubmit='return validate_form()'>
<?=$hidden;?>
<tr><td align='right'>Moderator:</td><td><?=$username;?></td></tr>
<tr><td align='right'>Game Name:</td><td><input type='text' name='title' value='<?=$game['title'];?>'/></td></tr>
<tr><td align='right'>Start Date:</td><td><input type='text' name='start_date' value='<?=$game['start_date'];?>'/></td></tr>
<tr><td align='right'>Aprox. Length:</td><td><select name='aprox_length'>
<?php
$selected7 = "";
$selected14 = "";
$selected21 = "";
if ( $game['aprox_length'] == "7" ) { $selected7 = "selected"; }
if ( $game['aprox_length'] == "14" ) { $selected14 = "selected"; }
if ( $game['aprox_length'] == "21" ) { $selected21 = "selected"; }
?>
<option <?=$selected7;?> value='7' />Short (7days)<option <?=$selected14;?> value='14' />Medium (14days)<option <?=$selected21;?> value='21' />Long (21days)</select></td></tr>
<tr><td align='right'>Description:<br />(This is a brief<br />description,<br /> not a full listingi<br /> of the rules.)</td><td><textarea name='description' rows='4' cols='30'><?=$game['description'];?></textarea></td></tr>
<tr><td align='center' colspan='2'><input type='submit' value='submit' name='submit' /></td></tr>
</form>
</table>
<iframe src="http://www.google.com/calendar/embed?src=cassandra.project%40gmail.com&amp;title=BGG%20Werewolf%20Calendar&amp;epr=9&amp;height=614" style=" border-width:0 " width="640" frameborder="0" height="614"></iframe>
</center>
</body>
</html>
