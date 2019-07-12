<?php

include_once("php/accesscontrol.php");
include_once("php/db.php");

dbConnect();

checkLevel($level,2);

$game_id = $_REQUEST['game_id'];

$sql = sprintf("select * from CC_info where game_id=%s",quote_smart($game_id));
$result = mysql_query($sql);
if ( mysql_num_rows($result) != 0 ) {
  error("This game has already been claimed.");
}

$sql = sprintf("insert into CC_info (game_id, user_id, claim_time) values(%s,%s, now())",quote_smart($game_id),quote_smart($uid));
$result = mysql_query($sql);

error("You now have 72hrs to finish completing this game before the other players can report errors.");
?>

