<?php
include_once "../setup.php";

include      ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/php/db.php";
include_once ROOT_PATH . "/php/common.php";
include_once ROOT_PATH . "/timezone_functions.php";
include_once ROOT_PATH . "/edit_game_functions.php";
include_once ROOT_PATH . "/google_calendar_functions.php";
include_once ROOT_PATH . "/menu.php";
include_once ROOT_PATH . "/autocomplete.php";

checkLevel($level,2);

$game_thread_id = $_REQUEST['thread_id'];
$pagename = "cc_show_game_stats.php";
$posts = "${here}game/$game_thread_id/";
$player = "${here}player/";
$game_page = "${here}game/";


if ( $game_thread_id == "" ) {
?>
<html>
<head>
<script language='javascript'>
<!--
window.history.back();
//-->
</script>
</head>
<body>
Please hit your browsers back button.
</body>
</html>
<?php
exit;
}

# Get Game info
$sql = "Select * from Games where thread_id=$game_thread_id";
$result = mysql_query($sql);
$game = mysql_fetch_array($result);
if ( mysql_num_rows($result) != 1 ) { $game['id'] = 0; }

$status = $game['status'];
$subthread = false;
if ( $status == "Sub-Thread" ) {
  $sql = "Select `status` from Games where id=".$game['parent_game_id'];
  $result = mysql_query($sql);
  $status = mysql_result($result,0,0);
  $subthread = true;
}
$sql = sprintf("select count(*) from Chat_rooms where game_id=%s",quote_smart($game['id']));
$result = mysql_query($sql);
$chats = mysql_result($result,0,0);

if ( $status != "Finished" ) {
  error("This game is not part of the Cassandra Competion because it is not Finished yet.");
}


# Get Cassandra Competition Information
$format = '%a. %b %e, %Y at %l:%i%p';
$sql = sprintf("select user_id, name, if(timestampdiff(HOUR,claim_time,now())>=72,'open',date_format(date_add(claim_time, interval 3 day),'%s')) as expire, challenger_id, type_error, desc_error from CC_info, Users where CC_info.user_id=Users.id and game_id=%s",$format,quote_smart($game['id']));
$result = mysql_query($sql);
if ( mysql_num_rows($result) != 1 ) {
  $CC_info['user_id'] = "";
} else {
  $CC_info = mysql_fetch_array($result);
}

# Find out if person viewing should have edit abilities.
$edit = false;
if ( $CC_info['user_id'] == $uid && $CC_info['expire'] != 'open') {
  $edit = true;
}
#if ( $level == 1 ) { $edit = true; }

if ( $game['id'] == "" || $game['id'] == 0 )  {$game['title'] = "Invalid Game";}

?>
<html>
<head>
<title><?=$game['title'];?></title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<link rel='stylesheet' type='text/css' href='/assets/css/hint.css'>
<script language='javascript'>
<!--
var thread_id = '<?=$game_thread_id;?>'
var game_id = '<?=$game['id'];?>'
var myURL = '<?=$_SERVER['REQUEST_URI'];?>'
var currentStatus = '<?=$game['status'];?>'

var xmlHttp
var element
if ( myURL == "/cc_game/"+thread_id || myURL == "/dev_game/"+thread_id ) {
  var dir = "../"
} else {
  var dir = ""
}
if ( myURL == "/dev_game/"+thread_id ) {
  dir = dir+"dev_"
}


function close(element) {
  document.getElementById(element).style.visibility='hidden'
}

function pm_players() {
element="PM_div"
document.getElementById(element).style.visibility='visible'
var url=dir+"pm_players.php?game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}

function go_replace(user_id, action) {
  sure = confirm("Are you sure?")
  if ( sure ) {
    location.href="/replace.php?user_id="+user_id+"&game_id="+game_id+"&action="+action
  }
}

//-->
</script>
<script src='/assets/js/hint.js'></script>
<script src='/competition/cc_control.js'></script>
<?php
$open_comment = "><!--";
$close_comment = "--";
if ( $edit )  {
?>
<script src='/edit_game.js'></script>
<script src='/mod_control.js'></script>
<script src='/assets/js/validation.js'></script>
<?php
$open_comment = "";
$close_comment = "";
}
?>
<script src='/assets/js/ajax.js'></script>
</head>
<body>
<?php display_menu(); ?>
<h1><div id='name_span' <?=$open_comment;?> onMouseOver='show_hint("Click to Change Name")' onMouseOut='hide_hint()' onClick='edit_name()' <?=$close_comment;?>>
<?php
if ( $game['number'] != "" ) {
  print $game['number'].") ";
}
?>
<?=$game['title'];?></div></h1>
<div id='divDescription' class='clDescriptionCont'>
<!--Empty Div used for hint popup-->
</div>

<table border='0'> <!--  This table is purely for layout-->
<tr><td valign='top' width='50%'>
<!-- Table with Main Game Information -->
<table class='forum_table' border='0' >
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to Edit Moderators")' onMouseOut='hide_hint()' onClick='edit_mod()' <?=$close_comment;?>><b>Moderator: </b></div></td>
<td id='mod_td'><?php show_moderator($game['id']); ?>
</td></tr>
<?php
if ( !$subthread ) {
?>
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to Edit Dates")' onMouseOut='hide_hint()' onClick='edit_dates()' <?=$close_comment;?>><b>Dates: </b></div></td>
<td><table border='0' width='100%'><tr><td id='date_td'><?php show_dates($game['id']); ?></td><td align='right'><? add_game_link($game['id']); ?></td></tr></table>
</td></tr>
<?php
}
?>
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to Change Status")' onMouseOut='hide_hint()' onClick='edit_status()' <?=$close_comment;?>><b>Status: </b><div></td>
<td><table width='100%' border='0'><tr><td><table width='100%'><tr><td id='status_td'><div <?=$open_comment;?>onMouseOver='show_hint("Click to Change Status")' onMouseOut='hide_hint()' onClick='edit_status()' <?=$close_comment;?>><?=$game['status'].' - '.$game['phase'].' '.$game['day'];?>
<?php
if ( $subthread) {
$sql = "Select title, thread_id from Games where id='".$game['parent_game_id']."'";
$result = mysql_query($sql);
$parent_game = mysql_fetch_array($result);
print " of <a href='$game_page".$parent_game['thread_id']."'>".$parent_game['title']."</a>";
}
print "</div></td><td align='right'>";
if ( $game['status'] == "In Progress" ) {
$format1 = '%i';
$format2 = '%l';
$sql = sprintf("select concat(date_format(if(minute>date_format(now(),'%s'),now(),date_add(now(),interval 1 hour)),'%s'),':',if(minute<10,concat('0',minute),minute)) as next from Post_collect_slots where game_id=%s",$format1,$format2,quote_smart($game['id']));
$result = mysql_query($sql);
if ( mysql_num_rows($result) > 0 ) {
$next = mysql_result($result,0,0);
print "Next Post Scan at $next";
}
}
print "</td></tr></table>";
print "<td align='right'>";
if ( $game['status'] == "Sign-up" ) {
  $sql1 = "select count(*) from Players where game_id='".$game['id']."'";
  $result1 = mysql_query($sql1);
  $count = mysql_result($result1,0,0);
  if ( $count < $game['max_players'] && !$isplayer ) {
    print "<a href='${here}sign_me_up.php?action=add&game_id=".$game['id']."'>Sign Me UP!!!</a>";
  }
  if ( $isplayer ) {
    print "<a href='${here}sign_me_up.php?action=remove&game_id=".$game['id']."'>Remove me</a>";
  }
}
print "</td></tr></table>";
?>
</td></tr>
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to Change Deadlines")' onMouseOut='hide_hint()' onClick='edit_deadline()' <?=$close_comment;?>><b>Deadlines:</b></div></td>
<?php
list($lynch,$x,$x) = split(":",$game['lynch_time']);
list($night,$x,$x) = split(":",$game['na_deadline']);
print "<td id='deadline_td'><div $open_comment onMouseOver='show_hint(\"Click to Change Deadlines\")' onMouseOut='hide_hint()' onClick='edit_deadline()' $close_comment>";
if ( $lynch != "" ) {
  print "Lynch: ".time_24($lynch)." BGG<br />";
}
if ( $night != "" ) {
  print "Night Action: ".time_24($night)." BGG";
}
print "</div>\n";
?>
</td></tr>
<?php
if ( $game['status'] == "Sign-up" ) {
print "<tr><td><div $open_comment onMouseOver='show_hint(\"Click to Change Max Players\")' onMouseOut='hide_hint()' onClick='edit_maxplayers()' $close_comment><b>Max Players:</b></div></td><td id='td_maxplayers'><div $open_comment onMouseOver='show_hint(\"Click to Change Max Players\")' onMouseOut='hide_hint()' onClick='edit_maxplayers()' $close_comment>".$game['max_players']."</div></td></tr>\n";
}
if ( !$subthread) {
print "<tr><td><div $open_comment onMouseOver='show_hint(\"Click to Change Complexity\")' onMouseOut='hide_hint()' onClick='edit_complex()' $close_comment><b>Complexity:</b></div></td><td id='td_complex'><div $open_comment onMouseOver='show_hint(\"Click to Change Complexity\")' onMouseOut='hide_hint()' onClick='edit_complex()' $close_comment>";
print show_complex($game['complex']);
print "</div></td></tr>\n";
$finished = false;
if ( $status == "Finished" || $edit) {
$finished = true;
print "<tr><td><div $open_comment onMouseOver='show_hint(\"Click to Change Winner\")' onMouseOut='hide_hint()' onClick='edit_winner()' $close_comment><b>Winner:</b></div> </td><td id='win_td'><div $open_comment onMouseOver='show_hint(\"Click to Change Winner\")' onMouseOut='hide_hint()' onClick='edit_winner()' $close_comment>".$game['winner']."</div></td></tr>\n";
}
}

if ( $edit ) {
?>
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to change BGG Thread id")' onMouseOut='hide_hint()' onClick='edit_thread()' <?=$close_comment;?>><b>BGG<br />Thread id:</b></div></td><td id='thread_td'><div <?=$open_comment;?>onMouseOver='show_hint("Click to change BGG Thread id")' onMouseOut='hide_hint()' onClick='edit_thread()' <?=$close_comment;?>><?=$game['thread_id'];?></div></td></tr>
<?php
}

if ( ! $subthread) {
$sql = "select count(*) from Games where parent_game_id='".$game['id']."'";
$result = mysql_query($sql);
$num = mysql_result($result,0,0);
if ( $num > 0 || $edit) {
print "<tr><td><div $open_comment onMouseOver='show_hint(\"Click to Add or Delete a Sub-Thread\")' onMouseOut='hide_hint()' onClick='edit_subt()' $close_comment><b>Sub-Threads:</b></div></td><td id='subt_td'>\n";
show_subt($game['id']);
print "</td></tr>\n";
}
}
?>
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to change Description")' onMouseOut='hide_hint()' onclick='edit_desc()' <?=$close_comment;?>><b>Description:</b></div></td><td id='desc_td'><div <?=$open_comment;?>onMouseOver='show_hint("Click to change Description")' onMouseOut='hide_hint()' onclick='edit_desc()' <?=$close_comment;?>><?=stripslashes($game['description']);?></div></td></tr>
</table>
</td>
<!-- End of Main Game Table -->
<td valign='top'>
<div id='cc_table'>
<table class='forum_table' width='100%'>
<tr><th>Cassandra Competition</th></tr>
<tr><td align='center'><div>
<?php
if ( $CC_info['user_id'] == "" ) {
  print "<a href='/competition/cc_claim.php?game_id=".$game['id']."'>Claim this game</a>";
} else {
  print "This game has been claimed by ".$CC_info['name'].". <br />";
  if ( $CC_info['expire'] == 'open') {
    print "Editing time has expired.<br />";
	if ( $CC_info['challenger_id'] != "" ) {
	  print "Challenged by ".get_player_page($CC_info['challenger_id'],false)."<br />";
	} else {
	  print "Any player may challenge the data.<br />";
	  print "<a href='javascript:show_challenge()'>Make a challenge</a>";
	}
  } else {
    print $CC_info['name']." has until ".$CC_info['expire']." to finish filling in the data before a player can challenge it.";
  }
  if ( $level == 1 && $CC_info['challenger_id'] != "" ) {
    print "<hr>";
    print "<table>";
	print "<tr><td style='font-weight:bold' align='right'>Type of Error:</td><td>".$CC_info['type_error']."</td></tr>";
	print "<tr><td style='font-weight:bold' align='right'>Description of Error:</td><td>".$CC_info['desc_error']."</td></tr>";
	print "<tr><td colspan='2' align='center'>";
	print "<a href='/competition/cc_mod.php?game_id=".$game['id']."&action=accept'>[Accept]</a> ";
	print "<a href='/competition/cc_mod.php?game_id=".$game['id']."&action=deny'>[Deny]</a> ";
	print "</td></tr>";
	print "</table>";
  }
}
?>
<div style='position:absolute; visibility:hidden; background-color:white; border:1px solid black;' id='cc_space'></div>
</div></td></tr>
</table>
</div>
<?php
if ( $edit ) {
?>
<div id='edit_table'>
<table class='forum_table' width='100%'>
<tr><th> 
Edit 
<img id='busy' style='visibility:hidden' src='/assets/images/ajax_busy.gif' />
</th></tr>
<tr><td align='center'><div id='edit_space'><?php clear_editSpace(); ?></div></td></tr>
</table></div>
<?php
}
?>
</td>
</tr>
</table>
<?php
  $sql = sprintf("select max(article_id) as a_id from Posts where game_id=%s",$game['id']);
  $result = mysql_query($sql);
  if ( $result ) { $article_id = mysql_result($result,0,0); }
?>

<br /><a id='game_link' href="http://www.boardgamegeek.com/thread/<?=$game_thread_id;?>">Go to Game Thread</a> 
<?php
if ( $status != "Sign-up" ) {
?>
: <a href='http://www.boardgamegeek.com/article/<?=$article_id;?>#<?=$article_id;?>'>Last retrieved post</a>
<?php
}
?>
<br />

<?php
if ( $game['auto_vt'] != "No" ) {
?>
<a href='<?=$posts;?>tally'>Vote Tally</a> : <a href='<?=$posts;?>votes'>Vote Log</a><br />
<?php 
}
#print "Number of Chat rooms: $chats<br />";
if ( $chats > 0 ) {
  print "<a href='${posts}chat'>Cassandra Communication System</a><br />";
}
?>
<a href='javascript:pm_players()'>GeekMail Players</a><br />
<div style='position:absolute; visibility:hidden; background-color:white; border:1px solid black;' id='PM_div'></div>
<table>
<tr><td>
<div id='player_table'>
<?php
createPlayer_table($edit,$game['id']);

print "</div>\n";
print "</td>\n";
print "<td valign='top'>\n";
# Show any Wolfy awards the game has won.
$sql = sprintf("select * from Wolfy_games, Wolfy_awards where Wolfy_games.award_id=Wolfy_awards.id and game_id=%s order by id, year",$game['id']);
$result = mysql_query($sql);
if ( $result ) {
  $num_awards = mysql_num_rows($result);
} else {
  $num_award = 0;
}
if ( $num_awards > 0 ) {
  print "<table class='forum_table'><tr><th>Wolfy Awards</th></tr>\n";
while ( $award = mysql_fetch_array($result) ) {
  print "<tr><td><a href='http://www.boardgamegeek.com/article/".$award['award_post']."#".$award['award_post']."'>".$award['award']." (".$award['year'].")</a></td></tr>\n";
}
  print "</table>\n";
}
print "</td></tr></table>\n";

$sql = "select distinct Posts.user_id, name from Posts, Users where Posts.user_id=Users.id and Posts.game_id='".$game['id']."' and Posts.user_id not in ( select user_id from Players where game_id='".$game['id']."') and Posts.user_id not in ( select user_id from Moderators where game_id='".$game['id']."') and Posts.user_id not in ( select replace_id from Replacements where game_id='".$game['id']."') order by name";
$result = mysql_query($sql);
$num_row = mysql_num_rows($result);
if ( $num_row > 0 ) {
print "<br />Non-Players who posted<br />\n";
print "<table class='forum_table'>\n";
while ( $row = mysql_fetch_array($result) ) {
  $sql2 = "select count(*) from Posts where game_id='".$game['id']."' and user_id='".$row['user_id']."'";
  $result2 = mysql_query($sql2);
  $num_post = mysql_result($result2,0,0);
  print "<tr><td>".get_player_page($row['name'])."<a href='$posts".$row['name']."'>($num_post posts)</a></td></tr>\n";
}
print "</table>\n";
}
?>
<br />
<table>
<tr><td><h3>Player Time Zone Chart</h3></td>
<td><?php print timezone_changer(); ?></td></tr>
</table>
<div id='tz_div'>
<?php print timezone_chart("",$game['id']); ?>
</div>
<?php
timezone_js();
#if ( $edit ) {
?>
<script language='javascript'>
setHint()
</script>
<?php 
#}
print player_autocomplete_js("new_p"); 
?>
</body>
</html>
