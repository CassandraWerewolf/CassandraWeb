<?php
include_once "../../setup.php";
include_once ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/php/db.php";

dbConnect();

if ( $uid != 200 && $uid != 18  && $uid != 58) {
  error("You can not access this page");
}

if ( isset($_POST['submit']) ){
  $sql = sprintf("update Chat_users, Chat_rooms set Chat_users.open=%s where Chat_users.room_id=Chat_rooms.id and game_id=514 and name like %s  and user_id != 200",quote_smart($_POST['open']),quote_smart('Player chat%'));
  #print $sql;
  $result = mysql_query($sql);
  error("The times have changed, you can now return to the game page.");
}

?>
<html>
<head>
<title>Beowulf's page</title>
</head>
<body>
<form action='<?=$_SERVER['PHP_SELF'];?>' method='post'>
New Time Stamp: <input type='text' name='open' /> <input type='submit' name='submit' value='Submit' />
</form>
</body>
</html>
