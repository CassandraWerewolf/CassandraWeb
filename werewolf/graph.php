<?php
	require_once("php/db.php");
	require_once("jpgraph/jpgraph.php");    
	require_once("jpgraph/jpgraph_bar.php"); 
	require_once("jpgraph/jpgraph_line.php"); 	


	#$player = $_REQUEST['player']; 
	$player = 'jmilum';

	dbConnect();

	$total_games = mysql_query("SELECT DATE_FORMAT(start_date, '%y%m') AS month, count(*) AS total FROM Games GROUP BY month ORDER BY month");
	if (!$total_games){
		die('Could not query:' . mysql_error());
	}

	$player_games = mysql_query("SELECT DATE_FORMAT(start_date, '%y%m') AS month, count(*) AS total FROM Games,Players,Users WHERE Users.name = '$player' AND Users.id=Players.user_id AND Games.id=Players.game_id GROUP BY month ORDER BY month");
	if (!$player_games){
		die('Could not query:' . mysql_error());
	}

	while($row = mysql_fetch_array($total_games)){
		$month[] = $row['month'];
		$total[] = $row['total'];
	}

	while($row = mysql_fetch_array($player_games)){
		$player_month[] = $row['month'];
		$player_total[] = $row['total'];
	}

	$graph = new Graph(750,350,"auto"); 
	$graph->SetScale("textint"); 
	$graph->img->SetMargin(50,30,50,50); 
	$graph->SetShadow(); 
	$graph->title->Set("Games Started by Month");
	$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);

	$graph->xaxis->SetTickLabels($month); 


	$bplot = new BarPlot($player_total); 
	$bplot->SetFillColor("lightgreen"); // Fill color 
	$bplot->value->Show(); 
	$bplot->value->SetColor("black","navy"); 

	$line1 = new LinePlot($total); 
	$line1->mark->SetType(MARK_CIRCLE);  
	$line1->SetColor('darkolivegreen');  
	$line1->SetWeight(3);  
	$line1->SetCenter();

	$graph->Add($bplot); 
	$graph->Add($line1); 
	$graph->Stroke(); ?> 

?>
