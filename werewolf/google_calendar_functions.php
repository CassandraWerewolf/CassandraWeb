<?php // These funtions are used for communicating with google caledars.

include_once "php/db.php";

dbConnect();

function add_game_link($game_id) {
  if ( $game_id == 0 ) { return; }
  $sql = sprintf("select if ( (end_date is NULL or end_date = '0000-00-00'), date_add(start_date, INTERVAL aprox_length DAY), date_add(end_date, INTERVAL 1 DAY)) as end_date from Games where id=%s",quote_smart($game_id));

  $result = mysql_query($sql);
  $edate = mysql_result($result,0,0);
  $format = "%Y%m%d";
  $sql = sprintf("select title as text, concat(date_format(start_date,'%s'),'/',date_format('%s','%s')) as dates, thread_id from Games where id=%s",$format,$edate,$format,quote_smart($game_id));
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  $text_qs = urlencode($row['text']);
  $dates_qs = urlencode($row['dates']);
  $details_qs = urlencode("/game/".$row['thread_id']);
  $sprops_qs = urlencode("website:http://cassandrawerewolf.com");
  $trp_qs = urlencode("true");
  $query_string = "action=TEMPLATE&text=$text_qs&dates=$dates_qs&details=$details_qs&sprop=$sprops_qs&trp=$trp_qs";
  $href = "http://www.google.com/calendar/event?".htmlentities($query_string);
  
  $output = "<a href='$href'><img src='http://www.google.com/calendar/images/ext/gc_button1.gif' border='0'></a>";

  return $output;
}
?>
