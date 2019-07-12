<?php

#include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "configure_physics_functions.php";
#include_once "php/common.php";

dbConnect();

$sql = sprintf("select * from Physics_processing order by id asc");
$result = mysql_query($sql);
while ($game = mysql_fetch_array($result)) {
  if ($game['frequency'] == 'immediate') {
	continue;
  }
  $last_run = getdate(strtotime($game['last_run']));
  $day = $last_run['yday'];
  $day++;
  $minute = $game['minute'];
  echo "day: $day\n";
  if ($game['frequency'] == 'hourly') {
    $hour = $last_run['hours']+1;
    echo "hour: $hour\n";
  } elseif ($game['frequency'] == '2hourly') {
    $hour = $last_run['hours']+2;
  } elseif ($game['frequency'] == '15mins') {
    $minute = $last_run['minutes']+15;
    $hour = $last_run['hours']; 
  } elseif ($game['frequency'] == '30mins') {
    $minute = $last_run['minutes']+30;
    $hour = $last_run['hours'];         
  } else {
    $hour = $game['hour'];
    if ($game['frequency'] == '5daily' && $last_run['weekday'] == 'Friday') 
    {
      $day += 2;
    }
    $day++;    	
  }
echo "hour: $hour\n";
echo "minute: ".$minute ."\n";
echo "year: ".$last_run['year'] ."\n";
  $should = mktime($hour,$minute,0,1,$day, $last_run['year']);
  echo "should: $should\n";
  echo "now: ".time()."\n";
  if ($should <= time()) {
    $sql_update = sprintf("update Physics_processing set last_run=now() where id=%s",quote_smart($game['id']));
    mysql_query($sql_update);
    if ($game['type'] == 'item') {
      process_items($game['game_id']);
    }    
    if ($game['type'] == 'movement') {
      process_movements($game['game_id']);
    }
  }
}
?>
