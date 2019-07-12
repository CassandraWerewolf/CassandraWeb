<?php

include_once ("autocomplete.php");

function display_menu(){
  global $username;
?>
	
<script language='javascript'>
<!--
function view_menu_player() {
player = document.getElementById("player_name_menu").value
pageType = document.getElementById("page_type").value
if ( pageType == "all_games" ) {
location.href = "/player/"+player+"/games_played"
} else {
location.href = "/"+pageType+"/"+player
}
}
function view_menu_game() {
game = document.getElementById("game_id_menu").value
location.href = "/game/"+game
}
//-->
</script>
<table border='0' width='100%' style='background:#F5F5FF; color:#000000' >
<form name="view_menu">
<tr>
<?php
if ( isset($username) ) {
?>
<td align='center'><b>Welcome <a href='/player/<?=$username;?>'><?=$username;?></a></b><br />
<a href='/logout.php'>Log Out</a></td>
<?php
} else {
?>
<td align='center'><a href='/index.php?login=true'>Log In</a></td>
<?php
}
?>
<td align='center'><a href="/">Cassy Home</a><br />
<a href="http://www.boardgamegeek.com/forum/76/forum/1">BGG WW Forum</a><br />
</td>
<td><table><tr><td>Player:</td><td><?php print player_autocomplete_form("menu"); print player_autocomplete_js("menu")?></td><td><select id="page_type"><option selected value="player">Stats</option><option value='all_games'>Games</option><option value="profile">Profile</option><option value='social/user'>Social</option></select></td><td><a href='javascript:view_menu_player()'>Go</a></td></tr></table>
</td>
<td><table border='0'><tr><td>Game:</td><td><?php print game_autocomplete_form("menu"); print game_autocomplete_js("menu");?></td><td><a href="javascript:view_menu_game()">Go</a></td></tr></table>
</td>
<?php if ( isset($username) ) { ?>
<td><a href="https://discord.gg/ftUvN3k" target="_blank">
<img src="https://img.shields.io/discord/143256979564003328.svg?colorA=8888FF&colorB=d1d1d1" alt="Discord chat">
</a></td>
<?php } ?>
</tr>
</form>
</table>
<!--
<h2 style='color:red'>I'm working on upgrading a feature.  Please excuse any broken items for the next few minutes</h2>
-->
<?php
}

function display_mobile_header(){
  global $username;
?>
  <div class='header'>Welcome <?=$username;?></div>
<?php
}

function display_mobile_footer(){
?>
<div class='footer'>
<a href='/logout.php'><input class='footer' type='button' value='Log Out' /></a>
<a href='http://www.boardgamegeek.com/forum/76/forum/1'><input class='footer' type='button' value='BGG - WW' /></a>
<a href=''><input class='footer' type='button' value='Full Site' /></a>
  </div>
<?php
}
?>
