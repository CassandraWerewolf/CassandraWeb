<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "configure_chat_functions.php";
include_once "menu.php";

$mysql = dbConnect();
#upgrading();

$game_id = $_REQUEST['game_id'];

if ( isset($_POST['submit_all']) ) {
  submit_all($game_id);
  error("Each player now has a mod chat room.  Please refresh the page if you don't see all the chat rooms.");
}

if ( isset($_POST['submit_phys']) ) {
  submit_all($game_id, "Physics Chat");
  error("Each player now has a physics chat room.  Please refresh the page if you don't see all the chat rooms.");
}


if ( isset($_POST['submit_comb']) ) {
  submit_comb($game_id);
  error("Each player combination has been created.  Please refresh the page if you don't see all the chat rooms.");
}

if ( isset($_POST['submit_newchat']) ) {
  submit_new_chat($game_id);
  error("Room has been created.  Please refresh the page if you don't see the new chat room.");
}

if ( isset($_POST['submit_editchat']) ) {
  submit_edit_chat($game_id);
  error("Room has been edited.  Please refresh the page to see the changes.");
}

if ( isset($_POST['delete_chat']) ) {
  $room_id = $_POST['room_id'];
  delete_chat_room($room_id);
  error("Room has been deleted.  Please refresh the page to see the change.");
}

if ( isset($_POST['eye_submit']) ) {
  $room_id = $_POST['room_id'];
  $user_id = $_POST['user_id'];
  $open = $_POST['open'];
  update_chat_user($room_id,$user_id,'open',$open);
  if ( isset($_POST['close']) ) {
    $close= $_POST['close'];
	if ( $close == "" ) { $close = "N/A"; }
    update_chat_user($room_id,$user_id,'close',$close);
  }
}

//Get Game Status
$gid = $game_id;
$status = "Sub-Thread";
while ( $status == "Sub-Thread" ) {
  $sql = sprintf("select `status`, parent_game_id from Games where id=%s",quote_smart($gid));
  $result = mysqli_query($mysql, $sql);
  $status = mysqli_result($result,0,0);
  if ( $status == "Sub-Thread" ) { $gid = mysqli_result($result,0,1); }
}

$sql = sprintf("select thread_id from Games where  id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
$thread_id = mysqli_result($result,0,0);

if ( $status != "In Progress"  && $status != "Sign-up" ) {
error("This game $game_id is not In progress - Communication can only be used while game is in Progress.");
}
//Make sure the person viewing this page is a moderator of the game.
$sql = sprintf("select * from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),$uid);
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) != 1 ) {
error("You must be the moderator of the game in order to configure the Communications System.");
}

$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
$game = mysqli_fetch_array($result);
?>
<html>
<head>
<title>Cassandra Communications System - Configuration</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<script src='/assets/js/color_picker.js'></script>
<script language='javascript'>
<!--
var game_id = '<?=$game_id;?>'
var lock_image;
var eye_image;
//-->
</script>
<script src='/configure_chat.js'></script>
</head>
<body>
<?php print display_menu(); ?>
<div id='mybody' style='padding-left:10px;'>
<h1>Cassandra Communications System - Configuration</h1>
<h2>For "<?=$game['title'];?>"</h2>
<table border='0' >
<tr><td colspan='2'>
<a href='/game/<?=$thread_id;?>'>Go to Game Page</a><br />
<a href='/game/<?=$thread_id;?>/chat'>Go to Chat Page</a><br />
<a href='/configure_physics.php?game_id=<?=$game_id;?>'>Configure Physics System</a><br />
<?php
$checked = "";
if ( $game['dawn_chat_reset'] == "Yes" ) { $checked = "checked='checked'"; }
?>
<input type='checkbox' id='dawn_chat_reset' name='dawn_chat_reset' <?=$checked;?> onClick='dawn_chat_reset()' />Auto Reset All Chat Post Limits at Dawn<br />
<span>Green locks can be reset by the system.  Red locks only you can manually unlock.</span><br />
<span>The lock prevents players from posting, the eye prevents players from viewing post created after that point.</span>
</td></tr>
<tr><td valign='top'>
<div id='room_list_div'>
<?php print list_chat_rooms($game_id); ?>
</div>
</td>
<td valign='top'>
<div id='dialog_div' >
<?php print display_add_dialog($game_id); ?>
</div>
</td></tr></table>
</div>
</body>
</html>
