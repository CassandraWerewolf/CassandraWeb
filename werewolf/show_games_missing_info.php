<?php
	include_once "php/db.php";
	include_once "php/accesscontrol.php";
	include_once "menu.php";
	require_once 'HTML/Table.php';

	$game = "/game/";

?>
<html>
<head>
<title>Games</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu(); ?>
<?php
	dbConnect();
	
		$sql_games = "SELECT u.name as mod_name, CONCAT(g.number, ') ', g.title) as title, g.thread_id FROM Games g, Players p, Moderators m, Users u where p.game_id = g.id and (p.role_id = 1 or p.side is null) and m.game_id = g.id and m.user_id = u.id and g.status = 'Finished' and g.winner != 'Other' group by g.number ORDER BY number;";


	$res = dbGetResult($sql_games);
	$count = dbGetResultRowCount($res);

	$title = "Finished Games With Missing Role Info ($count)";
	echo "<h1>" . $title . "</h1>\n";
	$games_played[] = "Game - Moderator";

	while($row = mysql_fetch_array($res)){
		$games_played[] = "<a href='$game".$row['thread_id']."'>".$row['title']."</a> - ".$row['mod_name'];
	}
	mysql_free_result($res);

	$attrs = array(
		'border' => '0',
		'cellpadding' => '4',
		'class' => 'forum_table'
	);

	$table =& new HTML_Table($attrs);
	$table->addCol($games_played);
	$table->setRowType(0,"TH");
	echo $table->toHTML();

?>
</body>
</html>
