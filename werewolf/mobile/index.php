<?php

include "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/common.php";
include_once "../menu.php";

checkLevel($level,1);

?>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<head>
<title>BGG Werewolf Stats - Mobile</title>
<link rel='stylesheet' type='text/css' href='mobile.css' />
</head>
<body>
<?php display_mobile_header(); ?>
<div id='content'>
<?php
# Find out if the user is playing in any games
$sql = sprintf("select * from Players, Games where Players.game_id=Games.id and Players.user_id=%s and Games.status = 'In Progress'",quote_smart($uid));
$result = mysql_query($sql);
$num_games = mysql_num_rows($result);
if ( $num_games > 0 ) {
print "<div class='game_list'>\n";
print "Currently Playing <br />\n";
display_game_list($result);
print "</div>\n";
} # if in a game
# Find out if the user is moderating any games
$sql = sprintf("select * from Moderators, Games where Moderators.game_id=Games.id and Moderators.user_id=%s and Games.status = 'In Progress'",quote_smart($uid));
$result = mysql_query($sql);
$num_games = mysql_num_rows($result);
if ( $num_games > 0 ) {
print "<div class='game_list'>\n";
print "Currently Moderating <br />\n";
display_game_list($result);
print "</div>\n";
} # if in a game
?>
</div>
<?php display_mobile_footer(); ?>
</body>
</html>
<?php

function display_game_list($result) {
while ( $game = mysql_fetch_array($result) ) {
?>
<div>
<input type='button' value="<?=$game['title'];?>" />
</div>
<?php
} # while games played
}
?>