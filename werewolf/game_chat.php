<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "game_chat_functions.php";
#include_once "game_order_assistant.php";
include_once "menu.php";

dbConnect();

if ( isset($_POST['submit_editchat']) ) {
  submit_edit_chat($_POST['game_id']);
  error("Room has been edited.");
}

if ( isset($_POST['submit_newchat']) ) {
  submit_new_chat($_POST['game_id']);
  error("Room has been added.");
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


$thread_id = $_GET['thread_id'];
if ( $thread_id == "" ) {
  ?>
  <html>
  <head>
  <script language='javascript'>
  <!--
  #window.history.back();
  //-->
  </script>
  </head>
  <body>
  Please hit your browsers back button.
  </body>
  </html>
  <?php
  exit;
} # if ( $thread_id == "" )

$sql = sprintf("select * from Games where thread_id=%s",quote_smart($thread_id));
$result = mysql_query($sql);
$game = mysql_fetch_array($result);
$game_id = $game['id'];
$title = $game['title'];
$status = $game['status'];
$p_game_id = $game['parent_game_id'];

//Find out if user is moderator of this game.
$mod = is_moderator($uid,$game_id);

// Set correct default tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : "";
$max_tab = count($tab_list);
if ( ! $mod ) { $max_tab--; }
if ( $tab == "" || $tab > $max_tab ) { $tab = 1; }

// Get Main Game Status if it is a Sub-Thread
$gid = $game_id;
if($status == "Sub-Thread"){
  $sql=sprintf("select `status` from Games where id=%s",quote_smart($p_game_id));
  $result = mysql_query($sql);
  $status = mysql_result($result,0);
}

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
      agent.call('','display_chat','update_div',room_id,'Finished','<?=$uid;?>')
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
  print display_chat($first_room,$status,$uid);
  print "</div>\n";
  ?>
  </div>
  </body>
  </html>
  <?php
} # if ( $status == "Finished" )
elseif ( $status != "In Progress" && !$mod)  {
  error("A game must be In Progress or Finished for viewing Game Communications");
}  # ( $status != "In Progress")
else {

$sql = sprintf("select * from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and user_id=%s and game_id=%s order by name",quote_smart($uid),quote_smart($game_id));
$result = mysql_query($sql);
$num_rooms = mysql_num_rows($result);
if ( $num_rooms == 1 ) {
  $room = mysql_fetch_array($result);
}
$force_room = false;
if ( isset($_GET['go_to_room']) ) {
  $room['id'] = $_GET['go_to_room'];
  $force_room = true;
}

?>
<html>
<head>
<title>Communications for <?=$title;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
<script language='javascript'>
<!--
var user_id = "<?=$uid;?>";
var game_id = "<?=$game_id;?>";
var is_mod = "<?=$mod;?>";
var current_tab = "<?=$tab;?>";
//-->
</script>
<script src="/game_chat.js"></script>
<script src="/configure_chat.js"></script>
<script src='/color_picker.js'></script>
</head>
<?php
if ( isset($_REQUEST['full']) ) {
?>
  <body>
  <?php print display_menu(); ?>
  <div style='padding-left:10px;'>
  <h1>Communications for <?=$username;?> in <?=$title;?></h1>
  <?php print display_chat($_REQUEST['room_id'],$status,$uid); ?>
  </div>
  </body>
  </html>
<?php
exit;
} # ( isset($_REQUEST['full']) )
?>
<body onload="javascript:startChat();">
<?php print display_menu(); ?>
<div style='padding-left:10px;'>
<h1>Communications for <?=$username;?> in <?=$title;?></h1>
<a href='/game/<?=$thread_id;?>'>Go to Game Page</a><br />
<?php
if( $mod ) {
  print "<a href='/configure_chat.php?game_id=$game_id'>Configure Game Communications System</a><br />\n";
  print "<a href='/configure_physics.php?game_id=$game_id'>Configure Physics System</a><br />\n";
}
?>
<table class='forum_table'>
<tr>
<th valign='center'>
<form>
Chat Room: 
<?php
if ( $num_rooms > 1 ) {
  print "<select onChange='select_room_change()' id='change_room'>";
  print "<option value='0'>Change Room</option>";
  $js_out = "var room_list = new Array()\n";
  $c = 0;
  while ( $myroom = mysql_fetch_array($result) ) {
    print "<option value='".$myroom['id']."'>".$myroom['name']."</option>";
	$js_out .= "room_list[$c] = '".$myroom['id']."'\n";
	$c++;
  }
  print "</select>";
  print "<script language='javascript'>\n";
  print $js_out;
  print "</script>\n";
}
?>
</form>
</th>
<th id='tab_navigation' align='left'>
<?php
print display_tabs($tab,$mod);
?>
</th>
</tr>
<tr>
<td id='chat_window' valign='top'>
<?php
if ( $num_rooms == 1 || $force_room) {
print display_chat_room($room['id'],$uid);
} else {
print display_chat_room(0,$uid);
}
?>
</td>
<td id='tab_window' valign='top'>
<?php
print show_tab($tab,$uid,$game_id);
?>
</td>
</tr>
<tr><td align='right' colspan='2'>You must re-click each tab to see any updates.</td></tr>
</table>
<a href='/game/<?=$thread_id;?>/old_chat'>Old Chat Page</a>
</div>
</body>
</html>
<?php
}
?>
