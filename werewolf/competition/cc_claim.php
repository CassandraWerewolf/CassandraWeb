<?php

include_once "../setup.php";
include_once(ROOT_PATH . "/php/accesscontrol.php");
include_once(ROOT_PATH . "/php/db.php");

$mysql = dbConnect();

checkLevel($level,2);

$game_id = $_REQUEST['game_id'];

$sql = sprintf("select * from CC_info where game_id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) != 0 ) {
  error("This game has already been claimed.");
}

$sql = sprintf("insert into CC_info (game_id, user_id, claim_time) values(%s,%s, now())",quote_smart($game_id),quote_smart($uid));
$result = mysqli_query($mysql, $sql);

error("You now have 72hrs to finish completing this game before the other players can report errors.");
?>

