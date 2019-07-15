<?php

include_once("php/db.php");
include_once "menu.php";
include_once "php/common.php";

dbConnect();

$site = '';
?>
<html>
<head>
<title>BGG Werewolf Active Players and Moderators</title>
<link rel='stylesheet' type='text/css' href='<?=$site;?>/assets/css/application.css'>
</head>
<body>
<?php display_menu(); ?>
<h1>BGG Werewolf Active Players and Moderators</h1>
These users are currently playing or moderating a running game.
<br />
<table cellspacing=10>
<tr valign=top><td>
<table class='forum_table' cellpadding='2'>
<?php
$sql = "SELECT name FROM Players_active ORDER BY name";
$result = dbGetResult($sql);
$total = dbGetResultRowCount($result);
echo "<tr><th colspan=3>Players ($total)</th></tr>";
while ( $player = mysql_fetch_array($result) ) {
print "<tr></td><td>";
print get_player_page($player['name']);
print "</td></tr>";
}
mysql_free_result($result);
?>
</table>
</td>
<td>
<table class='forum_table' cellpadding='2'>
<?php
$sql = "SELECT name FROM Moderators_active ORDER BY name";
$result = dbGetResult($sql);
$total = dbGetResultRowCount($result);
echo "<tr><th colspan=3>Moderators ($total)</th></tr>";
while ( $player = mysql_fetch_array($result) ) {
print "<tr></td><td>";
print get_player_page($player['name']);
print "</td></tr>";
}
mysql_free_result($result);
?>
</table>
</td>
</table>
</body>
</html>
