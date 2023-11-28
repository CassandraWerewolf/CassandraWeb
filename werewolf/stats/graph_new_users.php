<?php
	include_once "../setup.php";

	require_once(ROOT_PATH . "/php/db.php");
	require_once("jpgraph/jpgraph.php");    
	require_once("jpgraph/jpgraph_bar.php"); 
	require_once("jpgraph/jpgraph_line.php"); 	


	$mysql = dbConnect();

	$res = dbGetResult("SELECT DATE_FORMAT(start_month, '%b-%y') as month, DATE_FORMAT(start_month, '%y%m') AS sort_month, count(*) AS total FROM Users_start_month GROUP BY sort_month ORDER BY sort_month");

	while($row = mysqli_fetch_array($res)){
		$month[] = $row['month'];
		$total[] = $row['total'];
	}

	$graph = new Graph(750,350,"auto"); 
	$graph->SetScale("textint"); 
	$graph->img->SetMargin(50,30,50,50); 
	$graph->SetShadow(); 
	$graph->title->Set("New Users by Month");
	#$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);

	$graph->xaxis->SetTickLabels($month); 

	$line1 = new LinePlot($total); 
	$line1->mark->SetType(MARK_CIRCLE);  
	$line1->SetColor('darkolivegreen');  
	$line1->SetWeight(3);  
	$line1->SetCenter();

	$graph->Add($line1); 
	$graph->Stroke(); ?> 

?>
