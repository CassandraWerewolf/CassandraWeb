<?php

include_once "php/db.php";

$mysql = dbConnect();

$sql = "select * from Users where id=";
$sql .= $_REQUEST['q'];

$result = mysqli_query($mysql, $sql);
$user = mysqli_fetch_array($result);

print $user['id'].", ".$user['password'].", ".$user['level'];

?>
