<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/bgg.php";
include_once "../php/common.php";
include_once "../menu.php";

$cache = init_cache();

dbConnect();


if ( isset($_POST['submit']) ) {
  $_POST['title'] = safe_html($_POST['title']);
  $title = "Auto-mod: ".$_POST['title'];
  $message = file_get_contents("rulesets/".$_POST['template']."_ruleset.txt");
  $message .= file_get_contents("notice.txt");
  if ( $_POST['tie_break'] == 'lhv' ) {
    $tiebreaker = "longest held vote";
  } else {
    $tiebreaker = "longest held last vote";
  }
  $message .= "\nr{[b]Important Information:[/b]\n";
  $message .= "Tiebreaker: $tiebreaker\n";
  if ( $_POST['deadline_speed'] == "Standard") {
    $lynch = time_24($_POST['lynch']);
    $lynch_db = $_POST['lynch'];
    $message .= "Lynch time: ".$lynch."\n";
    $night = time_24($_POST['night']);
    $night_db = $_POST['night'];
    $message .= "Night Action Deadline: ".$night."\n";
    $day_length = "NULL";
    $night_length = "NULL";
  } else {
    $message .= "This is a Fast game where the \n";
    $day_length = $_POST['day_length'];
    $message .= "Day Length = $day_length \n";
    $night_length = $_POST['night_length'];
    $message .= "Night Length = $night_length \n";
    $lynch_db = "NULL";
    $lynch = "$day_length after Dawn";
    $night_db = "NULL";
    $night = "$night_length after Dusk";
  }
  if ( $_POST['weekend'] == "on" ) {
    $message .= "Weekend Policy: This game will run over the weekend.\n";
  } else {
    $message .= "Weekend Policy: This game will not run over the weekend.\n";
  }
  $message .= "}r\n";
  if ( $_POST['swf'] == "on" ) {
    $swf = "Yes";
    $message .= "\n[b]This game will start when full.[/b]\n";
	$start_date = date('Y-m-d');
  } else {
    $swf = "No";
    $message .= "\n[b]This game will not start until ".$_POST['start_date'].".  If it is not full by that date, it will start as soon as it is full after that date.[/b]\n";
	$start_date = $_POST['start_date'];
  }
  $message = preg_replace('/<tiebreaker>/',$tiebreaker,$message);
  $message = preg_replace('/<lynch>/',$lynch,$message);
  $message = preg_replace('/<night>/',$night,$message);
  $s_title = stripslashes($title);
  $thread_id = create_thread($s_title,$message,'76');
  $body = "This post is where the player list will be updated as players sign up using Cassandra.  http://cassandrawerewolf.com/game/$thread_id";
  $player_list_id = $bgg_cassy->reply_thread($thread_id,$body);

  $sql = sprintf("select * from AM_template where id=%s",quote_smart($_POST['template']));
  $result = mysql_query($sql);
  $template = mysql_fetch_array($result);

  $sql = sprintf("insert into Games (id, start_date, title, status, thread_id, description, swf, aprox_length, max_players, player_list_id, complex, game_order, auto_vt, deadline_speed, lynch_time, na_deadline, day_length, night_length, automod_id, automod_timestamp) values(NULL, %s, %s, 'Sign-up', %s, %s, %s, 7, %s, %s, 'Low', 'on', %s, %s, %s, %s, %s, %s, %s, now())",quote_smart($start_date),quote_smart($title),quote_smart($thread_id),quote_smart($template['description']),quote_smart($swf),quote_smart($template['num_players']),quote_smart($player_list_id),quote_smart($_POST['tie_break']),quote_smart($_POST['deadline_speed']),quote_smart($lynch_db),quote_smart($night_db),quote_smart($day_length),quote_smart($night_length),quote_smart($_POST['template']));
  $result = mysql_query($sql);
  $game_id = mysql_insert_id();

  # if not a fast game update the lynch times to add 1 min
  if ( $_POST['deadline_speed'] == "Standard" ) {
    $sql = sprintf("update Games set lynch_time=addtime(lynch_time,'00:01:00'), na_deadline=addtime(na_deadline,'00:01:00') where id = %s",quote_smart($game_id));
    $result = mysql_query($sql);
  }

  $sql = sprintf("insert into Moderators (user_id, game_id) values (%s, %s)",quote_smart(306),quote_smart($game_id));
  $result=mysql_query($sql);

  if ( $_POST['weekend'] == "on" ) {
    $sql = sprintf("update Games set automod_weekend=1 where id=%s",quote_smart($game_id));
	$result = mysql_query($sql);
  }

  $cache->remove('games-signup-list','front');
  $cache->remove('games-signup-fast-list','front');
  $cache->remove('games-signup-swf-list','front');
?>
<html>
<head>
<script language='javascript'>
<!--
window.location.href='/game/<?=$thread_id;?>'
//-->
</script>
</head>
<body>
If page does not re-direct <a href='/game/<?=$thread_id;?>'>click here</a>.
</body>
</html>

<?php
} else {
$today = date('Y-m-d');
$deadline_speed = array('Standard'=>'Standard', 'Fast'=>'Fast');
?>
<html>
<head>
<title>AutoMod Game Setup</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<script language='javascript'>
<!--
function  validate_form() {
 if ( document.getElementById('title').value == "" ) { 
   alert("You need to have a name for this game.")
   return false 
 }
 if ( document.getElementById('template').value == 0 ) { 
   alert("You need to choose a template")
   return false
 }
 if ( document.getElementById('tie_break').value == 0 ) { 
   alert("You need to choose a tie breaker")
   return false
 }
 if ( document.getElementById('lynch').value == "" ) {
  alert("Lynch time must be filled in")
  return false
 }
 if ( document.getElementById('night').value == "" ) {
  alert("Night Action Deadline time must be filled in")
  return false
 }
 if ( document.getElementById('lynch').value == document.getElementById('night').value ) {
   alert("Lynch time and Night Action Deadline can not be the same time")
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
<?php display_menu(); ?>
<div style='padding:10px;'>
<h1>AutoMod Game Setup - Beta</h1>
<p style='color:red'>Please be aware that issues may occur.  So please check that the thread was properly created.  If it wasn't or you have any other issues please contact Melsana.</p>
<form method='post' action='<?=$_SERVER['PHP_SELF'];?>'  onSubmit='return validate_form()'>
<table class='thin_table'>
<tr> <td>Game Name:</td>
 <td><input type='text' id='title' name='title' /></td> </tr>
<tr> <td>Template:</td>
 <td>
<?php
$where = sprintf("where mode = 'Active' || ( mode = 'Test' && owner_id = %s )",quote_smart($uid)); 
if ( $level == 1 ) { $where = "where mode != 'Edit'"; }
$sql = "select * from AM_template $where order by name";
$result = mysql_query($sql);
$html = "<select id='template' name='template' onChange='show_desc()' >\n".
$html .= "<option value='0' >Please Select</option>\n";
$js = "<script language='javascript'>\n<!--\n";
$divs = "";
while ( $row = mysql_fetch_array($result) ) {
  $html .= "<option value='".$row['id']."'>".$row['name']."</option>\n";
  $divs .= "<div id='desc_".$row['id']."' style='visibility:hidden; position:absolute;'>".$row['description']."</div>\n";
}
print $html;
print "</select>\n";
print $divs;
print "<div id='temp_desc'></div>";
print $js;
?>
function show_desc() {
  id = document.getElementById('template').value
  name = 'desc_'+id
  document.getElementById('temp_desc').innerHTML = document.getElementById(name).innerHTML
}
//-->
</script>
 </td> </tr>
<tr> <td>Tie Breaker</td>
 <td><select name='tie_break' id='tie_break'>
 <option value='lhlv' >Longest Held Last Vote</option>
 <option value='lhv' >Longest Held Vote</option>
 </select></td></tr>
<tr><td>Speed</td>
  <td><?php print create_dropdown('deadline_speed','Standard',$deadline_speed,"onChange='show_deadlines()'"); ?></td></tr>
<tr> <td>Start Date:</td>
 <td><input type='checkbox' id='swf' name='swf' checked='checked' onclick='show_dates()' />As soon as game is full<br />
 <div id='start' class='hideDiv'>
 <input type='text' name='start_date' value='<?=$today;?>'/>  
   <div id='start_time' class='hideDiv'>
      <?php print time_dropdown('s_time'); ?>
    </div>
 </div>
 </td></tr>
<tr><td>Deadlines</td>
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
<tr> <td>Run Over Weekend:</td>
 <td><input type='checkbox' id='weekend' name='weekend' />Yes</td></tr>
<tr> <td colspan='2' align='center'><input type='submit' name='submit' value='submit' /></td></tr>
</table>
</form>
</div>
</body>
</html>
<?php
}
?>
