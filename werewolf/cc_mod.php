<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";

dbConnect();

checkLevel($level,1);

$game_id = $_REQUEST['game_id'];
$action = $_REQUEST['action'];

$sql = sprintf("select * from CC_info where game_id=%s",quote_smart($game_id));
$result = mysql_query($sql);
if ( mysql_num_rows($result) != 1 ) {
  error("Game doesn't have a CC_info entry.");
}
$CC_info = mysql_fetch_array($result);

if ( $action == "accept" ) {
  $sql = sprintf("update CC_info set user_id=%s, claim_time=now(), challenger_id=null, type_error=null, desc_error=null where game_id=%s",quote_smart($CC_info['challenger_id']),quote_smart($game_id));
  $result = mysql_query($sql);
  error ("Game Challenge has been accepted.");
} else if ( $action == "deny" ) {
  $sql = sprintf("update CC_info set challenger_id=null, type_error=null, desc_error=null where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  error ("Game Challenge has been denied.");
} else {
  error("Invalid Action.");
}

?>
