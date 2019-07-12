<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/google_calendar.php";
include_once "../menu.php";

dbConnect();

if ( $uid != 306 ) { checkLevel($level,1); }

?>
<html>
<head>
<title>Update the Calendar with Database data</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu();?>
<h1>Updating the Calendar</h1>
<ul>
<li>Checking to see if Calendar needs updating.</li>
<?php
$sql = sprintf("select * from Update_calendar");
$result = mysql_query($sql);
$count = mysql_num_rows($result);
if ( $count > 0 ) {
  while ( $row = mysql_fetch_array($result) ) {
    switch ($row['action']) {
    case "add":
	print "<li>Adding game_id=".$row['game_id']."</li>";
	$format = "%Y-%m-%d";
	$sql_game = sprintf("select id, title, description, date_format(start_date,'%s') as start_date, date_format(if ( (end_date is NULL or end_date = '0000-00-00'), date_add(start_date, INTERVAL aprox_length DAY), date_add(end_date, INTERVAL 1 DAY)),'%s') as end_date, thread_id, status from Games where id=%s",$format,$format,quote_smart($row['game_id']));
	$result_game = mysql_query($sql_game);
	$valid = mysql_num_rows($result_game);
	if ( $valid != 1 ) { break; }
	$game = mysql_fetch_array($result_game);
	if ( $game['status'] == "Unknown" ) { 
	  print "Game is an unknown status - not adding it to the calendar<br />"; 
	  break;
	}
	$calendar_id = post_event($game['title'],$game['description'],$game['start_date'],$game['end_date'],$game['thread_id']);
	$sql_cal = sprintf("update Games set calendar_id=%s where id=%s",quote_smart($calendar_id),quote_smart($game['id']));
	$result_cal=mysql_query($sql_cal);
	$sql_delete = sprintf("delete from Update_calendar where action='add' and game_id=%s",quote_smart($game['id']));
	$result_delete = mysql_query($sql_delete);
	break;
	case "update":
	print "<li>Updating game_id=".$row['game_id']."</li>";
	$format = "%Y-%m-%d";
	$sql_game = sprintf("select id, title, description, date_format(start_date,'%s') as start_date, date_format(if ( (end_date is NULL or end_date = '0000-00-00'), date_add(start_date, INTERVAL aprox_length DAY), date_add(end_date, INTERVAL 1 DAY)),'%s') as end_date, thread_id, status, calendar_id from Games where id=%s",$format,$format,quote_smart($row['game_id']));
	$result_game = mysql_query($sql_game);
	$valid = mysql_num_rows($result_game);
	if ( $valid != 1 ) { break; }
	$game = mysql_fetch_array($result_game);
	if ( $game['status'] == "Unknown" ) { 
	  print "Game is an unknown status - not updating it on the calendar<br />"; 
	  break;
	}
	if ( $game['calendar_id'] != "" ) { delete_event($game['calendar_id']); }
	$calendar_id = post_event($game['title'],$game['description'],$game['start_date'],$game['end_date'],$game['thread_id']);
	$sql_cal = sprintf("update Games set calendar_id=%s where id=%s",quote_smart($calendar_id),quote_smart($game['id']));
	$result_cal = mysql_query($sql_cal);
	$sql_delete = sprintf("delete from Update_calendar where action='update' and game_id=%s",quote_smart($game['id']));
	$result_delete = mysql_query($sql_delete);
	break;
	case "delete":
	if ( $row['calendar_id'] == "" ) { break; }
	print "<li>Deleting calendar_id=".$row['calendar_id']."</li>";
	delete_event($row['calendar_id']);
	$sql_delete = sprintf("delete from Update_calendar where action='delete' and calendar_id=%s",quote_smart($row['calendar_id']));
	$result_delete = mysql_query($sql_delete);
	break;
	}
  }
} else {
print "<li>Nothing needs to be updated.</li>";
}
?>
</ul>
</body>
</html>

