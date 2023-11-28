<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "configure_physics_functions.php";
//include_once "menu.php";

$mysql = dbConnect();
#upgrading();

$game_id = $_REQUEST['export_game_id'];

//Make sure the person viewing this page is a moderator of the game.
$sql = sprintf("select * from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),$uid);
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) != 1 ) {
	error("You must be the moderator of the game in order to export the Physics System.");
}

$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
$game = mysqli_fetch_array($result);

header('Content-type: text/xml');
header('Content-Disposition: attachment; filename='.$game['title'].'.xml');
header('Pragma: no-cache');

echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>".PHP_EOL;
echo "<gamedata>".PHP_EOL;
if ($_REQUEST['room_names']) { $room_names = get_room_names_by_id($game_id); }
if ($_REQUEST['locations'] || $_REQUEST['all']) 
 { echo xml_export_locs($game_id); }
if ($_REQUEST['exits'] || $_REQUEST['all']) 
 { echo xml_export_exits($game_id); }
if ($_REQUEST['connections'] || $_REQUEST['all']) 
 { echo xml_export_connections($game_id); }
if ($_REQUEST['templates'] || $_REQUEST['all']) 
 { echo xml_export_templates($game_id); }
if ($_REQUEST['items'] || $_REQUEST['all']) 
 { echo xml_export_items($game_id); }
if ($_REQUEST['triggers']) 
 { echo xml_export_triggers($game_id); }
echo "</gamedata>"; 
?>
