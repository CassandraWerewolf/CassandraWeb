<?php

include_once("php/db.php");
include_once "php/accesscontrol.php";
include_once("menu.php");
include_once("php/common.php");

dbConnect();

$site = '';

?>
<html>
<head>
<title>BGG Werewolf Game Stats</title>
<link rel='stylesheet' type='text/css' href='<?=$site;?>/assets/css/application.css'>
<script language='javascript'>
<!--
function view_player() {
index = document.view.player.selectedIndex
player = document.view.player.options[index].value
location.href = "player/"+player
}
function view_game() {
index = document.view.game.selectedIndex
game = document.view.game.options[index].value
location.href = "game/"+game
}
//-->
</script>
</head>
<body>
<?php display_menu() ?>
<center>
<h1>BGG Werewolf Game Ranks</h1>


<table border='0' cellspacing=20>
<tr valign=top>
<td>

<table width='100%' class='forum_table' cellpadding='2'>
<tr>
<th colspan=3><a href='<?=$site;?>/show_ranks.php'>Player Ranks</a></th></tr>
<tr>
<th>Name</th><th>Total</th><th>Rank</th></tr>
<?php
$sql = "SELECT name, games_played, rank FROM Users_game_ranks WHERE rank <= 25 ORDER BY rank, name";
$result = mysql_query($sql);
while ( $player = mysql_fetch_array($result) ) {
print "<tr></td><td>";
print get_player_page($player['name']);
print "</td><td><a href='$site/player/".$player['name']."/games_played'>".$player['games_played']."</a></td><td>".$player['rank']."</td></tr>";
}
?>
</table></td>

<td>
<table width='100%' class='forum_table' cellpadding='2'>
        <tr><th colspan=3><a href='<?=$site;?>/show_ranks.php'>Moderator Ranks</a></th></tr>
        <tr><th>Name</th><th>Total</th><th>Rank</th></tr>
<?php
$sql = "SELECT name, games_moderated, rank FROM Users_modded_ranks WHERE rank <= 25 ORDER BY rank, name";
$result = mysql_query($sql);
while ( $player = mysql_fetch_array($result) ) {
print "<tr></td><td>";
print get_player_page($player['name']);
print "</td><td><a href='$site/player/".$player['name']."/games_modded'>".$player['games_moderated']."</a></td><td>".$player['rank']."</td></tr>";
}
?>
</table></td>
</tr></table></td>
</body></html>
