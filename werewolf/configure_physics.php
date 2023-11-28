<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "configure_physics_functions.php";
include_once "menu.php";

$mysql = dbConnect();
#upgrading();

$game_id = $_REQUEST['game_id'];

if ( isset($_POST['submit_import_phys']) ) {
  import_physics($game_id);
  $tab = $reverse_tab["Admin"];
  //error("All physics settings have been imported.");  
}

if ( isset($_POST['submit_phys_settings']) ) {
  submit_phys_settings($game_id);
  $tab = $reverse_tab["Admin"];
  //error("Physics settings have been modified.");  
}

if ( isset($_POST['submit_newloc']) ) {
  submit_new_loc($game_id);
  $tab = $reverse_tab["Locations"];
  //error("Location has been created. Please refresh the page if you don't see the new location.");
}

if ( isset($_POST['submit_newexit']) ) {
  submit_new_exit($game_id);
  $tab = $reverse_tab["Exits"];
  //error("Exit has been created. Please refresh the page if you don't see the new exit.");
}

if ( isset($_POST['submit_newitem']) ) {
  submit_new_item($game_id);
  $tab = $reverse_tab["Items"];
  //error("Item has been created. Please refresh the page if you don't see the new item.");
}

if ( isset($_POST['submit_newtemplate']) ) {
  submit_new_item_temp($game_id);
  $tab = $reverse_tab["Templates"];
  //error("Item Template has been created. Please refresh the page if you don't see the new item.");
}

if ( isset($_POST['submit_editloc']) ) {
  submit_edit_loc($game_id);
  $tab = $reverse_tab["Locations"];
  //error("Location has been edited.  Please refresh the page to see the changes.");
}

if ( isset($_POST['submit_editexit']) ) {
  submit_edit_exit($game_id);
  $tab = $reverse_tab["Exits"];
  //error("Exit has been edited.  Please refresh the page to see the changes.");
}

if ( isset($_POST['submit_edititem']) ) {
  submit_edit_item($game_id);
  $tab = $reverse_tab["Items"];
  //error("Item has been edited.  Please refresh the page to see the changes.");
}

if ( isset($_POST['submit_edittemplate']) ) {
  submit_edit_item_temp($game_id);
  $tab = $reverse_tab["Templates"];
  //error("Template has been edited.  Please refresh the page to see the changes.");
}

if ( isset($_POST['submit_editplayer']) ) {
  submit_edit_player($game_id);
  $tab = $reverse_tab["Players"];
  //error("Player has been edited.  Please refresh the page to see the changes.");
}

if ( isset($_POST['submit_schedule']) ) {
  submit_schedule_physics($game_id);
  $tab = $reverse_tab["Admin"];
  //error("The automatic schedule has been edited.");
}

if ( isset($_POST['submit_process_locs']) ) {
  process_movements($game_id);
  $tab = $reverse_tab["Admin"];
  //error("Players with movement orders have been moved.");
}

if ( isset($_POST['submit_process_items']) ) {
  process_items($game_id);
  $tab = $reverse_tab["Admin"];
  //error("Item orders have been processed.");
}

if ( isset($_POST['delete_item']) ) {
  delete_item($_POST['item_id']);
  $tab = $reverse_tab["Items"];
  //error("Item has been deleted.");
}

if ( isset($_POST['delete_temp']) ) {
  delete_item_temp($_POST['temp_id']);
  $tab = $reverse_tab["Templates"];
  //error("Item template has been deleted, and all items using it.");
}

if ( isset($_POST['delete_loc']) ) {
  delete_loc($_POST['loc_id']);
  $tab = $reverse_tab["Locations"];
  //error("Location has been deleted.");
}

if ( isset($_POST['delete_exit']) ) {
  delete_exit($_POST['exit_id']);
  $tab = $reverse_tab["Exits"];
  //error("Exit has been deleted.");
}


/*
if ( isset($_POST['submit_all']) ) {
  submit_all($game_id);
  error("Each player now has a mod chat room.  Please refresh the page if you don't see all the chat rooms.");
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
*/

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


if ($status != "In Progress"  && $status != "Sign-up" ) {
error("This game $game_id is not In progress - Physics can only be used while game is in Progress.");
}
//Make sure the person viewing this page is a moderator of the game.
$sql = sprintf("select * from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),$uid);
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) != 1 ) {
error("You must be the moderator of the game in order to configure the Physics System.");
}

// Set correct default tab
if ( $tab == "") { $tab = $_GET['tab']; }
$max_tab = count($ptab_list);
if ( $tab == "" || $tab > $max_tab ) { $tab = 4; }

$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
$game = mysqli_fetch_array($result);
?>
<html>
<head>
<title>Cassandra Physics System - Configuration</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<script src='/assets/js/color_picker.js'></script>
<script language='javascript'>
<!--
var game_id = '<?=$game_id;?>';
var user_id = '<?=$uid;?>';
var lock_image;
var eye_image;
//-->
</script>
<script src='/configure_physics.js'></script>
</head>
<body>
<?php print display_menu(); ?>
<div id='mybody' style='padding-left:10px;'>
<h1>Cassandra Physics System - Configuration</h1>
<h2>For "<?=$game['title'];?>"</h2>
<table border='0' >
<tr><td colspan='2'>
<a href='/game/<?=$thread_id;?>'>Go to Game Page</a><br />
<a href='/game/<?=$thread_id;?>/chat'>Go to Chat Page</a><br />
<a href='/configure_chat.php?game_id=<?=$game_id;?>'>Configure Game Communications System</a><br />
</td></tr>

<tr><td>
<table class='forum_table'>
 <tr>
  <th valign='center' align='left' id='tab_navigation'>
    <?php print display_physics_tabs($tab); ?> 
  </th>
 </tr>
 <tr>
  <td valign='top' id='tab_window'>
   <?php print show_physics_tab($tab,$uid,$game_id); ?>
  </td>
 </tr>
</table>
</td></tr></table>
</div>
</body>
</html>
