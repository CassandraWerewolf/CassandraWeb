<?php

include_once "php/accesscontrol.php";
include_once "php/common.php";
include_once "php/db.php";
include_once "php/bgg.php";
include_once "menu.php";

dbConnect();


$room_id = $_REQUEST['room_id'];
//Verify that user is allowed to see this chat room.
$sql = sprintf("select * from Chat_users where user_id=%s and room_id=%s",quote_smart($uid),quote_smart($room_id));
$result = mysql_query($sql);
if ( mysql_num_rows($result) != 1 ) {
  error("You must be a member of this chat room in order to GeekMail a transcript.");
  exit;
}

// Get the to list
//$sql = sprintf("select name from Chat_users, Users where Chat_users.user_id=Users.id and room_id=%s",quote_smart($room_id));
//$result = mysql_query($sql);
$to = "";
//while ( $row = mysql_fetch_array($result) ) {
 // $to .= $row['name'].", ";
//}

// only get the user since if aliases are used this could leak info
$sql = sprintf("select name from Chat_users, Users where Chat_users.user_id=Users.id and room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$to .= $row['name'];

// Get subject line
$sql = sprintf("select name, title from Chat_rooms, Games where Chat_rooms.game_id=Games.id and Chat_rooms.id=%s",quote_smart($room_id));
$result = mysql_query($sql);
$room_name = mysql_result($result,0,0);
$game_name = mysql_result($result,0,1);
$subject = "Transcript of Chat Room: $room_name from Game \"$game_name\"";

// Get transcript
$sql = sprintf("select date_sub(last_view, interval 1 second) as last_view, open, if(close is null, now(), close) as close, if(close is null, 'open', if(close < now(), 'close', 'open')) as eye, now() from Chat_users where room_id=%s and user_id=%s",quote_smart($_GET['room_id']),quote_smart($uid));
$result = mysql_query($sql);
$last_view = mysql_result($result,0,0);
$open = mysql_result($result,0,1);
$close = mysql_result($result,0,2);
$eye_status = mysql_result($result,0,3);

$format = '%b %e, %h:%i %p';
$sql = sprintf("select coalesce(alias,name) as name, message, color, date_format(post_time,%s) as post_time from Chat_messages, Chat_users, Users where Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_users.room_id=%s and Chat_messages.room_id=%s and (post_time > %s and post_time < %s) order by Chat_messages.id",quote_smart($format),quote_smart($room_id),quote_smart($room_id),quote_smart($open),quote_smart($close));
$result = mysql_query($sql);
$message = "";
while ( $row = mysql_fetch_array($result) ) {
  $message .= "[COLOR=".$row['color']."]".$row['name']." ".$row['post_time'].":[/COLOR] ".$row['message']."\n";
}

?>
<html>
<head>
<title>GeekMail: <?=$subject;?></title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php
print display_menu();
print geekmail_form($to,$subject,$message);
?>
</body>
</html>
