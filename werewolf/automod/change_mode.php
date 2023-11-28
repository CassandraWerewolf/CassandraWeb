<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";

$mysql = dbConnect();

$template_id = $_REQUEST['template_id'];
$mode = $_REQUEST['mode'];

# Test that person changing mode is allowed.
if ( $level != 1 ) {
  $sql = sprintf("select owner_id from AM_template where id=%s",quote_smart($template_id));
  $result = mysqli_query($mysql, $sql);
  if ( $uid != mysqli_result($result,0,0) ) {
    error("You do not have permision to change the Mode of this game.");
  }
}

# If the game is being changed to Edit mode, make sure there are no currently running games using this template_id

if ( $mode == "Edit" ) {
  $sql = sprintf("select count(*) from Games where automod_id=%s and status != 'Finished'",quote_smart($template_id));
  $result = mysqli_query($mysql, $sql);
  if ( mysqli_result($result,0,0) != 0 ) {
    error("You can not move this template into edit mode while there are unfinished games using this template.");
  }
}

# Make sure the number of players in the AM_template table is less than the  number in the AM_roles table.

$sql = sprintf("select num_players, num_player_sets from AM_template where id=%s",quote_smart($template_id));
$result = mysqli_query($mysql, $sql);
$template_num_players = mysqli_result($result,0,0);
$template_num_player_sets = mysqli_result($result,0,1);
$sets = explode(",",$template_num_player_sets);
$count = 0;
for ($i=0;$i<count($sets);$i++) { $count += $sets[$i]; }
if ( $template_num_players != $count ) {
  error("You need to have at same number of player in your sets as you have in total."); 
}
$sql = sprintf("select count(*) from AM_roles where template_id=%s",quote_smart($template_id));
$result = mysqli_query($mysql, $sql);
$roles_num_players = mysqli_result($result,0,0);

if ( $template_num_players > $roles_num_players ) {
  error("You need to have at least the number of roles that you have players."); 
}

$file = "rulesets/${template_id}_ruleset.txt";
if ( ! file_exists($file) && $mode != "Edit" ) {
  error("You need to have a ruleset created before you can move this game out of Edit mode.");
}

$sql = sprintf("update AM_template set mode=%s where id=%s",quote_smart($mode),quote_smart($template_id));
$result = mysqli_query($mysql, $sql);

error("Your Mode has been changed.");


?>
