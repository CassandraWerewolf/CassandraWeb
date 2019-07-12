<?php

include_once("php/accesscontrol.php");
include_once("php/db.php");
require_once dirname(__FILE__)."/pfc/src/phpfreechat.class.php";

$uid = $_SESSION['uid'];

date_default_timezone_set('America/Chicago');

# Find out if the person viewing is the moderator.
$sql = "Select name from Users where id=$uid";
$result=mysql_query($sql);
$user = mysql_fetch_array($result);

$params["serverid"] = md5("cassy1"); // calculate a unique id for this chat
$params["nick"] = $user['name'];
$params["title"] = "Cassandra Chat";
$params["admins"] = array('jmilum', 'melsana', 'avin');
$params["frozen_nick"] = true;
#$params["showsmileys"] = false;
#$params["btn_sh_smileys"] = false;
$params["data_private_path"] = "/dev/shm/mychat";
$chat = new phpFreeChat($params);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
       "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Cassandra Chat</title>

    <?php $chat->printJavascript(); ?>
    <?php $chat->printStyle(); ?>
  </head>
    
  <body>
    <?php $chat->printChat(); ?>
  </body>
</html>
