<?php

include_once "../setup.php";
include_once ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/php/db.php";

$mysql = dbConnect();

checkLevel($level,1);

$game_id = $_REQUEST['game_id'];
$action = $_REQUEST['action'];

$sql = sprintf("select * from CC_info where game_id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) != 1 ) {
  error("Game doesn't have a CC_info entry.");
}
$CC_info = mysqli_fetch_array($result);

if ( $action == "accept" ) {
  $sql = sprintf("update CC_info set user_id=%s, claim_time=now(), challenger_id=null, type_error=null, desc_error=null where game_id=%s",quote_smart($CC_info['challenger_id']),quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  error ("Game Challenge has been accepted.");
} else if ( $action == "deny" ) {
  $sql = sprintf("update CC_info set challenger_id=null, type_error=null, desc_error=null where game_id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  error ("Game Challenge has been denied.");
} else {
  error("Invalid Action.");
}

?>
