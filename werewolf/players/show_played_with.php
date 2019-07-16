<?php

include_once "../setup.php";
include ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/php/db.php";
include_once ROOT_PATH . "/php/common.php";
include_once ROOT_PATH . "/menu.php";

dbConnect();

$here = "/";
$pagename = "show_player_stats.php";
$game = "/game/";

$player = $_REQUEST['player'];
if ( $player == "" ) {
?>
<html>
<head>
<script language='javascript'>
<!--
window.history.back();
//-->
</script>
</head>
<body>
Please hit your browsers back button.
</body>
</html>
<?php
}

$sql = sprintf("select id from Users where name=%s",quote_smart($player));
$result = mysql_query($sql);
if ( mysql_num_rows($result) != 0 ) {
   $user_id = mysql_result($result,0,0);
} else {
   $user_id = 0;
}

?>
<html>
<head>
<title>Player Stats for <?=$player;?></title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu(); ?>
<div style='padding:10px'>
<h1><?=$player;?> has played with...</h1>
<?php
$player2 = $_REQUEST['player2'];
if ( $player2 != "" ) {
  $sql = sprintf("select id from Users where name=%s",quote_smart($player2));
  $result = mysql_query($sql);
  if ( mysql_num_rows($result) != 0 ) {
     $user_id2 = mysql_result($result,0,0);
  } else {
     $user_id2 = 0;
  }
  print "<h2>$player2 in</h2>\n";
  print "<table class='forum_table'>\n";
  print "<tr><th>Game</th><Number of games Played Together</th></tr>\n";
  $sql = sprintf("select game_id from Players_all, Games where Players_all.game_id=Games.id and user_id=%s and game_id in (select game_id from Players_all where user_id=%s ) and status='Finished' order by number",quote_smart($user_id),quote_smart($user_id2));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    print "<tr><td>";
    print get_game($row['game_id'],'num, title');
    print "</td></tr>\n";
  }
} else {
  print "<table class='forum_table'>\n";
  print "<tr><th>Player</th><Number of games Played Together</th></tr>\n";
  $sql = sprintf("select u.id, u.name, count(*) as num from Players_all p, Users u where p.user_id=u.id and u.id!=%s and p.game_id in (select g.id from Players p, Games g where p.game_id=g.id and g.status='Finished' and p.user_id=%s union select g.id from Replacements r, Games g where r.game_id=g.id and g.status='Finished' and r.replace_id=%s) group by u.id order by num desc",quote_smart($user_id),quote_smart($user_id),quote_smart($user_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    print "<tr><td>";
    print get_player_page($row['name']);
    print "</td><td><a href='/player/$player/with/".$row['name']."'>".$row['num']."</a></td></tr>\n";
  }
}
?>
</table>
</div>
</body>
</html>
