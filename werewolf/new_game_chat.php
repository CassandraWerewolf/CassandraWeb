<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";
include_once "php/common.php";
//include_once "chat_function.php";

dbConnect();

$thread_id = $_GET['thread_id'];
$start_room_id = "null";
$start_room_name = "Please Choose a Room";
if ( $thread_id == "" ) {
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

$sql = sprintf("select id, title, status from Games where thread_id=%s",quote_smart($thread_id));
$result = mysql_query($sql);
$game_id = mysql_result($result,0,0);
$title = mysql_result($result,0,1);
$status = mysql_result($result,0,2);

// Get Main Game Status if it is a Sub-Thread
$gid = $game_id;
while ( $status == "Sub-Thread" ) {
  $sql = sprintf("select `status`, parent_game_id from Games where id=%s",quote_smart($gid));
  $result = mysql_query($sql);
  $status = mysql_query($result,0,0);
  if ( $status == "Sub-Thread" ) { $gid = mysql_result($result,0,1); }
}

//Find out if user is moderator of this game.
$sql = sprintf("select * from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($uid));
$result = mysql_query($sql);
$mod = false;
if ( mysql_num_rows($result) == 1 ) { $mod = true; }

//Show chats based on Game Status
if ( $status == "Finished" ) {
  $sql = sprintf("select * from Chat_rooms where game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
?>
<html>
<head>
<title>Communications for <?=$title;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
<script language='javascript'>
<!--
function change_rooms() {
  room_id = document.getElementById('room_select').value
  if ( room_id == "all" ) {
    game_id = document.getElementById('game_id').value
    agent.call('','display_all','update_div',game_id)
  } else {
    agent.call('','display_chat','update_div',room_id)
  }
}

function update_div(obj) {
  rm_div = document.getElementById('room_div')
  rm_div.innerHTML = obj
}
//-->
</script>
</head>
<body>
<?php print display_menu(); ?>
<div style='padding-left:10px;'>
<h1>Communications for <?=$title;?></h1>
<?php 
$menu_output = "<form name='room_view'>\n";
$menu_output .= "<input type='hidden' id='game_id' name='game_id' value='$game_id' />\n";
$menu_output .= "<select id='room_select' name='room_select' onChange='change_rooms()' >\n";
$first_room = "";
while ( $room = mysql_fetch_array($result) ) {
  if ( $first_room == "" ) { $first_room = $room['id']; }
  $menu_output .= "<option value='".$room['id']."' />".$room['name']."\n";
}
$menu_output .= "<option value='all' />All post by time\n";
$menu_output .= "</select></form><br />\n";
print $menu_output;
print "<div id='room_div'>\n";
print display_chat($first_room); 
print "</div>\n";
?>
</div>
</body>
</html>
<?php
} elseif ( $status != "In Progress")  {
  error("A game must be In Progress or Finished for viewing Game Communications");
} else {
?>
<html>
<head>
<title>Communications for <?=$title;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
<script src='/new_game_chat.js' ></script>
<script language='javascript'>
<!--
game_id = '<?=$game_id;?>';
user_id = '<?=$uid;?>';
//-->
</script>
</head>
<?php
if ( isset($_REQUEST['full']) ) {
?>
<body>
<?php print display_menu(); ?>
<div style='padding-left:10px;'>
<h1>Communications for <?=$username;?> in <?=$title;?></h1>
<?php print display_chat($_REQUEST['room_id']); ?>
</div>
</body>
</html>
<?php
} else {
?>
<body onload="javascript:startChat();">
<?php print display_menu(); ?>
<div style='padding-left:10px;'>
<h1>Communications for <?=$username;?> in <?=$title;?></h1>
<a href='/game/<?=$thread_id;?>'>Go to Game Page</a><br />
<?php
if( $mod ) {
  print "<a href='/configure_chat.php?game_id=$game_id'>Configure Game Communications System</a><br />\n";
}
?>
<table border='0' cellpadding='2' class='forum_table'>
<tr><th>Your Chat Rooms</th><th>Current Room:</th></tr>
<tr><td valign='top' id='rooms_td'>
<?php
print show_chatRooms($game_id,$uid);
?>
</td>
<td valign='top'>
<div>
<a name='<?=$start_room_id;?>'></a><h3 id='room_name'><?=$start_room_name;?></h3>
<div id='room_created'></div>
<div id='user_view'></div>
<div id='div_chat' style='height: 300px; width: 500px; overflow: auto; background-color: #dddddd; border: 1px solid #555555;'>
</div>
<form id='room' name='room' onSubmit="return blockSubmit()" method='post' >
<input type='hidden' id='room_id' name='room_id' value='<?=$start_room_id;?>' />
<input type='hidden' id='message' name='message' value='' />
<input type='hidden' id='to' name='to' value='' />
<textarea id='text' name='text' style='width: 500px; height: 80px;' onKeyPress='return enter_submit(event)'></textarea>
<br />
<input type='submit' id='send' name='send' value='Send' />
<input type='button' id='pm' name='pm' value='GeekMail Transcript' onClick='javascript:geekMail();' /> 
<input type='submit' id='full' name='full' value='View Entire Chat' onClick='javascript:entireChat();' />
</form>
</div>
</td></tr></table>
</div>
</body>
</html>
<?php
}
}
function show_chatRooms($game_id,$user_id) {
//Find out if user is moderator of this game.
$sql = sprintf("select * from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($user_id));
$result = mysql_query($sql);
$mod = false;
if ( mysql_num_rows($result) == 1 ) { $mod = true; }
//Get room id's for displaying.
  $output = "";
  $js = "<script language='javascript'>\n";
  $js .= "<!--\n";
  global $start_room_id, $start_room_name, $uid;
  $sql = sprintf("select id, name, Chat_rooms.max_post, Chat_rooms.remaining_post, if(Chat_users.`lock`='Off' ,if(close<now(),'On',Chat_rooms.`lock`), Chat_users.`lock` ) as `lock`, created, open, if(close is null, now(), close) as close, if(close is null, concat('View post after: ',open), concat('View post between: ',open,' and ',close)) as user_view  from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and game_id=%s and user_id=%s order by `lock`, name",quote_smart($game_id),quote_smart($user_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $room_ids[] = $row['id'];
    $room_names[$row['id']] = $row['name'];
	$room_created[$row['id']] = $row['created'];
	$user_view[$row['id']] = $row['user_view'];
    $sql2 = sprintf("select count(*) from Chat_messages where post_time > %s and post_time < %s and room_id=%s",quote_smart($row['open']),quote_smart($row['close']),quote_smart($row['id']));
    $result2 = mysql_query($sql2);
    $num_messages[$row['id']] = mysql_result($result2,0,0);
    $sql2 = sprintf("select count(*) from Chat_messages, Chat_users where Chat_messages.room_id=Chat_users.room_id and Chat_messages.room_id=%s and Chat_users.user_id=%s and post_time > last_view and post_time > %s and post_time < %s",quote_smart($row['id']),quote_smart($uid),quote_smart($row['open']),quote_smart($row['close']));
	$result2 = mysql_query($sql2);
	if ( mysql_num_rows($result2) != 0 ) {
      $new_messages[$row['id']] = mysql_result($result2,0,0);
	} else {
      $new_messages[$row['id']] = 0;
	}
	$lock[$row['id']] = $row['lock'];
	$max_post[$row['id']] = $row['max_post'];
	$remaining_post[$row['id']] = $row['remaining_post'];
  }
  if ( count($room_names) == 0 ) {
    $output .= "<p>Either you are not a player of this game,<br /> or the Moderator has not set up any chat<br />rooms for you.  Please wait until the game<br />is finished to view all the game's<br />communications.</p>";
  } else {
    foreach ( $room_names as $room_id => $room_name ) {
      $output .= "<a href='javascript:change_rooms(\"$room_id\",\"$room_name\",\"".$room_created[$room_id]."\",\"".$user_view[$room_id]."\")'>$room_name</a> (".$num_messages[$room_id].")";
	  if ( $lock[$room_id] == "On" ) {
        $output .= "<img id='lock_img_$room_id' src='/images/lock_green.gif' />";
	  } elseif ( $lock[$room_id] == "Secure" ) {
        $output .= "<img id='lock_img_$room_id' src='/images/lock_red.gif' />";
	  }
      if ( $new_messages[$room_id] != 0 ) {
	    $count = $new_messages[$room_id];
        $output .= "&nbsp;&nbsp;&nbsp;";
		$output .= "<span onMouseOver='javascript:{document.getElementById(\"${room_id}_nm\").style.visibility=\"visible\";}' ";
	    $output .= "onMouseOut='javascript:{document.getElementById(\"${room_id}_nm\").style.visibility=\"hidden\"}'>";
        $output .= "<img src='/images/new_message.png' border='0'/></span>";
        $output .= "<span id='${room_id}_nm' style='position:absolute;border:solid black 1px; background-color:white; visibility:hidden;'>$count new messages</span> ";
      }
	  $output .= "<br />\n";
	  if ( $max_post[$room_id] != "" ) {
        $output .= "&nbsp;&nbsp;&nbsp;(RPL: ".$max_post[$room_id].", Remaining: ".$remaining_post[$room_id].")<br />\n";
	  }
      $sql2 = sprintf("select if(last_view>(date_sub(now(), interval 3 second)),'(online)','') as available from Chat_users where user_id=%s and room_id=%s ",quote_smart($uid),quote_smart($room_id));
	  $result2 = mysql_query($sql2);
	  $online = false;
	  if ( mysql_result($result2,0,0) == "(online)" ) { $online = true; }
      $sql2 = sprintf("select if(alias is null, name, alias) as name, color, max_post, remaining_post, if(last_view>(date_sub(now(), interval 3 second)),'(online)','') as available from Chat_users, Users where Chat_users.user_id=Users.id and room_id=%s order by available desc, name asc",quote_smart($room_id));
      $result2 = mysql_query($sql2);
	  $user_list = "";
      while ( $row = mysql_fetch_array($result2) ) {
	    #if ( $online || $row['available'] == "(online)" ) {
	    #if ( $online ) {
	    if ( $online || ( $mod && $row['available'] == "(online)") ) {
          $output .= "&nbsp;&nbsp;&nbsp;<span style='color: ".$row['color'].";'>";
	      $output .= $row['name'];
	      $output .= "</span> ".$row['available']."<br />\n";
		  if ( $row['max_post'] != "" ) {
		    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(PPL: ".$row['max_post'].", Remaining: ".$row['remaining_post'].")<br />";
		  }
	      $user_list .= $row['name'].",";
	    }
      }
      $js .= "userlist['$room_id'] = '$user_list';\n";;
    }
  }
  if ( count($room_ids) == 1 ) {
    $start_room_id = $room_ids[0];
    $start_room_name = $room_names[$start_room_id];
  }

  $js .= "//-->\n";
  $js .= "</script>";

  $output .= $js;
                                                                                    
  return $output;
}

function display_chat($room_id) {
  global $status, $uid;
  $output = "<table class='forum_table' width='500px'>\n";
  $sql = sprintf("select name from Chat_rooms where id=%s",quote_smart($room_id));
  $result = mysql_query($sql);
  $room_name = mysql_result($result,0,0);
  $output .= "<tr><th>Room: $room_name</th></tr>\n";
  $format = '%b %e, %h:%i %p';
  if ( $status == "In Progress" ) {
    $sql = sprintf("select date_sub(last_view, interval 1 second) as last_view, open, if(close is null, now(), close) as close, if(close is null, 'open', if(close < now(), 'close', 'open')) as eye, now() from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($uid));
	$result = mysql_query($sql); $last_view = mysql_result($result,0,0); $open = mysql_result($result,0,1);
	$close = mysql_result($result,0,2);
	$eye_status = mysql_result($result,0,3);
    $sql = sprintf("select if(alias is null, name, alias) as name, message, color, date_format(post_time,%s) as post_time from Chat_messages, Chat_users, Users where Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_users.room_id=%s and Chat_messages.room_id=%s and (post_time > %s and post_time < %s) order by Chat_messages.id",quote_smart($format),quote_smart($room_id),quote_smart($room_id),quote_smart($open),quote_smart($close));
  } else {
    $sql = sprintf("select if(alias is null, name, alias) as name, message, color, date_format(post_time,%s) as post_time from Chat_messages, Chat_users, Users where Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_users.room_id=%s and Chat_messages.room_id=%s order by Chat_messages.id",quote_smart($format),quote_smart($room_id),quote_smart($room_id));
  }
  $result = mysql_query($sql);
  $output .= "<tr><td>";
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<span style='font-weight:bold; color:".$row['color']."'>".$row['name']." ".$row['post_time'].":</span> ".$row['message']." <br />\n";
  }
  $output .= "</td></tr>\n";
  $output .= "</table><br />\n";
  return $output;
}

function display_all($game_id) {
  $output = "<table class='forum_table' width='500px'>\n";
  $sql = sprintf("select title from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $game_title = mysql_result($result,0,0);
  $output .= "<tr><th>All post for $game_title</th></tr>\n";
  $format = '%b %e, %h:%i %p';
  $sql = sprintf("select Chat_rooms.name as room_name, if(alias is null, Users.name, alias) as name, message, color, date_format(post_time,%s) as post_time from Chat_rooms, Chat_messages, Chat_users, Users where Chat_rooms.id=Chat_messages.room_id and Chat_rooms.id=Chat_users.room_id and Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_rooms.game_id=%s order by Chat_messages.id",quote_smart($format),quote_smart($game_id));
  $result = mysql_query($sql);
  $output .= "<tr><td>";
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<span style='font-style:italic;'>".$row['room_name']."</span><br />\n";
    $output .= "<span style='font-weight:bold; color:".$row['color']."'>".$row['name']." ".$row['post_time'].":</span> ".$row['message']."<br />\n";
  }
  $output .= "</td></tr>\n";
  $output .= "</table><br />\n";
  return $output;
}

?>
