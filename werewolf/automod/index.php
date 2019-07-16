<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/bgg.php";
include_once "../php/common.php";
include_once "../menu.php";

$cache = init_cache();

dbConnect();

#checkLevel($level,1);

?>
<html>
<head>
<title>AutoMod Templates </title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu(); ?>
<div style='padding:10px;'>
<h1>AutoMod Templates</h1>
<h2><a href="https://docs.google.com/document/d/1yDuiN5w3HvbkDkZn_qsN-vGfRle8OhIUvyGc2yzh6_w/edit">Users Guide</a></h2>
<table class='forum_table'>
<tr><th>Copy</th><th>ID</th><th>Automod Template</th><th>Owner</th><th>Mode</th></tr>
<?php
$sql = sprintf("select * from AM_template order by id");
$result = mysql_query($sql);
while ( $template = mysql_fetch_array($result) ) {
  print "<tr>";
  print "<td><a href='/automod/copy_template.php?template_id=".$template['id']."'><img src='/images/copy.png' border='0'></a></td>";
  print "<td><a href='/automod/template/".$template['id']."'>".$template['id']."</a></td>";
  print "<td><a href='/automod/template/".$template['id']."'>".$template['name']."</a></td>";
  print "<td>".get_player_page($template['owner_id'])."</td>";
  print "<td>".$template['mode']."</td>";
}
?>
</table>
<a href='/automod/new_template.php'>Create a new template</a>
<p><a href='http://boardgamegeek.com/thread/168792'>BGG Forum Discussion</a></p>
</div>
</body>
</html>
