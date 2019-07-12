<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";

dbConnect();

?>
<html>
<head>
<title>My Stuff: <?=$username;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu();?>
<h1>My Stuff: <?=$username;?></h1>
<?php
$status_types = array("Sign-up", "Scheduled", "Unknown");
foreach ( $status_types as $status ) {
?>
<h2>Games <?=$status;?></h2>
<ul>
<?php
  $sql = sprintf("select * from Games, Moderators where Games.id=Moderators.game_id and user_id=%s and status=%s",quote_smart($uid),quote_smart($status));
  $result = mysql_query($sql);
  while ( $game = mysql_fetch_array($result) ) {
    print "<li>".$game['title']." (Start Date: ".$game['start_date'].")<br /><a href='/schedule_a_game.php?game_id=".$game['id']."'>[edit]</a>";
	if($status != 'Sign-up'){
		print "<a href='/move_to_signup.php?game_id=".$game['id'].">[move to sign-up mode]</a>";
	}
print "<a href='/delete_game.php?game_id=".$game['id']."'>[delete]</a> </li>\n";
  }
?>
</ul>
<?php
}
?>
</body>
</html>
