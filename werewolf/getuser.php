<?php

include_once "php/db.php";

dbConnect();

$sql = sprintf("select * from Users where id=%s",quote_smart($_REQUEST['q']));

$result = mysql_query($sql);
$user = mysql_fetch_array($result);

print $user['id'].", ".$user['password'].", ".$user['level'];
//why does this exist???? I know it's encrypted but MD5 isn't a great hash and could potentially be decrypted. Google it
?>
