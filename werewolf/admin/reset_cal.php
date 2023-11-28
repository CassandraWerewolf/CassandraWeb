<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/google_calendar.php";
include_once "../menu.php";

$mysql = dbConnect();

checkLevel($level,1);


?>
<html>
<head>
<title>Reset the Calendar with Database data</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu();?>
<h1>Reseting the Calendar</h1>
<ul>
<li>Deleting all entries to Update_calendar table</li>
<?php
if ( 1 != 1 ) {
$sql = sprintf("delete from Update_calendar");
$result = mysqli_query($mysql, $sql);
?>
<li>Deleting all calendar entries</li>
<?php
$feed = get_calendar_feed();
foreach ($feed as $item) {
  print "Deleting: ".$item->id()."<br />";
  delete_event($item->id());
}
?>
<li>Adding in Entries from Games Table in the Database</li>
<?php
$format = "%Y-%m-%d";
$sql = sprintf("select id, title, description, date_format(start_date,'%s') as start_date, date_format(if ( (end_date is NULL or end_date = '0000-00-00'), date_add(start_date, INTERVAL aprox_length DAY), date_add(end_date, INTERVAL 1 DAY)),'%s') as end_date, thread_id from Games where start_date != '0000-00-00 00:00:00'",$format,$format);
$result = mysqli_query($mysql, $sql);
while ( $row = mysqli_fetch_array($result) ) {
  print "Game: ".$row['id']."<br />";
  $calendar_id = post_event(($row['title']),$row['description'],$row['start_date'],$row['end_date'],$row['thread_id']);
  $sql_update = sprintf("update Games set calendar_id=%s where id=%s",quote_smart($calendar_id),quote_smart($row['id']));
  $result_update = mysqli_query($mysql, $sql_update);
}
}
?>
</li>Finished</li>
</ul>
</body>
</html>
