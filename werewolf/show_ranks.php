<?php

include_once("php/db.php");
include_once "menu.php";
include_once("php/common.php");

dbConnect();

$site = '';
?>
<html>
<head>
<title>BGG Werewolf Player and Moderator Ranks</title>
<link rel='stylesheet' type='text/css' href='<?=$site;?>/assets/css/application.css'>
</head>
<body>
<?php display_menu(); ?>
<h1>BGG Werewolf Player and Modertaor Ranks</h1>
<br />
<table cellspacing=10>
<tr valign=top><td>
<table class='forum_table' cellpadding='2'>
<tr><th colspan=3>Player Ranks</th></tr>
<tr><th>Name</th><th>Total</th><th>Rank</th></tr>
<?php
$sql = "SELECT name, games_played, rank FROM Users_game_ranks ORDER BY rank, name";
$result = mysql_query($sql);
while ( $player = mysql_fetch_array($result) ) {
print "<tr></td><td>".get_player_page($player['name'])."</td><td><a href='$site/player/".$player['name']."/games_played'>".$player['games_played']."</a></td><td>".$player['rank']."</td></tr>";
}
mysql_free_result($result);
?>
</table>
</td>
<td>
<table class='forum_table' cellpadding='2'>
<tr><th colspan=3>Moderator Ranks</th></tr>
<tr><th>Name</th><th>Total</th><th>Rank</th></tr>
<?php
$sql = "SELECT name, games_moderated, rank FROM Users_modded_ranks ORDER BY rank, name";
$result = mysql_query($sql);
while ( $player = mysql_fetch_array($result) ) {
print "<tr></td><td>".get_player_page($player['name'])."</td><td><a href='$site/player/".$player['name']."/games_modded'>".$player['games_moderated']."</a></td><td>".$player['rank']."</td></tr>";
}
mysql_free_result($result);
?>
</table>
</td>
</table>
</body>
</html>
