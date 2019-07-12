<?php
	include_once "php/accesscontrol.php";
	include_once "php/db.php";
	include_once "menu.php";
	include_once "php/common.php";
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
<div style='padding:10px;'>
<?php
	dbConnect();
	
	$sql_games = "SELECT Games.id, winner FROM Games WHERE status='Finished' ";

	if($_REQUEST['type'] == 'all')
	{
		$sql_games .= " OR status='In Progress'"; 
		$title = "All Games";
	}
	else if($_REQUEST['type'] == 'evil')
	{
		$sql_games .= " AND winner = 'evil'"; 
		$title = "All Games Won by Evil";
	}
	else if($_REQUEST['type'] == 'good')
	{
		$sql_games .= " AND winner = 'good'"; 
		$title = "All Games Won by Good";
	}
	else if($_REQUEST['type'] == 'other')
	{
		$sql_games .= " AND winner = 'other'"; 
		$title = "All Other Type Games";
	}
	else if($_REQUEST['type'] == 'missing_winner')
	{
		$sql_games .= " AND winner = ''"; 
		$title = "Finished Games With Missing Winner";
	}
	else if($_REQUEST['type'] == 'missing_roles')
	{
		$sql_games = "SELECT g.id, u.id as mod_id, u.name as mod_name, CONCAT(g.number, ') ', g.title, ' - ', u.name) as title, g.thread_id FROM Games g, Players p, Moderators m, Users u where p.game_id = g.id and (p.role_id = 1 or p.side is null) and m.game_id = g.id and m.user_id = u.id and g.status = 'Finished' and g.winner != 'Other' group by g.number ";

		$title = "Finished Games With Missing Role Info";
	}
	$sql_games .= "order by Games.number";

	echo "<h1>" . $title . "</h1>\n";

	$res = dbGetResult($sql_games);
	$count = dbGetResultRowCount($res);
	$games_played[] = "$title ($count)";
	$winner[] = "Winner";
	while($row = mysql_fetch_array($res)){
		#$games_played[] = get_game($row['id'],"num, complex, in, title, mod, post");
		$games_played[] = get_game($row['id'],"num, complex, title, mod");
		$winner[] = $row['winner'];
	}
	mysql_free_result($res);

	$attrs = array(
		'border' => '0',
		'cellpadding' => '4',
		'class' => 'forum_table'
	);

	$table =& new HTML_Table($attrs);
	$table->addCol($games_played);
	$table->addCol($winner);
	$table->setRowType(0,"TH");
	echo $table->toHTML();

?>
</div>
</body>
</html>
