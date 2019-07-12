<?php

include_once "../php/accesscontrol.php";
checkLevel($level,1);

include_once "../php/db.php";
dbConnect();

include_once "../menu.php";

$site = "";
$sql = "select id, name from Users order by name";
$result = mysql_query($sql);
?>
<html>
<head>
<title>Cassandra User ID's</title>
<link rel='stylesheet' type='text/css' href='../bgg.css'>
</head>
<body>
<?php display_menu();?>
<h1>Cassandra User ID's</h1>
<table class='forum_table'>
<tr><th>User ID</th><th>User</th></tr>
<?php
while ( $user = mysql_fetch_array($result) ) {
  print "<tr><td>".$user['id']."</td>";
  print "<td><a href='$site/player/".$user['name']."'>".$user['name']."</a></td>\n";
  print "</tr>\n";
}
?>
</table>
</body>
</html>
