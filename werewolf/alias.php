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
  $sql = sprintf("update Games set alias_display=%s, vote_by_alias=%s, phys_by_alias=%s where id=%s",
	quote_smart($_POST['alias_display']), quote_smart($_POST['vote_by_alias'] ? 'Yes' : 'No'), quote_smart($_POST['phys_by_alias'] ? 'Yes' : 'No'),
	quote_smart($game_id));
  $result = mysql_query($sql);
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

print display_alias_settings($game_id);

function display_alias_settings($game_id) {
$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$game = mysql_fetch_array($result);

$output = "<html>";
$output .= "<head>";
$output .= "<title>Alias Assistant - Configuration</title>";
$output .= "</head>";
$output .= "<body>";
$output .= "<div align='left'>";
$output .= "<form name='alias_opt' method='post' action='".$_SERVER['PHP_SELF']."'>";
$output .= "<input type='hidden' name='game_id' value='$game_id' />";
$output .= "<p> Display options:";
$output .= "<select name='alias_display'>";
$output .= "<option ".($game['alias_display'] == 'None' ? 'selected' : '') ." value='None'>No Aliases</option>";
$output .= "<option ".($game['alias_display'] == 'Private' ? 'selected' : '') ." value='Private'>Private</option>";
$output .= "<option ".($game['alias_display'] == 'Public' ? 'selected' : '') ." value='Public'>Public</option>";
$output .= "</select></p>";
$output .= "<p><input type='checkbox' name='vote_by_alias' id='vote_by_alias' ".($game['vote_by_alias'] == 'Yes' ? 'checked' : '') ." />";
$output .= " Use for Voting";
$output .= "\t<input type='checkbox' name='phys_by_alias' id='phys_by_alias' ".($game['phys_by_alias'] == 'Yes' ? 'checked' : '') ." />";
$output .= " Use for Physics</p>";
$output .= "<p><input type='submit' name='submit' value='submit' /></p>";
$output .= "</form>";
$output .= "<span align='right'><a href='javascript:close(\"control_space\")'>[close]</a></span></p>";
$output .= "</body>";
$output .= "</html>";

return $output;
}

?>
