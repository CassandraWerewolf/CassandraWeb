<?php

include_once "../php/accesscontrol.php";
checkLevel($level,1);

include_once "../php/db.php";
dbConnect();

include_once "../menu.php";

$site = "";
$sql = "select id, name, level from Users where level < 4 order by level, name";
$result = mysql_query($sql);
?>
<html>
<head>
<title>Cassandra Priviledge Users</title>
<link rel='stylesheet' type='text/css' href='../assets/css/application.css'>
</head>
<body>
<?php display_menu();?>
<h1>Cassandra Priviledge Users</h1>
<table class='forum_table'>
<tr><th>User ID</th><th>User</th><th>Level</th></tr>
<?php
while ( $user = mysql_fetch_array($result) ) {
  print "<tr><td>".$user['id']."</td>";
  print "<td><a href='$site/player/".$user['name']."'>".$user['name']."</a></td>\n";
  print "<td>".$user['level']."</td>";
  print "</tr>\n";
}
?>
</table>
</body>
</html>
