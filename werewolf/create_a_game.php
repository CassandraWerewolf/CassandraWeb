<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";
include_once "php/common.php";

$cache = init_cache();

dbConnect();

$today = date('Y-m-d');
$deadline_speed = array('Standard'=>'Standard', 'Fast'=>'Fast');

if ( isset($_POST['submit']) ) {
  # Post a Cassandra Player List to Thread.
  $body = "This post is where the player list will be updated as players sign up using Cassandra.  http://cassandrawerewolf.com/game/".$_POST['thread_id'];
  $player_list_id = $bgg_cassy->reply_thread($_POST['thread_id'],$body);
  if ( $player_list_id == "" ) { error("Please check your thread_id"); }
  if ( $_POST['swf'] == 'on' ) {
    $swf = "Yes";
    $start_date = $today;
  } else {
    $swf = "No";
    $start_date = $_POST['start_date']." ".$_POST['s_time'];
  }
  if ( $_POST['deadline_speed'] == "Standard" ) {
    $lynch_db = $_POST['lynch'].":00";
    $night_db = $_POST['night'].":00";
    $day_length = "NULL";
    $night_length = "NULL";
  } else {
    $lynch_db = "NULL";
    $night_db = "NULL";
    $day_length = $_POST['day_length'].":00";
    $night_length = $_POST['night_length'].":00";
  }
  $_POST['title'] = safe_html($_POST['title']);
  $_POST['description'] = safe_html($_POST['description']);
  $sql = sprintf("insert into Games (id, start_date, title, status, thread_id, description, swf, aprox_length, max_players, player_list_id, complex, deadline_speed, lynch_time, na_deadline, day_length, night_length) values(NULL, %s, %s, 'Sign-up', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",quote_smart($start_date),quote_smart($_POST['title']),quote_smart($_POST['thread_id']),quote_smart($_POST['description']),quote_smart($swf),quote_smart($_POST['aprox_length']),quote_smart($_POST['max_players']),$player_list_id,quote_smart($_POST['complex']),quote_smart($_POST['deadline_speed']),quote_smart($lynch_db),quote_smart($night_db),quote_smart($day_length),quote_smart($night_length));
  $result = mysql_query($sql);
  $game_id = mysql_insert_id();
  $sql = sprintf("insert into Moderators (user_id, game_id) values (%s, %s)",quote_smart($_SESSION['uid']),quote_smart($game_id));
  $result = mysql_query($sql);

  // clean the cache since this action will change the front page
  $cache->remove('games-signup-fast-list', 'front');
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
?>
<html>
<head>
<title>Add a New Game - Sign-up Mode</title>
<link rel='stylesheet' type='text/css' href='<?=$here;?>assets/css/application.css'>
<script src='/assets/js/validation.js'></script>
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
if ( s_date == "" || s_date == "yyyy-mm-dd" ) {
  alert("You need to have a Start Date")
  return false
}
if ( document.getElementById('lynch').value == "" ) {
  alert("Please fill in a Lynch time.")
  return false
}
if ( document.getElementById('night').value == "" ) {
  alert("Please fill in a Night Action deadline.")
  return false
}
if ( document.getElementById('lynch').value == document.getElementById('night').value ) {
  alert("Lynch time and Night Action Deadline can not be the same time")
  return false
}

m_players = myform.max_players.value
if ( ! isNumber(m_players) ) {
  alert ("You need to specify a maximum number of players.  If you don't have a max number allowed just use a very large number (like 100)")
  return false
}
if ( ! isDate(s_date,'yyyy-MM-dd') ) {
  alert("The start date is not in the proper format it must be yyyy-mm-dd")
  return false
}
return true
}

function show_dates() {
  full = document.getElementById('swf').checked
  if ( full ) {
    document.getElementById('start').className="hideDiv"
  } else {
    document.getElementById('start').className="showDiv"
  }
}

function show_deadlines() {
  fast = document.getElementById('deadline_speed').value
  if ( fast == "Fast" ) {
    document.getElementById('deadlines').className="hideDiv"
    document.getElementById('cycle_lengths').className="showDiv"
    document.getElementById('start_time').className="showDiv"
  } else {
    document.getElementById('deadlines').className="showDiv"
    document.getElementById('cycle_lengths').className="hideDiv"
    document.getElementById('start_time').className="hideDiv"
  }
}
//-->
</script>
</head>
<body>
<center>
<h1>Add a New Game - Sign-up Mode</h1>
<p>Use this form to add a new game in signup mode.  <br />You must already have a BGG thread set up for sign-ups, with your rules posted. <br />This is for Moderators only, you can not post someone elses game.</p>
<table border='0' class='thin_table'>
<form name='create_new' method='post' action='<?=$_SERVER['PHP_SELF'];?>' onSubmit='return validate_form()'>
<tr><td align='right'>Moderator:</td><td><?=$username;?></td></tr>
<tr><td align='right'>Game Name:</td><td><input type='text' name='title' /></td></tr>
<tr><td align='right'>BGG Thread ID:</td><td><input type='text' name='thread_id' /></td></tr>
<tr><td align='right'>Speed</td>
  <td><?php print create_dropdown('deadline_speed','Standard',$deadline_speed,"onChange='show_deadlines()'"); ?></td></tr>
<tr><td align='right'>Start Date:</td>
    <td><input type='checkbox' id='swf' name='swf' onClick='show_dates()' />As soon as game is full<br />
    <div id='start' class='showDiv'>
	<input type='text' name='start_date' value='<?=$today;?>'/>
      <div id='start_time' class='hideDiv'>
        <?php print time_dropdown('s_time'); ?> 
      </div>
    </div>
</td></tr>
<tr><td align='right'>Deadlines</td>
  <td>
  <div id='deadlines' class='showDiv'>
  <table cellspacing='0' cellpadding='0'>
  <tr><td>Lynch Time:</td><td><?php print time_dropdown('lynch','16:00'); ?></td></tr>
  <tr><td>Night Action Deadline:</td><td><?php print time_dropdown('night','17:00'); ?></td></tr>
  </table>
  </div>
  <div id='cycle_lengths' class='hideDiv'>
  <table cellspacing='0' cellpadding='0'>
  <tr><td>Day Length:</td><td><?php print time_dropdown('day_length','01:00',true); ?></td></tr>
  <tr><td>Night Length:</td><td><?php print time_dropdown('night_length','0:30',true); ?></td></tr>
  </table>
  </div>
  </td>
</tr>
<tr><td align='right'>Aprox. Length:</td><td><select name='aprox_length'><option value='7' />Short (7days)<option selected value='14' />Medium (14days)<option value='21' />Long (21days)</select></td></tr>
<tr><td align='right'>Max Players:</td><td><input type='text' name='max_players' value='' /></td></tr>
<tr><td align='right'>Complexity:</td><td><select name='complex'><option value='Newbie' />Newbie<option value='Low' />Low<option selected value='Medium' />Medium<option value='High' />High<option value='Extreme' />Extreme</select></td></tr>
<tr><td align='right'>Description:<br />(This is a brief<br />description,<br /> not a full listingi<br /> of the rules.)</td><td><textarea name='description' rows='4' cols='30'></textarea></td></tr>
<tr><td align='center' colspan='2'><input type='submit' value='submit' name='submit' /></td></tr>
</form>
</table>
</center>
</body>
</html>
