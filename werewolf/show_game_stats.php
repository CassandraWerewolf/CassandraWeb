<?php

include "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "timezone_functions.php";
include_once "edit_game_functions.php";
include_once "google_calendar_functions.php";
include_once "menu.php";
include_once "autocomplete.php";

#checkLevel($level,1);

$game_thread_id = $_REQUEST['thread_id'];
$here = "/";
$pagename = "show_game_stats.php";
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

# Find out if the person viewing is the moderator.
$moderator = is_moderator($uid,$game['id']);

# Find out if the person viewing is a player.
$sql = "Select * from Players, Games where Players.game_id=Games.id and user_id=$uid and thread_id=$game_thread_id";
$result=mysql_query($sql);
$row_count = mysql_num_rows($result);
$isplayer = false;
if ( $row_count == 1 ) $isplayer = true;
$player_info = mysql_fetch_array($result);

# Find out if person viewing should have edit abilities.
$edit = false;
if ( $moderator || (($level == 1 || $level == 2)  && $status == 'Finished') ) {
  $edit = true;
}

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
if ( myURL == "/game/"+thread_id || myURL == "/dev_game/"+thread_id ) {
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
<?php
print time_dropdown_js();
$open_comment = "><!--";
$close_comment = "--";
if ( $edit )  {
?>
<script src='/assets/js/color_picker.js'></script>
<script src='<?=$here;?>edit_game.js'></script>
<script src='<?=$here;?>mod_control.js'></script>
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
<?=$game['title'];?>
</div></h1>
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
<td><table border='0' width='100%'><tr><td id='date_td'><?php show_dates($game['id']); ?></td><td align='right'><? print add_game_link($game['id']); ?></td></tr></table>
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
    if ( $player_info['need_to_confirm'] == 1 ) {
      print "<a href='${here}sign_me_up.php?action=confirm&game_id=".$game['id']."'>Confirm</a><br />";
	}
    print "<a href='${here}sign_me_up.php?action=remove&game_id=".$game['id']."'>Remove me</a>";
  }
}
print "</td></tr></table>";
?>
</td></tr>
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to Change Speed")' onMouseOut='hide_hint()' onClick='edit_speed()' <?=$close_comment;?>><b>Speed:</b></div></td>
<td id='speed_td'><div <?=$open_comment;?> onMouseOver='show_hint("Click to Change Speed")' onMouseOut='hide_hint()' onClick='edit_speed()' <?=$close_comment;?>><?=$game['deadline_speed'];?></div></td>
</tr>
<tr><td><div <?=$open_comment;?>onMouseOver='show_hint("Click to Change Deadlines")' onMouseOut='hide_hint()' onClick='edit_deadline()' <?=$close_comment;?>><b>Deadlines:</b></div></td>
<?php
list($lynch,$lmin,$x) = split(":",$game['lynch_time']);
list($night,$nmin,$x) = split(":",$game['na_deadline']);
list($day_length,$dlmin,$x) = split(":",$game['day_length']);
list($night_length,$nlmin,$x) = split(":",$game['night_length']);
print "<td id='deadline_td'><div $open_comment onMouseOver='show_hint(\"Click to Change Deadlines\")' onMouseOut='hide_hint()' onClick='edit_deadline()' $close_comment>";
if ( $game['deadline_speed'] == "Standard" ) {
  if ( $lynch != "" ) {
    print "Dusk: ".time_24($lynch,$lmin)." BGG<br />";
  }
  if ( $night != "" ) {
    print "Dawn: ".time_24($night,$nmin)." BGG";
  }
} else {
  print "Day Length: $day_length:$dlmin <br />\n";
  print "Night Length: $night_length:$nlmin <br />\n";
}
print "</div>\n";
?>
</td></tr>
<?php
if ( $lynch != "" ) {
  $sql = sprintf("SELECT concat_ws(', ',if(sun, 'Sun', null),if(mon, 'Mon', null), if(tue, 'Tue', null), if(wed, 'Wed', null), if(thu, 'Thu', null), if(fri, 'Fri', null), if(sat, 'Sat', null)) as lynch_days from Auto_dusk where  game_id=%s",quote_smart($game['id']));
  $result = mysql_query($sql);
  if ( mysql_num_rows($result) == 1 ) {
    $lynch_days = mysql_result($result,0,0);
	print"<tr><td><b>Game Days:</b></td><td>$lynch_days</td></tr>\n";
  }
 
}
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
<?php
if ( $edit ) {
  if ( $moderator && ($status != "Finished") ) {
?>
<div id='control_table'>
<table class='forum_table' width='100%'>
<tr><th> 
Moderator Controls 
</th></tr>
<tr><td align='center'>
<?php
# Controls while the game is in Signup
#if ( $status == "Sign-up" ) { 
#  print "<div><a href='javascript:rand_assign()'>Randomly Assign Roles</a></div>\n"; 
#  print "<div><a href='javascript:delete_game()'>Remove this game from the Cassandra Database</a></div>\n"; 
#}
if ( $status != "Finished" ) { 
  print "<div><a href='javascript:rand_assign()'>Randomly Assign Roles</a></div>\n"; 
  print "<div><a href='javascript:delete_game()'>Remove this game from the Cassandra Database</a></div>\n";
  print "<div><a href='${here}configure_physics.php?game_id=".$game['id']."'>Activate/Configure Physics System</a></div>\n"; 
}
# Controls while the game is in Progress
if ( $status == "In Progress" ) {
  if ( $game['auto_vt'] == "No" ) { 

    print "<div><a href='javascript:activate_vt()'>Activate Auto Vote Tally</a></div>\n";
  } else {
    print "<div><a href='javascript:retrieve_vt()'>Force Vote Tally Post</a></div>\n";
    if ( $game['vote_by_alias'] == "No" ) {
      #print "<div><a href='javascript:activate_aliases()'>Require Voting by Aliases</a></div>\n";
    }
  }
  print "<div><a href='${here}configure_chat.php?game_id=".$game['id']."'>Activate/Configure Game Communications System</a></div>\n"; 
  if ( $chats > 0 ) {
    print "<div><a href='javascript:activate_goa()'>Activate/Modify Game Order Assistant</a></div>\n";
  }
  if ( $game['missing_hr'] > 0 ) {
    print "<div><a href='javascript:activate_mpw()'>Missing Player Warning: ".$game['missing_hr']."hrs</a></div>";
  } else {
    print "<div><a href='javascript:activate_mpw()'>Activate Missing Player Warning System</a></div>";
  }
  print "<div><a href='javascript:activate_al()'>Activate/Modify Alias Settings</a></div>\n";
  #print "<div><a href='javascript:activate_ad()'>Activate/Modify Auto Dusk</a></div>\n";
}
# Controls for the game in sign-up or In progress
if ( $status == "Sign-up" || $status == "In Progress" ) {
  print "<div><a href='javascript:random_selector()'>Random Selector Tool</a></div>\n";
  $sql_player_list = sprintf("select name from Players, Players_all, Users where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players_all.user_id=Users.id and Players.game_id=%s order by name",quote_smart($game['id']));
  $result_player_list = mysql_query($sql_player_list);
?>
<script language='javascript'>
//<!--
<?php
$list = "";
$count = 0;
while ( $player = mysql_fetch_array($result_player_list) ) {
  if ( $list != "" ) { $list .= ", "; }
  $list .= '"'.$player['name'].'"';
  $count ++;
}
 print "var Player_list = new Array($list)\n";
?>

function random_selector() {
  myelement = document.getElementById("control_space")
  myelement.style.visibility='visible'
  myelement.innerHTML = "<form><table class='forum_table'>"
  myelement.innerHTML += "<tr><td>Choose:</td><td><input type='text' size='2' id='rand_count' value='1' /></td></tr>"
  myelement.innerHTML += "<tr><td><input type='checkbox' onClick=select_all() id='all' /></td><td>Select All</td></tr>"
  for(var i=0; i < <?=$count;?>; i++) {
    myelement.innerHTML += "<tr><td><input type='checkbox' id='"+Player_list[i]+"' /></td><td>"+Player_list[i]+"</td></tr>\n"
  }
  myelement.innerHTML += "<tr><td colspan='2'><input type='button' value='Submit' onClick='select_random()' /></td></tr>\n"
  myelement.innerHTML += "</table>"
  myelement.innerHTML += "<span align='right'><a href='javascript:close(\"control_space\")'>[close]</a></span>\n"
  myelement.innerHTML += "</forum>"
}

function select_all() {
  if ( document.getElementById('all').checked ) {
    for(var i=0; i < <?=$count;?>; i++) {
	  document.getElementById(Player_list[i]).checked = true
	}
  } else {
    for(var i=0; i < <?=$count;?>; i++) {
	  document.getElementById(Player_list[i]).checked = false
	}
  }
}

function select_random() {
  var rand_list = new Array()
  c = 0;
  for(var i=0; i < <?=$count;?>; i++) {
    if ( document.getElementById(Player_list[i]).checked ) {
     rand_list[c] = Player_list[i]
	 c++
	}
  }
  num = document.getElementById('rand_count').value
  if ( num <= c ) { 
    var r_list = new Array()
    for(var i=0; i < num; i++ ) {
      r = Math.floor(Math.random()*c)
    	while ( in_array(r,r_list) ) {
        r = Math.floor(Math.random()*c)
    	}
  	  r_list[i] = r
    }
  }
  myelement = document.getElementById("control_space")
  myelement.style.visibility='visible'
  myelement.innerHTML = ""
  if ( num <= c ) {
    myelement.innerHTML += "<ul>"
    for(var i=0; i<num; i++ ) {
        myelement.innerHTML += "<li>"+rand_list[r_list[i]]+"</li>"
    }
	myelement.innerHTML += "</ul>";
  } else {
    myelement.innerHTML += "Not enough players<br />selected to provide<br />results.";
  }
    myelement.innerHTML += "<br /><span align='right'><a href='javascript:close(\"control_space\")'>[close]</a></span>"
}

function in_array(mystring,myarray) {
  for (var i=0; i< myarray.length; i++ ) {
    if ( mystring == myarray[i] ) {
      return true;
	}
  }
  return false;
}
//-->
</script>

<?php
}
?>
<div style='position:absolute; visibility:hidden; background-color:white; border:1px solid black;' id='control_space'></div>
</td></tr>
</table></div>
<?php

  }
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
# Create a place for the Player to make their own comments about the game.
if ( $isplayer && $status != "Sign-up") { 
  $sql_player_comment = sprintf("select user_comment, original_id from Games, Players, Players_all where Games.id=Players_all.game_id and Games.id=Players.game_id and Players_all.original_id=Players.user_id and Players_all.game_id=Players.game_id and Players_all.user_id=%s and Players_all.game_id=%s",quote_smart($uid),quote_smart($game['id']));
  $result_player_comment = mysql_query($sql_player_comment);
  $player_comment = mysql_result($result_player_comment,0,0); 
  $player_original_id = mysql_result($result_player_comment,0,1);
?>
<script language='javascript'>
<!--
var user_id = "<?=$uid;?>";
var game_id = "<?=$game['id'];?>";
var original_id = "<?=$player_original_id;?>";
var myDiv = "";
var myComment = "";
var myForm = "";

function edit_player_comment() {
  myComment = document.getElementById('player_comment');
  myForm = document.getElementById('form_player_comment');
  myDiv = myForm
  agent.call('','edit_dialog','update_div',user_id,game_id,original_id);
}

function update_div (str) {
  myComment.style.visibility = "hidden";
  myComment.style.position = "absolute";
  myComment.innerHTML = "";
  myForm.style.visibility = "hidden";
  myForm.style.position = "absolute";
  myForm.innerHTML = "";
  myDiv.style.visibility = "visible";
  myDiv.style.position = "static";
  myDiv.innerHTML = str;
}

function submit_comment() {
  comment = document.getElementById('new_comment').value;
  myComment = document.getElementById('player_comment');
  myForm = document.getElementById('form_player_comment');
  myDiv = myComment
  agent.call('','update_comment','update_div',user_id,game_id,original_id,comment);
}
function clear_edit() {
  comment = document.getElementById('new_comment').value;
  myComment = document.getElementById('player_comment');
  myForm = document.getElementById('form_player_comment');
  myDiv = myComment
  if (comment == "" ) { comment = "&nbsp;" }
  update_div(comment)
}
 //-->
</script>
<table class='forum_table' width='100%'>
<tr><th>My Comments</th></tr>
<tr><td align='center'>The Comments you write here will be publically viewable on <a href="<?="$player$username/games_played";?>">your game page</a> after the game is finished. </td></tr>
<tr><td><div id='player_comment' onMouseOver='show_hint("Click to Edit your Comment")' onMouseOut='hide_hint()' onClick='edit_player_comment()' style='visibility:visible; position:static;'><?=$player_comment;?>&nbsp;</div>
<div id='form_player_comment' style='visibiliy:hidden; position:absolute;'></div>
</td></tr>
</table>
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


<?php
if ( $game['automod_id'] != "") {
?>
<br /><a href='../automod/template/<?=$game['automod_id'];?>'>Automod Template #<?=$game['automod_id'];?></a>
<?php
}
?>

<br />

<?php
if ( $game['auto_vt'] != "No" ) {
?>
<a href='<?=$posts;?>tally'>Vote Tally</a> : <a href='<?=$posts;?>tally_inverted'>Inverted Tally</a> : <a href='<?=$posts;?>votes'>Vote Log</a> : <a href='<?=$posts;?>votes/xml'>XML</a> : <a href='http://gamedecay.com/voteviewer.html?g=<?=$game_thread_id;?>'>VoteViewer</a> <br />
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

<?php
function edit_dialog($user_id,$game_id,$original_id) {
  $output = "<form>\n";
  if ( $user_id == $original_id ) {
    $table = "Players";
    $field = "user_comment";
    $id = "user_id";
  } else {
    $table = "Replacements";
    $field = "rep_comment";
    $id = "replace_id";
  }
  $sql = sprintf("select %s from %s where %s=%s and game_id=%s",$field,$table,$id,quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $comment = mysql_result($result,0,0);
  $output .= "<textarea id='new_comment' name='new_comment' style='width:100%; height:80px;'>$comment</textarea><br />";
  $output .= "<input type='button' name='submit' value='submit' onclick='submit_comment()' /> ";
  $output .= "<input type='button' name='cancel' value='cancel' onclick='clear_edit()' />";
  $output .= "</form>\n";

  return $output;
}

function update_comment($user_id,$game_id,$original_id,$comment){
  if ( $user_id == $original_id ) {
    $table = "Players";
    $field = "user_comment";
    $id = "user_id";
  } else {
    $table = "Replacements";
    $field = "rep_comment";
    $id = "replace_id";
  }
  $comment = stripslashes($comment);
  $comment = safe_html($comment);
  $sql = sprintf("update %s set %s=%s where %s=%s and game_id=%s",$table,$field,quote_smart($comment),$id,quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);

  return $comment;
}


?>
