<?php

// If you want to include the timezone chart on a page, you must put the timezone_changer where you want the hidden box to appear on the page.  You then put the timezone_chart inside a div tag where the id='tz_div'.  Then at the bottom of the page you put the timezone_js.  Also if you don't have the Menu.php included you must run the ajax agent.

include_once "php/db.php";
include_once "HTML/Table.php";
dbConnect();

function timezone_changer($position="relative") {
  $output = "<div id='get_time' style='position:$position; visibility:hidden; background-color:white; border:1px solid black; height:40px;'>\n";
  $output .= "<br style='font-size:50%;' />";
  $output .= "<form name='tz_time'>\n";
  $output .= "&nbsp;&nbsp<input type='text' name='time' value='12:00' size='5' />\n";
  $output .= "<select name='mer'><option value='am' />AM<option value='pm'>PM</select>\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='calcTime()' />\n";
  $output .= "<input type='button' name='now' value='now' onClick='calcNow()' />\n";
  $output .= "&nbsp;&nbsp</div>\n";
  
  return $output;
}
function timezone_chart($time="utc_timestamp()",$game_id="") {
  global $username;
  $dst = date('I');
  $format = "%W<br />%h:%i %p";
  if ( $time == "" ) { $time="utc_timestamp()"; } 
  if ( $time != "utc_timestamp()" ) { $time = quote_smart($time); }
  $sql_tz = sprintf("select zone, description, date_format(date_add(%s, interval GMT hour), %s) as standard_time, date_format(date_add(%s, interval GMT+1 hour),%s) as daylight_time from Timezones order by GMT ",$time,quote_smart($format),$time,quote_smart($format));
  $result_tz = mysql_query($sql_tz);
  while ( $row = mysql_fetch_array($result_tz) ) {
    $zones[$row['zone']] = $row['description'];
    $standard[$row['zone']] = "<div id='".$row['zone']."_std' onMouseOver='show_hint(\"Click to change times\")' onMouseOut='hide_hint()' onClick='change_time(\"".$row['zone']."\",\"std\")'>".$row['standard_time']."</div>";
    $daylight[$row['zone']] = "<div id='".$row['zone']."_day' onMouseOver='show_hint(\"Click to change times\")' onMouseOut='hide_hint()' onClick='change_time(\"".$row['zone']."\",\"dst\")'>".$row['daylight_time']."</div>";
  }
  if ( $game_id != "" ) {
    $sql = sprintf("select name, time_zone as zone from Users, Bio, Users_game_all where Users.id=Bio.user_id and Users_game_all.user_id=Users.id and game_id=%s order by name",$game_id);
  } else {
    $sql = "select Users.id, name, time_zone as zone from Users, Bio where Users.id=Bio.user_id order by name";
  }
  $result = mysql_query($sql);
  $player['S'] = "<b style='color:maroon'>BGG Time</b>";
  while ( $row = mysql_fetch_array($result) ) {
   $sb = "";
   $eb = "";
   if ( $row['name'] == $username ) {
     $sb = "<b>";
	 $eb = "</b>";
   }
   if ( isset($player[$row['zone']]) ) {
     #$player[$row['zone']] .= "<br />".$sb.$row['name'].$eb;
     $player[$row['zone']] .= "<br />".$sb;
	 $player[$row['zone']] .= get_player_page($row['name']);
	 $player[$row['zone']] .= $eb;
   } else {
     $player[$row['zone']] = $sb;
	 $player[$row['zone']] .= get_player_page($row['name']);
	 $player[$row['zone']] .= $eb;
   }
  }
  $attrs = array (
    'class' => 'forum_table'
  );
  $table =& new HTML_Table($attrs);  
  $col = array ("Zone", "Standard Time", "Daylight Time");
  $table->addCol($col);
  unset($col);
  $col_count = 1;
  foreach ( $zones as $zone => $description ) {
    if ( isset($player[$zone]) ) {
	  $col = array ($description, $standard[$zone], $daylight[$zone], $player[$zone]);
	  $table->addCol($col);
	  if ( $zone == "S" ) { $bgg_col = $col_count; }
	  $col_count++;
	  unset($col);
	}
  }
  $time_row = 1;
  if ( $dst ) { $time_row = 2; }
  $table->updateRowAttributes(0,"style='font-weight:bold'");
  $table->updateRowAttributes($time_row,"style='background-color:white'");
  $table->updateRowAttributes(3,"valign='top'");
  $table->updateColAttributes($bgg_col,"style='background-color:white'");
  $table->updateCellAttributes(0,$bgg_col,"style='font-weight:bold; background-color:white'");
  $table->updateCellAttributes($time_row,$bgg_col,"style='font-weight:bold; background-color:white'");

  $output = $table->toHTML();
  $output .= "Don't see your name? - Go fill out your <a href='/players/add_profile.php'>profile</a>.\n";

  return $output;
}

function timezone_js() {
  ?>
<script language='javascript'>
  <!--
  zone = ""
  type = ""
  function change_time(my_zone,my_type) {
    zone = my_zone
    type = my_type
    document.getElementById('get_time').style.visibility = 'visible';
  }

  function calcTime() {
    time = document.tz_time.time.value
	mer = document.tz_time.mer.value
    document.getElementById('get_time').style.visibility = 'hidden';
	agent.call('','calcTime','changeTimes',zone,type,time,mer,game_id);
  }

  function calcNow() {
    document.getElementById('get_time').style.visibility = 'hidden';
	agent.call('','timezone_chart','changeTimes',"",game_id)
  }
  
  function changeTimes(obj) {
    var str = new String()
    str = decodeURIComponent(obj.toString())
	document.getElementById('tz_div').innerHTML=str
  }
  
  //-->
</script>
<?php
}

function calcTime($zone,$type,$time,$mer,$game_id="") {
  $sql = sprintf("select GMT from Timezones where zone=%s",quote_smart($zone));
  $result = mysql_query($sql);
  $gmt_offset = mysql_result($result,0,0);
  list($hr, $mn) = split(":",$time);
  if ( $mer == "pm" && $hr != "12") {
    $gmt_offset += 12;
  }
  if ( $mer == "am" && $hr == "12" ) {
    $gmt_offset -=12;
  }
  if ( $type == "dst" ) {
    $gmt_offset++;
  }
  $sql = "select curdate()";
  $result = mysql_query($sql);
  $date = mysql_result($result,0,0);
  $fulldate = "$date $time";
  $sql = sprintf("select date_sub(%s, interval %s hour)",quote_smart($fulldate),$gmt_offset);
  $result = mysql_query($sql);
  $gmt_time = mysql_result($result,0,0);
  $output = timezone_chart($gmt_time,$game_id);
  return rawurlencode($output);
}
?>
