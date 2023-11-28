<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";

dbconnect();

if (isset($_GET['action']) && $_GET['action'] == "read_all") {
  mark_all_read($uid, $_GET['game_id']);
  exit;
}

$room_lp = explode(",",$_GET['room_id']);
if ( isset ($_GET['game_id']) ) {
  // Brodcast message.  Create a room loop.
  $sql = sprintf ("select id from Chat_rooms where game_id=%s",quote_smart($_GET['game_id']));
  $result = mysqli_query($mysql, $sql);
  while ( $room = mysqli_fetch_array($result) ) {
    if ( $room['id'] != $_GET['room_id'] ) {
      $room_lp[] = $room['id'];
    }
  }
}
foreach ( $room_lp as $room_id ) {
  if ( $room_id == "null"  || $room_id == 0) { continue; }
  //Verify that the player requesting this data has access to this room.
  $sql = sprintf("select * from Chat_users where user_id=%s and room_id=%s",quote_smart($uid),quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
  if ( mysqli_num_rows($result) == 0 ) {
    exit;
  }

  //Check to see if a message was sent.
  if(isset($_POST['message']) && $_POST['message'] != '') {
    $_POST['message'] = safe_html($_POST['message'],"<a>");
    // Check that the room isn't locked.
    $sql = sprintf("select `lock` from Chat_rooms where id=%s",quote_smart($room_id));
    $result = mysqli_query($mysql, $sql);
    if ( mysqli_result($result,0,0) == "Off" ) {
      // Check that the player isn't locked.
      $sql = sprintf("select `lock` from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($uid));
      $result = mysqli_query($mysql, $sql);
      if ( mysqli_result($result,0,0) == "Off" ) {
        $sql = sprintf("insert into Chat_messages (room_id, user_id, message, post_time) values (%d, %d, %s, now())",quote_smart($room_id),quote_smart($uid),quote_smart($_POST['message']));
        $result = mysqli_query($mysql, $sql);
   	    //Find out if poster is a moderator
  	    $sql = sprintf("select `type` from Users_game_all, Chat_rooms where Users_game_all.game_id = Chat_rooms.game_id and Chat_rooms.id=%s and user_id=%s",quote_smart($room_id),quote_smart($uid));
	    $result = mysqli_query($mysql, $sql);
	    if ( mysqli_result($result,0,0) != "moderator" ) {
	      $sql = sprintf("update Chat_rooms set remaining_post = remaining_post-1 where id=%s",quote_smart($room_id));
	      $result = mysqli_query($mysql, $sql);
	      $sql = sprintf("update Chat_users set remaining_post = remaining_post-1 where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($uid));
	      $result = mysqli_query($mysql, $sql);
        }
	  }
    }
  }
}

//Get the users last_visit time, and view window.
$sql = sprintf("select date_sub(last_view, interval 1 second) as last_view, open, if(close is null, now(), close) as close, if(close is null, 'open', if(close < now(), 'close', 'open')) as eye, now() from Chat_users where room_id=%s and user_id=%s",quote_smart($_GET['room_id']),quote_smart($uid));
$result = mysqli_query($mysql, $sql);
if($result)
{
	$last_view = mysqli_result($result,0,0);
	$open = mysqli_result($result,0,1);
	$close = mysqli_result($result,0,2);
	$eye_status = mysqli_result($result,0,3);
}

$last = (isset($_GET['last']) && $_GET['last'] != '') ? $_GET['last'] : 0;
//Figure out what the First message should be if $last = 0.
$first_message = "";
if ( $last == 0 ) {
  // Find out how many messages are new since the visiters last view of the room.
  $sql = sprintf("select count(*) from Chat_messages where room_id=%s and post_time > %s and ( post_time >= %s and post_time <= %s )",quote_smart($_GET['room_id']),quote_smart($last_view),quote_smart($open),quote_smart($close));
  $result = mysqli_query($mysql, $sql);
  $new_messages = mysqli_result($result,0,0);

  // Find out the max new messages to show from the player profile 
  $max_messages = 0;
  $sql = sprintf("select max_messages from Bio where user_id=%s",quote_smart($uid));
  $result = mysqli_query($mysql, $sql);
  if(mysqli_num_rows($result) > 0) { $max_messages = mysqli_result($result,0,0); }
  if($max_messages <= 0 or $max_messages = '') { $max_messages = $new_messages; }
  if ( $new_messages > $max_messages ) { $new_messages = $max_messages; }

  $show_messages = 25;
  if ( $new_messages > $show_messages ) { $show_messages = $new_messages; }

  // Find the message id of the one $show_messages back from the end.
  $sql = sprintf("select id from Chat_messages where room_id=%s and ( post_time >= %s and post_time <= %s ) order by id",quote_smart($_GET['room_id']),quote_smart($open),quote_smart($close));
  $result = mysqli_query($mysql, $sql);
  $total_messages = mysqli_num_rows($result);
  $get_message = $total_messages - $show_messages;  
  if ( $get_message <= 0 ) { 
    $get_message = 0; 
	  $first_message = "";
  } else {
    $first_message = '<message id="0">';
    $first_message .= '<user>'.htmlspecialchars('Cassandra Project').'</user>';
	  $first_message .= '<color>'.htmlspecialchars('#ffffff').'</color>';
	  $first_message .= '<bgcolor>'.htmlspecialchars('#000000').'</bgcolor>';
	  $first_message .= '<text>'.htmlspecialchars("This Chat room has been truncated for faster loading.  To see the entire chat click on the 'View Entire Chat' button below.").'</text>';
	  $first_message .= '<time>Now</time>';
	  $first_message .= '</message>';
  }
  $last = "0";
  if ($result) { $last = mysqli_result($result,$get_message,0) -1; }
}
	
//Create the XML response.
$xml = '<?xml version="1.0" ?><root>';
//Check to ensure the user is in a chat room.
if(!isset($_GET['room_id'])) {
  $xml .='Your are not currently in a chat session.';
  $xml .= '<message id="0" >';
  $xml .= '<user>Cassandra Project</user>';
  $xml .= '<text>Your are not currently in a chat session.</text>';
  $xml .= '<time>' . date('h:i') . '</time>';
  $xml .= '</message>';
} else {
  $xml .= $first_message;
  $f1 = '%h:%i %p';
  $f2 = '%b %e, %h:%i %p';
  $sql = sprintf("select Chat_messages.id as message_id, coalesce(alias,name) as name, IFNULL(color,'#000000') as color, message, if(post_time<(date_sub(now(), interval 1 day)),date_format(post_time,%s),date_format(post_time,%s)) as post_time, if(%s<=post_time,'#ffffff','#dddddd') as bgcolor from Chat_messages inner join Users on Chat_messages.user_id=Users.id left join Chat_users on Chat_users.user_id=Users.id and Chat_messages.room_id = Chat_users.room_id where Chat_messages.room_id=%s and Chat_messages.id > %s and (post_time >= %s and post_time <= %s) order by Chat_messages.id",quote_smart($f2),quote_smart($f1),quote_smart($last_view),quote_smart($_GET['room_id']),quote_smart($last),quote_smart($open),quote_smart($close));
#  $sql = sprintf("select Chat_messages.id as message_id, coalesce(alias,name) as name, color, message, if(post_time<(date_sub(now(), interval 1 day)),date_format(post_time,%s),date_format(post_time,%s)) as post_time, if(%s<=post_time,'#ffffff','#dddddd') as bgcolor from Chat_messages, Chat_users, Users where Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_users.room_id=%s and Chat_messages.room_id=%s and Chat_messages.id > %s and (post_time >= %s and post_time <= %s) order by Chat_messages.id",quote_smart($f2),quote_smart($f1),quote_smart($last_view),quote_smart($_GET['room_id']),quote_smart($_GET['room_id']),quote_smart($last),quote_smart($open),quote_smart($close));
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $xml .= '<message id="'.$row['message_id'].'">';
    $xml .= '<user>'.htmlspecialchars($row['name']).'</user>';
	$xml .= '<color>'.htmlspecialchars($row['color']).'</color>';
	$xml .= '<bgcolor>'.htmlspecialchars($row['bgcolor']).'</bgcolor>';
	$xml .= '<text>'.htmlspecialchars($row['message']).'</text>';
	$xml .= '<time>'.$row['post_time'].'</time>';
	$xml .= '</message>';
  }
}
$xml .= '</root>';
print $xml;

//Update last_view to now.
if ( $eye_status == "open" ) {
  $sql = sprintf("update Chat_users set last_view=now() where user_id=%s and room_id=%s",quote_smart($uid),quote_smart($_GET['room_id']));
  $result = mysqli_query($mysql, $sql);
} else {
  $sql = sprintf("update Chat_users set last_view=%s where user_id=%s and room_id=%s",quote_smart($close),quote_smart($uid),quote_smart($_GET['room_id']));
  $result = mysqli_query($mysql, $sql);

}

function mark_all_read($user_id, $game_id){
  $sql = sprintf("update Chat_users set last_view=now() where user_id=%s and room_id in (select id from Chat_rooms where game_id=%s)",quote_smart($user_id),quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
}
?>
