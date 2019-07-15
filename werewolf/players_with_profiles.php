<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
dbConnect();
include "menu.php";

?>
<html>
<head>
<title>Players with Profiles</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu();?>
<h1>Players with Profiles</h1>
<table class='forum_table'>
<?php
$sql = "select name from Bio, Users where Bio.user_id=Users.id order by name";
$result = mysql_query($sql);
while ( $row = mysql_fetch_array($result) ) {
  print "<tr><td><a href='/profile/".$row['name']."'>".$row['name']."</a></td></tr>\n";
}
?>
</table>
</body>
</html>
