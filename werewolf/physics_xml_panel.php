<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "configure_physics_functions.php";
include_once "menu.php";

dbConnect();
#upgrading();

if (!($uid == 459 || $uid == 754 || $uid == 58 || $uid == 905 || $uid == 18 || $uid == 321 || $uid == 367)) {
  error("You do not currently have access to this page. Ask Avin for more details.");
}

// Export
$sql = sprintf("select Games.id id, Games.title title from Games, Moderators where Moderators.game_id = Games.id and Moderators.user_id=%s",quote_smart($uid));
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) { ?>
<form id='export_form' action="export_physics.php" method="get" >
<select name='export_game_id'>
<?php 
    while ($game = mysql_fetch_array($result)) {
      echo "<option value='".$game['id']."'>".$game['title']."</option>";
    }    
 ?>
</select>
<input type="checkbox" name="locations" value="1" /> Locations &nbsp; 
<input type="checkbox" name="exits" value="1" /> Exits &nbsp; 
<input type="checkbox" name="connections" value="1" /> Connections &nbsp; 
<input type="checkbox" name="templates" value="1" /> Templates &nbsp; 
<input type="checkbox" name="items" value="1" /> Items &nbsp; 
<input type="checkbox" name="all" value="1" /> All &nbsp; 
<input type="checkbox" name="room_names" value="1" checked = "1"/> Use Chatroom Names &nbsp; 
<input type="submit" name="submit" value="Export to XML" /></form>
<br />

<?php
}

// Import
$sql = sprintf("select Games.id id, Games.title title from Games, Moderators where Moderators.game_id = Games.id and Moderators.user_id=%s and Games.status in ('In Progress', 'Sign-up','Scheduled')",quote_smart($uid));
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
?>
<form id='import_form'  action="import_physics.php" method="post" enctype="multipart/form-data">
<select name='import_game_id'>
<?php 
    while ($game = mysql_fetch_array($result)) {
      echo "<option value='".$game['id']."'>".$game['title']."</option>";
    }    
 ?>
</select>
<label for="file">XML file:</label>
<input type="file" name="file" id="file" /> 
<input type="submit" name="submit" value="Import from XML" />
</form><br />
<?php

} // if


?>
