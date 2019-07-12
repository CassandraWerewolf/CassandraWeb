<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";
include_once "php/common.php";

$cache = init_cache();

dbConnect();

$game_id = $_REQUEST['game_id'];

# Make sure player accessing this page for this game is the moderator

if ( ! is_moderator($uid,$game_id) ) {
  error("You are not the moderator of this game so you can not move it to signup mode.");
}

if ( isset($_POST['submit']) ) {
  # Post a Cassandra Player List to Thread.
  $body = "This post is where the player list will be updated as players sign up using Cassandra.  /game/".$_POST['thread_id'];
  $player_list_id = reply_thread($_POST['thread_id'],$body);
  if ( $player_list_id == "" ) { error("Please check your thread_id"); }
  $lynch_db = $_POST['lynch'].":00:00";
  $night_db = $_POST['night'].":00:00";
  $sql = sprintf("replace into Games (id, start_date, title, status, thread_id, description, aprox_length, max_players, player_list_id, complex, lynch_time, na_deadline) values(%s, %s, %s, 'Sign-up', %s, %s, %s, %s, %s, %s, %s, %s)",quote_smart($game_id),quote_smart($_POST['start_date']),quote_smart($_POST['title']),quote_smart($_POST['thread_id']),quote_smart($_POST['description']),quote_smart($_POST['aprox_length']),quote_smart($_POST['max_players']),$player_list_id,quote_smart($_POST['complex']),quote_smart($lynch_db),quote_smart($night_db));
  $result = mysql_query($sql);
    $sql = sprintf("insert into Moderators (user_id, game_id) values (%s, %s)",quote_smart($uid),quote_smart($game_id));
	  $result = mysql_query($sql);

  // clean the cache since this action will change the front page
  $cache->remove('games-signup-list', 'front');
?>
<html>
<head>
<script language='javascript'>
<!--
window.location.href='/game/<?=$_POST['thread_id'];?>'
//-->
</script>
</head>
<body>
If page does not re-direct <a href='/game/<?=$_POST['thread_id'];?>'>click here</a>.
</body>
</html>
<?php
exit;
}
$format = "%Y-%m-%d";
$sql = sprintf("select title, date_format(start_date,'%s') as start_date, aprox_length, description from Games where id=%s",$format,quote_smart($game_id));
$result = mysql_query($sql);
$game = mysql_fetch_array($result);
?>
<html>
<head>
<title>Move Scheduled Game to Sign-up Mode</title>
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
t_id = myform.thread_id.value
if ( t_id == "" ) {
  alert("You must have a BGG thread ID")
  return false
}
if ( ! isNumber(t_id) ) {
  alert("A BGG thread ID is all numbers.")
  return false
}
s_date = myform.start_date.value
if ( s_date == "" || s_date == "yyyy-mm-dd" || s_date == "0000-00-00") {
  alert("You need to have a Start Date")
  return false
}
if ( document.getElementById('lynch').value == document.getElementById('night').value ) {
  alert("Lynch time and Night Action Deadline can not be the same time")
  return false
}

m_players = myform.max_players.value
alert(m_players)
if ( ! isNumber(m_players) || m_players == "" ) {
  alert ("You need to specify a maximum number of players.  If you don't have a max number allowed just use a very large number (like 100)")
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
<center>
<h1>Move a Scheduled game to Sign-up Mode</h1>
<p>Use this form to move a scheduled game to signup mode.  <br />You must already have a BGG thread set up for sign-ups, with your rules posted. <br />This is for Moderators only, you can not post someone elses game.</p>
<table border='0' class='thin_table'>
<form name='create_new' method='post' action='<?=$_SERVER['PHP_SELF'];?>' onSubmit='return validate_form()'>
<input type='hidden' name='game_id' value='<?=$game_id;?>' />
<tr><td align='right'>Moderator:</td><td><?=$username;?></td></tr>
<tr><td align='right'>Game Name:</td><td><input type='text' name='title' value='<?=$game['title'];?>' /></td></tr>
<tr><td align='right'>BGG Thread ID:</td><td><input type='text' name='thread_id' /></td></tr>
<tr><td align='right'>Start Date:</td><td><input type='text' name='start_date' value='<?=$game['start_date'];?>'/></td></tr>
<tr><td align='right'>Lynch Time:</td><td><?php print time_dropdown_old('lynch'); ?></td></tr>
<tr><td align='right'>Night Action Deadline:</td><td><?php print time_dropdown_old('night'); ?></td></tr>
<tr><td align='right'>Aprox. Length:</td><td><select name='aprox_length'>
<?php
$selected7 = "";
$selected14 = "";
$selected21 = "";
if ( $game['aprox_length'] == '7' ) { $selected7 = 'selected'; }
if ( $game['aprox_length'] == '14' ) { $selected14 = 'selected'; }
if ( $game['aprox_length'] == '21' ) { $selected21 = 'selected'; }
?>
<option <?=$selected7;?> value='7' />Short (7days)<option <?=$selected14;?> value='14' />Medium (14days)<option <?=$selected21;?> value='21' />Long (21days)</select></td></tr>
<tr><td align='right'>Max Players:</td><td><input type='text' name='max_players' value='' /></td></tr>
<tr><td align='right'>Complexity:</td><td><select name='complex'><option value='Newbie' />Newbie<option value='Low' />Low<option selected value='Medium' />Medium<option value='High' />High<option value='Extreme' />Extreme</select></td></tr>
<tr><td align='right'>Description:<br />(This is a brief<br />description,<br /> not a full listingi<br /> of the rules.)</td><td><textarea name='description' rows='4' cols='30'><?=$game['description'];?></textarea></td></tr>
<tr><td align='center' colspan='2'><input type='submit' value='submit' name='submit' /></td></tr>
</form>
</table>
</center>
</body>
</html>
