<?php

// Allow CORS
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');    
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
}   
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Headers: *");
}

include_once "php/accesscontrol.php";
include_once "php/db.php";

dbConnect();

//checkLevel($level, 1);

$thread_id = $_GET['thread_id'];
$here = "/";
$game_page = "${here}game/";
if ($thread_id == "") {
	exit;
}

$sql = sprintf("select id from Games where thread_id=%s", quote_smart($thread_id));
$result = mysql_query($sql);
if (mysql_num_rows($result) == 1) {
	$game_id = mysql_result($result, 0, 0);
} else {
	exit;
}

$writer = new XMLWriter();

$writer->openURI('php://output'); 
$writer->startDocument('1.0'); 
$writer->setIndent(5); 

$writer->startDtd('votelog', '-//W3C//DTD XHTML 1.0 Transitional//EN', '/votelog.dtd');
$writer->endDtd();

$writer->startElement('votelog'); 
$writer->writeAttribute('game_id', $game_id); 

$sql_days = sprintf("select distinct day from Votes_log where game_id=%s order by day asc", $game_id);
$result_days = mysql_query($sql_days);

while ($day = mysql_fetch_array($result_days)) {
	$writer->startElement('day');
	$writer->writeAttribute('daynum', $day[0]);
	
	$sql_votes = sprintf("select * from Votes_log where game_id=%s and day=%s order by time_stamp", $game_id, $day[0]);
	$result_votes = mysql_query($sql_votes);

	while ($row = mysql_fetch_array($result_votes)) {
		$writer->startElement('vote');
		$writer->writeAttribute('type', $row['type']);
		$writer->writeAttribute('valid', $row['valid']);
		$writer->writeAttribute('edited', $row['edited']);
		$writer->writeElement('voter', $row['voter']);

		if($row['votee']	!= '') {
			$writer->writeElement('votee', $row['votee']);
		}

		$writer->writeElement('timestamp', $row['time_stamp']);
		$writer->writeElement('article_id', $row['article_id']);

		if($row['misc']	!= '') {
			$writer->writeElement('misc', $row['misc']);
		}
	
		$writer->endElement();
	}

	$sql_nonvoters = sprintf("select get_non_voters(%d, %d);", $game_id, $day[0]);
	$res = mysql_query($sql_nonvoters);
	$nonvoters = mysql_result($res, 0, 0);

	if($nonvoters != '') {
		$elements = explode(', ', $nonvoters);
		$writer->startElement('notvoting');
		for ($i=0; $i<count($elements); $i++) {
			$writer->writeElement('notvoter', $elements[$i]);
		}
		$writer->endElement();
	}	

	// end day
	$writer->endElement();
}

// end votelog
$writer->endElement(); 
$writer->endDocument(); 

$writer->flush(); 
?>
