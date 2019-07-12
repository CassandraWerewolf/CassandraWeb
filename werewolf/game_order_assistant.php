<?php

include "php/accesscontrol.php";
include_once "php/db.php";

dbConnect();

$game_id = $_REQUEST['game_id'];

# Verify user accessing this page is the Moderator.
if ( ! is_moderator($uid,$game_id) ) {
  error("You must be the game's moderator ($uid) to access this page ($game_id).");
}

if ( isset($_POST['submit']) ) {
  $sql = sprintf("update Games set game_order='on' where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $sql = sprintf("select * from Players where game_id=%s",quote_smart($game_id));
  $result=mysql_query($sql);
  while ( $player = mysql_fetch_array($result) ) {
    $sql_update = sprintf("update Players set game_action=%s, ga_desc=%s, ga_text=%s, ga_group=%s where user_id=%s and game_id=%s",quote_smart($_POST['na_'.$player['user_id']]),quote_smart($_POST['desc_'.$player['user_id']]),quote_smart($_POST['text_'.$player['user_id']]),quote_smart($_POST['group_'.$player['user_id']]),quote_smart($player['user_id']),quote_smart($game_id));
	$result_update = mysql_query($sql_update);
  }
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

print display_goa($game_id);

function display_goa($game_id) {
$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$game = mysql_fetch_array($result);

$output = "<html>";
$output .= "<head>";
$output .= "<title>Game Order Assistant - Activation</title>";
$output .= "</head>";
$output .= "<body>";
$output .= "<div align='left'>";
$output .= "<p>Please choose the type of players that the player gets to choose from for their game actions (all actions see the same list) and if they get a user defined field:</p>";
$output .= "<ul><li>none - no player list</li>";
$output .= "<li>alive - can only choose from living players</li>";
$output .= "<li>dead - can only choose from dead players</li>";
$output .= "<li>all - can choose any player.</li>";
$output .= "<li>checkbox - can input a player defined value.</ul>";
$output .= "<p>The Order description should fit in to the phrase 'Player: _______ Player'. Put commas between words to give the player more than one game order possibility.</p>";
$output .= "<p>You can group players together so that they can see eachother's orders.  This is needed for wolves and maybe masons.  To do this just give them all the same group name.  Make sure you also give them all the same action description.  If they are not part of a group leave it blank.</p>";
$output .= "</div>";
$output .= "<form name='game_orders' method='post' action='".$_SERVER['PHP_SELF']."'>";
$output .= "<input type='hidden' name='game_id' value='$game_id' />";
$output .= "<table class='forum_table'>";
$output .= "<tr><th>Player</th><th>Order Description</th><th>Game Order Choices</th><th>Group Name</th></tr>";
$sql = sprintf("select * from Players, Users where Players.user_id=Users.id and game_id=%s order by name",quote_smart($game_id));
$result = mysql_query($sql);
while ( $player = mysql_fetch_array($result) ) {
  $output .= "<tr>";
  $output .= "<td>".$player['name']."</td>";
  $output .= "<td><input type='text' width='50' id='desc_".$player['user_id']."' name='desc_".$player['user_id']."' value='".$player['ga_desc']."' />";
  $output .= "<td align='center'>";
  $output .= na_dropdown($game_id,$player['user_id']);
  $checked = "";
  if ( $player['ga_text'] != "" ) { $checked = "checked = 'checked'"; }
  $output .= "<input type='checkbox' name='text_".$player['user_id']."' id='text_".$player['user_id']."' $checked />";
  $output .= "</td>";
  $output .= "<td><input type='text' width='50' id='group_".$player['user_id']."' name='group_".$player['user_id']."' value='".$player['ga_group']."' />";
  $output .= "</tr>";
}
$output .= "<tr><td align='center' colspan='4'><input type='submit' name='submit' value='submit' /></td></tr>";
$output .= "</table>";
$output .= "</form>";
$output .= "<span align='right'><a href='javascript:close(\"control_space\")'>[close]</a></span></p>";
$output .= "</body>";
$output .= "</html>";

return $output;
}

function na_dropdown($game_id,$player_id) {
  $output .= "<select name='na_$player_id'>\n";
  $sql = sprintf("select game_action from Players where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($player_id));
  $result = mysql_query($sql);
  $na = mysql_result($result,0,0);
  $sql="show columns from Players where field='game_action'";
  $result=mysql_query($sql);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
	  $selected = "";
	  if ( $na == $v ) { $selected = "selected"; }
	  $output .= "<option $selected value='$v'>$v</option>";
    }	
  }
  $output .= "</select>\n";

  return $output;
}
?>
