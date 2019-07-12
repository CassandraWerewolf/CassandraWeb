<?php // common.php

include_once "db.php";
dbConnect();

function error($msg) {
?>
<html>
<head>
<script language='Javascript'>
<!--
alert ("<?=$msg?>");
history.back();
//-->
</script>
</head>
<body></body>
</html>
<?php 
exit;
}
function get_player_page($player,$profile=true) {
  // $player can be a name or an user_id.
  if ( is_numeric($player) ) {
    $sql = sprintf("select name from Users where id=%s",quote_smart($player));
    $result = mysql_query($sql);
    $player = mysql_result($result,0,0);
  }
  $output = "";
  if ( $profile ) {
    $output .= get_profile_page($player,true);
	$output .= " ";
  }
  $output .= "<a href='/player/$player'>$player</a>";

  return $output;
}
function get_profile_page($player,$icon=false) {
  // $player can be a name or an user_id.
  if ( is_numeric($player) ) {
    $id = $player;
    $sql = sprintf("select name from Users where id=%s",quote_smart($player));
    $result = mysql_query($sql);
    $player = mysql_result($result,0,0);
  } else {
    $sql = sprintf("select id from Users where name=%s",quote_smart($player));
	$result = mysql_query($sql);
	$id = mysql_result($result,0,0);
  }
  $sql = sprintf("select * from Bio where user_id=%s",quote_smart($id));
  $result = mysql_query($sql);
  $count = mysql_num_rows($result);
  if ( $count == 1 ) {
    if ( $icon ) {
      $output = "<a href='/profile/$player'><img src='/images/camera-photo.png' style='border:0' alt='View Player Profile' /></a>";
    } else {
      $output = "<a href='/profile/$player'>$player</a>";
    }
  } else {
    if ( $icon ) {
	  $output  = "";
	} else {
	  $output = $player;
	}
  }

  return $output;
}
function get_game($game_id,$parameters="title"){
  global $uid;
# This function can take a string list of paramters to describe how and what should be displayed.  The game will be displayed in the order the parameters are given.  The available parambters are:
# num : Displayes the game number with a ) after it.
# title : Displays the game title as a link to the game page.
# complex: Displays a small icon of the games complexity.
# full: Displayes (number of players/Max num of players)
# post: Displays (#of post) as a link to the full cassandra files page.
# mod: Displays (list of moderators) where they are links to the players stats page.
# mod_np: Displays list of moderators where they are links to the players stats page.
# in: Displays an icon if the person viewing the page in the game (Game_users_all)
# chat: Displays an icon if the person viewing the page has a new message in a chat room for that game.
# 
  $parms = explode(", ",$parameters);
  $sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $game = mysql_fetch_array($result);
  $output = "";
  foreach ( $parms as $parm) {
  switch($parm){
    case num:
	  if ( !is_numeric($game['number']) ) { $game['number'] = "*"; }
	  $output .= $game['number'].") ";
	break;
	case title:
	  $output .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> ";
	break;
	case complex:
	  if ( $game['complex'] != "" ) {
        $output .= "<img src='/images/".$game['complex']."_small.png' /> ";
	  }
	break;
	case full:
	  $sql = sprintf("select count(*) from Players where game_id=%s",quote_smart($game_id));
	  $result = mysql_query($sql);
	  $num_players = mysql_result($result,0,0);
	  $full = "$num_players/".$game['max_players'];
	  if ( $num_players == $game['max_players'] ) { $full = "Full/$num_players"; }
	  $output .= "($full) ";
	break;
	case post:
	  $sql = sprintf("select count(*) from Posts where game_id=%s",quote_smart($game_id));
	  $result = mysql_query($sql);
	  $num_posts = mysql_result($result,0,0);
	  $output .= "<a href='/game/".$game['thread_id']."/all'>($num_posts posts)</a> ";
	break;
	case mod:
	case mod_np:
	  $sql = sprintf("select name from Users, Moderators where Users.id=Moderators.user_id and game_id=%s",quote_smart($game_id));
	  $result = mysql_query($sql);
	  $mod_num = mysql_num_rows($result);
	  $count = 0;
	  $modlist = "";
	  while ( $mod = mysql_fetch_array($result) ) {
	    if ( $count == 0  && $parm != "mod_np" ) $modlist = "(";
	    if ( $count != 0  ) $modlist .= ", ";
		$modlist .= get_player_page($mod['name'],false);
	    $count++;
	    if ( $count == $mod_num && $parm != "mod_np" ) $modlist .= ")";
	  }
      $output .= $modlist." ";
	break;
	case in:
	  if ( isset($uid) ) {
	    $sql = sprintf("select * from Users_game_all where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($uid));
	    $result = mysql_query($sql);
	    if ( mysql_num_rows($result) != 0 ) {
          $output .= "<img src='/images/calendar.png' /> ";
	    }
	  }
	break;
	case chat:
	  if ( isset($uid) ) {
        #$sql = sprintf("select count(*) from Chat_users, Chat_rooms, Chat_messages where Chat_users.room_id=Chat_rooms.id and Chat_rooms.id=Chat_messages.room_id and Chat_messages.post_time >= Chat_users.last_view and Chat_messages.post_time > Chat_users.open and Chat_messages.post_time < if(Chat_users.close is null, now(), Chat_users.close) and Chat_rooms.game_id=%s and Chat_users.user_id=%s",quote_smart($game_id),quote_smart($uid));
        $sql = sprintf("select null from Chat_users, Chat_rooms, Chat_messages where Chat_users.room_id=Chat_rooms.id and Chat_rooms.id=Chat_messages.room_id and Chat_messages.post_time >= Chat_users.last_view and Chat_messages.post_time > Chat_users.open and Chat_messages.post_time < if(Chat_users.close is null, now(), Chat_users.close) and Chat_rooms.game_id=%s and Chat_users.user_id=%s limit 1",quote_smart($game_id),quote_smart($uid));
		$result = mysql_query($sql);
		#$count = mysql_result($result,0,0);
		#if ( $count > 0 ) {
		if ( mysql_num_rows($result) == 1 ) {
          $output .= "<a href='/game/".$game['thread_id']."/chat' ";
		  #$output .= "onMouseOver='javascript:{document.getElementById(\"${game_id}_nm\").style.visibility=\"visible\";}' ";
		  #$output .= "onMouseOut='javascript:{document.getElementById(\"${game_id}_nm\").style.visibility=\"hidden\"}'";
		  $output .= ">";
		  $output .= "<img src='/images/new_message.png' border='0'/></a>";
		  #$output .= "<div id='${game_id}_nm' style='position:absolute;border:solid black 1px; background-color:white; visibility:hidden;'>$count new messages</div> ";
		}
	  }
	break;
  }
  }
  return $output;
}

function clean_cache($dir)
{
	require_once 'Cache/Lite.php';

	$options = array('cacheDir' => $dir);
	$cache = new Cache_Lite($options);

	return($cache->clean());
}

?>

