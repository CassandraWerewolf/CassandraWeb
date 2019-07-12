<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "HTML/Table.php";
include_once "menu.php";

dbConnect();

?>
<html>
<head>
<title>Wolfy Awards</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu(); ?>
<h1>Wolfy Awards</h2>
<?php
$sql = "select distinct year from Wolfy_games order by year";
$result = mysql_query($sql);

while ( $year = mysql_fetch_array($result) ) {
  $player_awards[] = $year[0];
  $player_awards[] = "Player Awards";
  $player_names[] = $year[0];
  $player_names[] = "Player Awards";
  $game_awards[] = $year[0];
  $game_awards[] = "Game Awards";
  $game_names[] = $year[0];
  $game_names[] = "Game Awards";
  
  $sql_players = sprintf("select award, name, award_post from Wolfy_players, Wolfy_awards, Users where Wolfy_players.award_id=Wolfy_awards.id and Wolfy_players.user_id = Users.id and year=%s order by IF(Wolfy_awards.id=33, 100, Wolfy_awards.id)",$year[0]);
  $result_players = mysql_query($sql_players);
  while ( $players = mysql_fetch_array($result_players) ) {
    $player_awards[] = "<a href='http://www.boardgamegeek.com/article/".$players['award_post']."#".$players['award_post']."'>".$players['award']."</a>\n";
	$player_names[] = "<a href='/player/".$players['name']."'>".$players['name']."</a>\n";
  }

  $sql_games = sprintf("select award, title, award_post, thread_id, Games.id from Wolfy_games, Wolfy_awards, Games where Wolfy_games.award_id=Wolfy_awards.id and Wolfy_games.game_id = Games.id and year=%s order by IF(Wolfy_awards.id=18, 100, Wolfy_awards.id)",$year[0]);
  $result_games = mysql_query($sql_games);
  while ( $games = mysql_fetch_array($result_games) ) {
    $game_awards[] = "<a href='http://www.boardgamegeek.com/article/".$games['award_post']."#".$games['award_post']."'>".$games['award']."</a>\n";
	$sql_m = sprintf("select name from Users, Moderators where Users.id=Moderators.user_id and game_id=%s",$games['id']);
	$result_m = mysql_query($sql_m);
	$mod_num = mysql_num_rows($result_m);
	$count = 0;
	$modlist = "";
	while ( $mod = mysql_fetch_array($result_m) ) {
      if ( $count == 0  ) $modlist = "(";
	  if ( $count != 0  ) $modlist .= ", ";
	  $modlist .= "<a href='/player/".$mod['name']."'>".$mod['name']."</a>";
	  $count++;
	  if ( $count == $mod_num ) $modlist .= ")";
	}
	$game_names[] = "<a href='/game/".$games['thread_id']."'>".$games['title']."</a> $modlist\n";
  }
  
  $table =& new HTML_Table("class='forum_table'");
  $table->addCol($player_awards);
  $table->addCol($player_names);
  $table->addCol($game_awards);
  $table->addCol($game_names);

  $table->setHeaderContents(0,0,$year[0]);

  $table->setHeaderContents(1,0,"Player Awards");
  $table->setHeaderContents(1,2,"Game Awards");

  $table->setCellAttributes(0,0,"colspan='4'");
  $table->setCellAttributes(1,0,"colspan='2'");
  $table->setCellAttributes(1,2,"colspan='2'");

  echo $table->toHTML();
  echo "<br />";

  unset($player_awards);
  unset($player_names);
  unset($game_awards);
  unset($game_names);
}

?>
</body>
</html>
