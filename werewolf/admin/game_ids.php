<?php

include_once "../php/accesscontrol.php";
checkLevel($level,1);

include_once "../php/db.php";
dbConnect();

include_once "../menu.php";

$sql = "select id, concat(if(number,number,'*'),') ',title) as name, thread_id from Games order by id";
$result = mysql_query($sql);
?>
<html>
<head>
<title>Cassandra Game ID's</title>
<link rel='stylesheet' type='text/css' href='../bgg.css'>
</head>
<body>
<?php display_menu();?>
<h1>Cassandra Game ID's</h1>
<table class='forum_table'>
<tr><th>Game ID</th><th>Title</th></tr>
<?php
while ( $game = mysql_fetch_array($result) ) {
  print "<tr><td>".$game['id']."</td>";
  print "<td><a href='/game/".$game['thread_id']."'>".$game['name']."</a></td></tr>\n";
}
?>
</table>
</body>
</html>
