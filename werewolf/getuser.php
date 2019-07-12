<?php

include_once "php/db.php";

dbConnect();

$sql = "select * from Users where id=";
$sql .= $_REQUEST['q'];

$result = mysql_query($sql);
$user = mysql_fetch_array($result);

print $user['id'].", ".$user['password'].", ".$user['level'];

?>
