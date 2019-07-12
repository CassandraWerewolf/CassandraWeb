<?php
    include "php/accesscontrol.php";
	include_once "php/db.php";
	include_once "menu.php";
	require_once 'HTML/Table.php';

	$site = "";
	$game = "/game/";
?>
<html>
<head>
<title>Cassandra Files Listing</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu(); ?>
<h1>Cassandra Files Listing</h1>
<?php
	dbConnect();

	$sql = "SELECT Games.id, number, thread_id, title, last_dumped, status, deadline_speed FROM Post_collect_slots, Games WHERE Post_collect_slots.game_id=Games.id order by number;";

	$attrs = array(
		'border' => '0',
		'cellpadding' => '4',
		'class' => 'forum_table'
	);

	$table =& new HTML_Table($attrs);
    if ( $level == 1 ) {
	  $table->addRow(array("ID","Game", "Speed", "Last Dumped")); 
    } else {
	  $table->addRow(array("Game", "Speed", "Last Dumped")); 
    }
	$table->setRowType(0,"TH");

	$res = dbGetResult($sql);
	while($row = mysql_fetch_array($res))
	{
		$number = ($row['number']) ? $row['number']:"*";
        if ( $level == 1 ) {
		  $table->addRow(array($row['id'],"<a href='$game".$row['thread_id']."'>".$number.") ".$row['title']."</a>", $row['deadline_speed'],$row['last_dumped'])); 
        } else {
		  $table->addRow(array("<a href='$game".$row['thread_id']."'>".$number.") ".$row['title']."</a>", $row['deadline_speed'],$row['last_dumped'])); 
        }
	}
	mysql_free_result($res);

	echo $table->toHTML();
	print "<br />* Denotes a Subthread"; 
?>
</body>
</html>
