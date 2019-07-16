<?php // common.php

include_once "db.php";
dbConnect();

function init_cache($dir='/dev/shm/cache_lite/')
{
	require_once 'Cache/Lite.php';

	$options = array(
    	'cacheDir' => $dir,
    	'lifeTime' => 86400,
    	'writeControl' => false,
    	'fileNameProtection' => false,
    	'readControl' => false,
    	'hashedDirectoryLevel' => 1,
	    'hashedDirectoryUmask' => 0770
	);

	$cache = new Cache_Lite($options);

	return($cache);
}

function error($msg) {
?>
<html>
<head>
<script language='Javascript'>
<!--
alert ("<?=$msg?>");
history.back();
//-->
</script>
</head>
<body></body>
</html>
<?php 
exit;
}
function get_player_page($player,$profile=true) {
  // $player can be a name or an user_id.
  if ( is_numeric($player) and $player > 0) {
    $sql = sprintf("select name from Users where id=%s",quote_smart($player));
    $result = mysql_query($sql);
   	$player = mysql_result($result,0,0);
  }
  $output = "";
  if ( $profile ) {
    $output .= get_profile_page($player,true);
	$output .= " ";
  }
  $output .= "<a href='/player/$player'>$player</a>";

  return $output;
}
function get_profile_page($player,$icon=false) {
  // $player can be a name or an user_id.
  if ( is_numeric($player) ) {
    $id = $player;
    $sql = sprintf("select name from Users where id=%s",quote_smart($player));
    $result = mysql_query($sql);
    $player = mysql_result($result,0,0);
  } else {
    $sql = sprintf("select id from Users where name=%s",quote_smart($player));
	$result = mysql_query($sql);
	$id = mysql_result($result,0,0);
  }
  $sql = sprintf("select * from Bio where user_id=%s",quote_smart($id));
  $result = mysql_query($sql);
  $count = mysql_num_rows($result);
  if ( $count == 1 ) {
    if ( $icon ) {
      $output = "<a href='/profile/$player'><img src='/images/camera-photo.png' style='border:0' alt='View Player Profile' /></a>";
    } else {
      $output = "<a href='/profile/$player'>$player</a>";
    }
  } else {
    if ( $icon ) {
	  $output  = "";
	} else {
	  $output = $player;
	}
  }

  return $output;
}
function get_game($game_id,$parameters="title"){
  global $uid;
# This function can take a string list of paramters to describe how and what should be displayed.  The game will be displayed in the order the parameters are given.  The available parambters are:
# num : Displayes the game number with a ) after it.
# title : Displays the game title as a link to the game page.
# complex: Displays a small icon of the games complexity.
# full: Displayes (number of players/Max num of players)
# post: Displays (#of post) as a link to the full cassandra files page.
# mod: Displays (list of moderators) where they are links to the players stats page.
# mod_np: Displays list of moderators where they are links to the players stats page.
# in: Displays an icon if the person viewing the page in the game (Game_users_all)
# chat: Displays an icon if the person viewing the page has a new message in a chat room for that game.
# repl: Displays and icon if the game needs a replacement player.
# 
  $parms = explode(", ",$parameters);
  $sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $game = mysql_fetch_array($result);
  $output = "";
  foreach ( $parms as $parm) {
  switch($parm){
    case "num":
	  if ( !is_numeric($game['number']) ) { $game['number'] = "*"; }
	  $output .= $game['number'].") ";
	break;
	case "title":
    if ($game['thread_id'] == null) { 
      $output .= $game['title']; 
    } else {
      $output .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> ";
    }
	break;
	case "complex":
	  if ( $game['complex'] != "" ) {
        $output .= "<img src='/images/".$game['complex']."_small.png' /> ";
	  }
	break;
	case "full":
	  $sql = sprintf("select count(*) from Players where game_id=%s",quote_smart($game_id));
	  $result = mysql_query($sql);
	  $num_players = mysql_result($result,0,0);
	  $full = "$num_players/".$game['max_players'];
	  if ( $num_players == $game['max_players'] ) { $full = "Full/$num_players"; }
	  $output .= "($full) ";
	break;
	case "post":
	  $sql = sprintf("select count(*) from Posts where game_id=%s",quote_smart($game_id));
	  $result = mysql_query($sql);
	  $num_posts = mysql_result($result,0,0);
	  $output .= "<a href='/game/".$game['thread_id']."/all'>($num_posts posts)</a> ";
	break;
	case "mod":
	case "mod_np":
	  $sql = sprintf("select name from Users, Moderators where Users.id=Moderators.user_id and game_id=%s",quote_smart($game_id));
	  $result = mysql_query($sql);
	  $mod_num = mysql_num_rows($result);
	  $count = 0;
	  $modlist = "";
	  while ( $mod = mysql_fetch_array($result) ) {
	    if ( $count == 0  && $parm != "mod_np" ) $modlist = "(";
	    if ( $count != 0  ) $modlist .= ", ";
		$modlist .= get_player_page($mod['name'],false);
	    $count++;
	    if ( $count == $mod_num && $parm != "mod_np" ) $modlist .= ")";
	  }
      $output .= $modlist." ";
	break;
	case "in":
	  if ( isset($uid) ) {
	    $sql = sprintf("select * from Users_game_all where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($uid));
	    $result = mysql_query($sql);
	    if ( mysql_num_rows($result) != 0 ) {
          $output .= "<img src='/images/calendar.png' /> ";
	    }
	  }
	break;
	case "chat":
	  if ( isset($uid) ) {
        #$sql = sprintf("select count(*) from Chat_users, Chat_rooms, Chat_messages where Chat_users.room_id=Chat_rooms.id and Chat_rooms.id=Chat_messages.room_id and Chat_messages.post_time >= Chat_users.last_view and Chat_messages.post_time > Chat_users.open and Chat_messages.post_time < if(Chat_users.close is null, now(), Chat_users.close) and Chat_rooms.game_id=%s and Chat_users.user_id=%s",quote_smart($game_id),quote_smart($uid));
        $sql = sprintf("select null from Games, Chat_users, Chat_rooms, Chat_messages where Chat_users.room_id=Chat_rooms.id and Chat_rooms.id=Chat_messages.room_id and Chat_messages.post_time >= Chat_users.last_view and Chat_messages.post_time > Chat_users.open and Chat_messages.post_time < if(Chat_users.close is null, now(), Chat_users.close) and Chat_rooms.game_id=%s and Games.id=Chat_rooms.game_id and Games.status ='In Progress' and Chat_users.user_id=%s limit 1",quote_smart($game_id),quote_smart($uid));
		$result = mysql_query($sql);
		if ( mysql_num_rows($result) == 1 ) {
          $output .= "<a href='/game/".$game['thread_id']."/chat'>";
		  $output .= "<img src='/images/new_message.png' border='0'/></a>";
		}
	  }
	break;
	case "repl":
	  $sql = sprintf("select * from Players where game_id=%s and need_replace is not null",quote_smart($game_id));
	  $result = mysql_query($sql);
	  if ( mysql_num_rows($result) > 0 ) {
        $output .= "<img src='/images/i_replace.png' border='0'/>";
	  }
	break;
  }
  }
  return $output;
}

function time_dropdown_old($name,$select='') {
  $output = "<select name='$name' id='$name' >\n";
  $selected = "";
  if ( $select == "") { 
    $selected = "selected='selected'"; 
    $select = 24;
  }
  $output .= "<option value='' $selected >None</option>\n";
  $m[0] = "am";
  $m[1] = "pm";
  for ( $i=0;$i<2;$i++ ) {
    for ( $j=0;$j<12;$j++ ) {
      $value = $j+($i*12);
      $v = $j;
      if ( $j == 0 ) { $v = 12; }
      $text = $v.":00 ".$m[$i];
	  $selected = "";
  if ( $select == $value ) { $selected = "selected='selected'"; }
      $output .= "<option value='$value' $selected>$text</option>\n";
    }
  }
  $output .= "</select>";

  return $output;
}

function time_dropdown_js($name='') {
  # If the time_dropdown is going to be used in ajax, this must be called first 
  if ( $name == "" ) {
    # one code for all dropdown on the page
    $title = "time_dropdown";
    $input = "name";
    $name = "name";
    $nhr = "name+'_hr'";
    $nmin = "name+'_min'";
    $nm = "name+'_m'";
  } else {
    # one code for each dropdown
    $title = $name;
    $input = '';
    $nhr = "'".$name."_hr'";
    $nmin = "'".$name."_min'";
    $nm = "'".$name."_m'";
    $name = "'".$name."'";
  }
  $js = "<script language='javascript'>\n";
  $js .= "<!--//\n";
#$js .= "alert('test')\n";
  $js .= "function set_$title($input) {\n";
#$js .= "alert('set_$title($input)')\n";
  $js .= "  hr = document.getElementById($nhr).value\n";
  $js .= "  min = document.getElementById($nmin).value\n";
  $js .= "  m = null\n";
  $js .= "if ( document.getElementById($nm) != null ) {\n";
  $js .= "  m = document.getElementById($nm).value\n";
  $js .= "}\n";
  $js .= "  if ( m == 'am' ) {\n";
  $js .= "    if ( hr == 12 ) { hr = 00 }\n";
  $js .= "  } else if ( m == 'pm' ) {\n";
  $js .= "    hr = parseInt(hr) + 12\n";
  $js .= "    if ( hr == 24 ) { hr = 12 }\n";
  $js .= "  }\n";
  $js .= "  v = hr+':'+min\n";
  $js .= "  document.getElementById($name).value = v\n";
#$js .= "  alert(document.getElementById($name).value)\n";
  $js .= "}\n";
  $js .= "//-->\n";
  $js .= "</script>\n";

  return $js;

}
function time_dropdown($name,$select="0:00",$mil_time=false,$need_js=true) {
  # The select value must be in 24hr time, but the display will only 
  # be in 24hr/military time if mil_time is set to true - ie removes the am/pm
  # dropdown option.
  # Needing the js is default so you don't have to remember to call 
  # time_dropdwon_js seperately.  But if putting a dropdown in an ajax call you
  # will need to call it outside of the ajax and then set need_js to false.
  list($s_hr,$s_min) = explode(":",$select);
  $s_m = "am";
  if ( $s_hr >= 12 ) { $s_m = "pm"; }
  if ( ! $mil_time ) {
    if ( $s_hr == 0 ) { $s_hr = 12;}
    if ( $s_hr > 12 ) { $s_hr = $s_hr - 12; }
  }
  $js = "";
  if ($need_js) { 
    $js = time_dropdown_js($name); 
    $onChange = "onChange='set_$name()'";
  } else {
    $onChange = "onChange='set_time_dropdown(\"$name\")'";
  }
  $output = "<input type='hidden' name='$name' value='$select' id='$name' />\n";
  $end = 12;
  if ( $mil_time ) { $end = 24; }
  for ( $i=0;$i<$end;$i++ ) {
    $v = $i;
    if ( !$mil_time ) {if ( $v == 0 ) { $v = 12; }}
    $hrs["$v"] = "$v";
  }
  for ( $i=0;$i<60;$i++ ) {
    $v = $i;
    if ( $v < 10 ) { $v = "0".$v; }
    $mins["$v"] = "$v";
  }
  $ms = array ("am" => "am", "pm" => "pm");

  $output .= create_dropdown($name."_hr",$s_hr,$hrs,$onChange);
  $output .= ":";
  $output .= create_dropdown($name."_min",$s_min,$mins,$onChange);
  if ( ! $mil_time) {
    $output .= create_dropdown($name."_m",$s_m,$ms,$onChange);
  }

  return $output.$js;
}

function time_24($hour,$min="00") {
  if ( $hour == 0 ) {
    return "12:${min}am";
  }
  if ( $hour < 12 ) {
    return "$hour:${min}am";
  }
  if ( $hour == 12 ) {
    return "$hour:${min}pm";
  }
  if ( $hour > 12 ) {
    $hour-=12;
    return "$hour:${min}pm";
  }
}

function is_moderator($user_id,$game_id) {
  $sql = sprintf("Select * from Moderators where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
  $result=mysql_query($sql);
  $row_count = mysql_num_rows($result);
  $moderator = false;
  if ( $row_count == 1 ) $moderator = true;
  
  return $moderator;
}

function is_player($user_id,$game_id) {
  $sql = sprintf("Select * from Players where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
  $result=mysql_query($sql);
  $row_count = mysql_num_rows($result);
  $isplayer = false;
  if ( $row_count == 1 ) $isplayer = true;

  return $isplayer;
}

function get_enum_array($field,$table) {
  $sql = "show columns from $table where field='$field'";
  $result = mysql_query($sql);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
	  $enum_array[$v] = $v;
	}
  }

  return $enum_array;
}

function create_dropdown($name,$selected,$list,$extra="",$multiple=false) {
  $output = "<select id='$name' name='$name' $extra ";
  if ( $multiple ) { $output .= "multiple='multiple' "; }
  $output .= ">\n";
  foreach ( $list as $value => $display ) {
    $s = "";
	if ( is_array($selected) ) {
	  if( in_array($value,$selected) ) { $s = "selected='selected'"; }
	} else {
	  if( $value == $selected ) { $s = "selected='selected'"; }
	}
	$output .= "<option $s value='$value'>$display</option>\n";
  }
  $output .= "</select>\n";

  return $output;
}

function safe_html($text,$tags='') {
  
  # List of allowable tags:
  $allow = "<b>"; # Bold
  $allow .= "<i>"; # Italics
  $allow .= "<u>"; # Underline
  $allow .= "<br>"; # Line Break
  $allow .= "<font>"; # Font specifier
  $allow .= "<color>"; # Color specifier
  $allow .= "<hr>"; # Horizontal Rule
  $allow .= "<ul>"; # Unordered list
  $allow .= "<ol>"; # Ordered list
  $allow .= "<li>"; # List item
  $allow .= "<strike>"; # strike text
  $allow .= $tags;

  $safe_text = strip_tags($text,$allow);

  $safe_text = ($safe_text) ? $safe_text : " ";

  return $safe_text;
}

function page_header($title,$extra="") {
  $output  = "<html><head>\n";
  $output .= "<title>$title</title>\n";
  $output .= "<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>\n";
  $output .= $extra;
  $output .= "</head>\n";
  $output .= "<body>\n";
  $output .= display_menu();
  $output .= "<div style='padding:10px;'>\n";

  return $output;
}

function page_footer($extra="") {
  $output = "</div>\n";
  $output .= $extra;
  $output .= "</body></html>\n";

  return $output;
}

function run_asynch($path) {
    $WshShell = new COM("WScript.Shell");
    $oExec = $WshShell->Run(addslashes($path), 7, false);
    unset($WshShell,$oExec);
}

?>
