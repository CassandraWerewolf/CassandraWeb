<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "php/bgg.php";
include_once "HTML/Table.php";
include_once "google_calendar_functions.php";
// include_once "moderator_control_functions.php";


dbConnect();

function get_game_info($id,$type) {
  if ( $type == "thread" ) {
    $sql = sprintf("Select * from Games where thread_id=%s",quote_smart($id));
  } elseif ( $type == "game" ) {
    $sql = sprintf("Select * from Games where id=%s",quote_smart($id));
  } else {
    $game['id'] = 0;
    return $game;
  }
  $result = mysql_query($sql);
  $game = mysql_fetch_array($result);
  if ( mysql_num_rows($result) != 1 ) { $game['id'] = 0; }

  return $game;
}

function get_player_info($user_id,$game_id) {
  $sql = sprintf("select * from Players, Players_all where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players_all.user_id=%s and Players_all.game_id=%s",quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $player_info = mysql_fetch_array($result);
  if ( mysql_num_rows($result) != 1 ) { unset($player_info); }

  return $player_info;
}

function get_game_status($status,$parent_id) {
 $subthread = false;
 if ( $status == "Sub-Thread" ) {
  $sql = sprintf("Select `status` from Games where id=%s",quote_smart($parent_id));
  $result = mysql_query($sql);
  $status = mysql_result($result,0,0);
  $subthread = true;
 }

 $out = array($status,$subthread); 
 return $out;
}

function get_game_chat_status($game_id){
  $sql = sprintf("select count(*) from Chat_rooms where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $chats = mysql_result($result,0,0);
  
  return $chats;
}

function create_edit_div($edit,$id,$hint,$onclick,$content){
  $output = "<div id='$id' ";
  if ( $edit ) {
    $output .= "onMouseOver='show_hint(\"$hint\")' ";
	$output .= "onMouseOut='hide_hint()' ";
	$output .= "onClick='$onclick' ";
  }
  $output .= ">";
  $output .= $content;
  $output .= "</div>";

  return $output;
}
 
function create_game_info_table($edit,$status,$subthread,$game_id){
  $output = "<table class='forum_table' border='0' >\n";
  #Moderators
  $output .= "<tr><td>\n";
  $content = "<b>Moderators:</b>\n";
  $output .= create_edit_div($edit,'mod_div',"Click to Edit Moderators",'get_edit_form("mod_form")',$content);
  $output .= "</td><td id='mod_td'>\n";
  $output .= show_moderator($game_id);
  $output .= "</td></tr>\n";
  if ( !$subthread ) {
    #Dates
    $output .= "<tr><td>\n";
    $content = "<b>Dates:</b>\n";
    $output .= create_edit_div($edit,'dates_div',"Click to Edit Dates",'get_edit_form("dates_form")',$content);
    $output .= "</td><td>\n";
	$output .= "<table border='0' width='100%'><tr><td id='dates_td'>\n";
	$output .= show_dates($game_id,$edit);
	$output .= "</td><td align='right'>\n";
    $output .= add_game_link($game_id); 
	$output .= "</td></tr></table>\n";
    $output .= "</td></tr>\n";
  }
  #Status
  $output .= "<tr><td>\n";
  $content = "<b>Status:</b>\n";
  $output .= create_edit_div($edit,'status_div',"Click to Edit Status",'get_edit_form("status_form")',$content);
  $output .= "</td><td>\n";
  $output .= "<table border='0' width='100%'><tr><td id='status_td'>\n";
  $output .= show_game_status($game_id,$edit);
  $output .= "</td><td align='right'>\n";
  $output .= show_extra_status_info($game_id);
  $output .= "</td></tr></table>\n";
  $output .= "</td></tr>\n";
  #Deadlines
  $output .= "<tr><td>\n";
  $content = "<b>Deadlines:</b>\n";
  $output .= create_edit_div($edit,'deadline_div',"Click to Edit Deadlines",'get_edit_form("deadline_form")',$content);
  $output .= "</td><td id='deadline_td'>\n";
  $output .= show_deadlines($game_id,$edit);
  $output .= "</td></tr>\n";
  if ( $status == "Sign-up" && !$subthread ) {
    #Max Players
    $output .= "<tr><td>\n";
    $content = "<b>Max Players:</b>\n";
    $output .= create_edit_div($edit,'max_div',"Click to Edit Max Players",'get_edit_form("maxplayers_form")',$content);
    $output .= "</td><td id='maxplayers_td'>\n";
    $output .= show_maxplayers($game_id,$edit);
    $output .= "</td></tr>\n";
  }
  if ( !$subthread ) {
    #Complexity
    $output .= "<tr><td>\n";
    $content = "<b>Complexity:</b>\n";
    $output .= create_edit_div($edit,'complex_div',"Click to Edit Complexity",'get_edit_form("complexity_form")',$content);
    $output .= "</td><td id='complex_td'>\n";
    $output .= show_complexity($game_id,$edit);
    $output .= "</td></tr>\n";
  }
  if ( $status == "Finished" || $edit ) {
    #Winner
    $output .= "<tr><td>\n";
    $content = "<b>Winner:</b>\n";
    $output .= create_edit_div($edit,'win_div',"Click to Edit Winner",'get_edit_form("winner_form")',$content);
    $output .= "</td><td id='win_td'>\n";
    $output .= show_winner($game_id,$edit);
    $output .= "</td></tr>\n";
  }
  if ( $edit ) {
    #BGG Thread_id
    $output .= "<tr><td>\n";
    $content = "<b>BGG Thread id:</b>\n";
    $output .= create_edit_div($edit,'thread_div',"Click to Change BGG Thread id",'get_edit_form("thread_form")',$content);
    $output .= "</td><td id='thread_td'>\n";
    $output .= show_thread_id($game_id,$edit);
    $output .= "</td></tr>\n";
  }
  if ( !$subthread ) {
    #Sub-Threads
    $output .= "<tr><td>\n";
    $content = "<b>Sub-Threads:</b>\n";
    $output .= create_edit_div($edit,'subt_div',"Click to Add or Delete Sub-Threads",'get_edit_form("subt_form")',$content);
    $output .= "</td><td id='subt_td'>\n";
    $output .= show_subthreads($game_id);
    $output .= "</td></tr>\n";
  }
  #Description
  $output .= "<tr><td>\n";
  $content = "<b>Description:</b>\n";
  $output .= create_edit_div($edit,'desc_div',"Click to Edit Description",'get_edit_form("desc_form")',$content);
  $output .= "</td><td id='desc_td'>\n";
  $output .= show_description($game_id,$edit);
  $output .= "</td></tr>\n";
  $output .= "</table>\n";

  return $output;
}

function show_name($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  if ( $game['number'] != "" ) {
    $output .= $game['number'].") ";
  }
  $output .= $game['title'];

  return $output;
}

function show_moderator($game_id) {
  global $game, $domain;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $sql = sprintf("Select id, name from Users, Moderators where Users.id=Moderators.user_id and Moderators.game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  $count = 0;
  while ( $mod = mysql_fetch_array($result) ) {
    $sql2 = sprintf("Select count(*) from Posts where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($mod['id']));
    $result2=mysql_query($sql2);
    $num_post=mysql_result($result2,0,0);
    if ( $count == 0 ) {
      $output .= get_player_page($mod['name']);
      $output .= " <a href='$domain/game/".$game['thread_id']."/".$mod['name']."'>($num_post post)</a>";
    } else {
      $output .= ", ";
      $output .= get_player_page($mod['name']);
      $output .= " <a href='$domain/game/".$game['thread_id']."/".$mod['name']."'>($num_post post)</a>";
    }
    $count++;
  }

  return $output;
}

function show_dates($game_id,$edit='false') {
  $output = "";
  $format = "'%b %e, %Y'";
  $sql = sprintf("select date_format(start_date, %s) as start, date_format(end_date, %s) as end from Games where id=%s",$format,$format,quote_smart($game_id));
  $result = mysql_query($sql);
  $date = mysql_fetch_array($result);
  if ( $date['end'] == "" ) { $date['end'] = "?"; }
  $content = $date['start']." to ".$date['end'];
  $output = create_edit_div($edit,'dates_div2',"Click to Edit Dates",'get_edit_form("dates_form")',$content);

  return $output;
}

function show_game_status($game_id,$edit='false') {
  global $game, $domain;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $content = $game['status']." - ".$game['phase']." ".$game['day']; 
  $output .= create_edit_div($edit,'status_div2',"Click to Edit Status",'get_edit_form("status_form")',$content);
  list ($status, $subthread) = get_game_status($game['status'],$game['parent_game_id']);
  if ( $subthread ) {
    $sql = sprintf("Select title, thread_id from Games where id=%s",quote_smart($game['parent_game_id']));
    $result = mysql_query($sql);
    $parent_game = mysql_fetch_array($result);
    $output .= " of <a href='$domain/game/".$parent_game['thread_id']."'>".$parent_game['title']."</a>";
  }
  return $output;
}

function show_extra_status_info($game_id) {
  global $game, $domain, $uid;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $ismod = is_moderator($uid,$game_id);
  $isplayer = is_player($uid,$game_id);
  $player_info = get_player_info($uid,$game_id);
  $output = "";
  list ($status, $subthread) = get_game_status($game['status'],$game['parent_game_id']);
  if ( $status == "In Progress" ) {
    $format1 = '%i';
    $format2 = '%l';
    $sql = sprintf("select concat(date_format(if(minute>date_format(now(),'%s'),now(),date_add(now(),interval 1 hour)),'%s'),':',if(minute<10,concat('0',minute),minute)) as next from Post_collect_slots where game_id=%s",$format1,$format2,quote_smart($game_id));
    $result = mysql_query($sql);
    if ( mysql_num_rows($result) > 0 ) {
      $next = mysql_result($result,0,0);
      $output .= "Next Post Scan at $next";
    } 
  }
  if ( $game['status'] == "Sign-up" ) {
    $sql = sprintf("select count(*) from Players where game_id=%s",quote_smart($game_id));
    $result = mysql_query($sql);
    $count = mysql_result($result,0,0);
    if ( $count < $game['max_players'] && !$isplayer  && !$ismod ) {
      $output .= "<a href='$domain/sign_me_up.php?action=add&game_id=$game_id'>Sign Me UP!!!</a>";
    }
    if ( $isplayer ) {
      $player_info = get_player_info($uid,$game_id);
      if ( $player_info['need_to_confirm'] == 1 ) {
        $output .= "<a href='$domain/sign_me_up.php?action=confirm&game_id=$game_id'>Confirm</a><br />";
      }
      $output .= "<a href='$domain/sign_me_up.php?action=remove&game_id=$game_id'>Remove me</a>";
    }
  } 
  return $output;
}

function show_deadlines($game_id,$edit='false') {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  list($lynch,$x,$x) = split(":",$game['lynch_time']);
  list($night,$x,$x) = split(":",$game['na_deadline']);
  $content = "";
  if ( $lynch != "" ) { $content .= "Lynch: ".time_24($lynch)." BGG<br />"; }
  if ( $night != "" ) { $content .= "Night Action: ".time_24($night)." BGG"; }
  $output .= create_edit_div($edit,'deadline_div2',"Click to Edit Deadlines",'get_edit_form("deadline_form")',$content);
  if ( $lynch != "" ) {
    $sql = sprintf("SELECT concat_ws(', ',if(sun, 'Sun', null),if(mon, 'Mon', null), if(tue, 'Tue', null), if(wed, 'Wed', null), if(thu, 'Thu', null), if(fri, 'Fri', null), if(sat, 'Sat', null)) as lynch_days from Auto_dusk where  game_id=%s",quote_smart($game_id));
    $result = mysql_query($sql);
    if ( mysql_num_rows($result) == 1 ) {
      $lynch_days = mysql_result($result,0,0);
      $output .= "<tr><td><b>Lynch Days:</b></td><td>$lynch_days</td></tr>\n";
    }
  }

  return $output;
}

function show_maxplayers($game_id,$edit='false') {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $output .= create_edit_div($edit,'max_div2',"Click to Edit Max Players",'get_edit_form("maxplayers_form")',$game['max_players']);
  return $output;
}

function show_complexity($game_id,$edit='false') {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $content = "";
  if ( $game['complex'] != "" ) {
    $content .= "<img src='/images/".$game['complex']."_large.png' />";
  }
  $output .= create_edit_div($edit,'complex_div2',"Click to Edit Complexity",'get_edit_form("complexity_form")',$content);
  return $output;
}

function show_winner($game_id,$edit='false') {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $content = $game['winner'];
  $output .= create_edit_div($edit,'win_div2',"Click to Edit Winner",'get_edit_form("winner_form")',$content);
  return $output;
}

function show_thread_id($game_id,$edit='false') {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $content = $game['thread_id'];
  $output .= create_edit_div($edit,'thread_div2',"Click to Change BGG Thread id",'get_edit_form("thread_form")',$content);
  return $output;
}

function show_subthreads($game_id) {
  global $game, $domain;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $sql = sprintf("select * from Games where parent_game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<a href='$domain/game/".$row['thread_id']."'>".$row['title']."</a><br />\n";
  }
  return $output;
}

function show_description($game_id,$edit='false') {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "";
  $content = stripslashes($game['description']);
  $output .= create_edit_div($edit,'desc_div2',"Click to Edit Description",'get_edit_form("desc_form")',$content);
  return $output;
}

function create_mod_controls($game_id,$status,$chats) {
  global $game, $domain;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "Test";
  $output .= "<div id='control_table'>";
  $output .= "<table class='forum_table' width='100%'>\n";
  $output .= "<tr><th>Moderator Controls</th></tr>\n";
  $output .= "<tr><td align='center'>";
  # Controls while the game is in Signup
  if ( $status == "Sign-up" ) {
    $output .=  "<div><a href='javascript:get_modcontrol_form(\"assign_roles_form\")'>Randomly Assign Roles</a></div>\n";
   $output .=  "<div><a href='javascript:delete_game()'>Remove this game from the Cassandra Database</a></div>\n";
  }
  # Controls while the game is in Progress
  if ( $status == "In Progress" ) {
    if ( $game['auto_vt'] == "No" ) {
      $output .= "<div><a href='javascript:get_modcontrol_form(\"vote_tally_form\")'>Activate Auto Vote Tally</a></div>\n";
    } else {
      $output .= "<div><a href='javascript:submit_vote_tally(\"retrieve\")'>Retrieve Final Lynch Time Vote</a></div>\n";
    }
    $output .=  "<div><a href='$domain/configure_chat.php?game_id=".$game['id']."'>Activate/Configure Game Communications System</a></div>\n";
    if ( $chats > 0 ) {
      $output .=  "<div><a href='javascript:get_modcontrol_form(\"goa_form\")'>Activate/Modify Game Order Assistant</a></div>\n";
    }
    if ( $game['missing_hr'] > 0 ) {
      $output .= "<div><a href='javascript:get_modcontrol_form(\"mpw_form\")'>Missing Player Warning: ".$game['missing_hr']."hrs</a></div>";
    } else {
      $output .= "<div><a href='javascript:get_modcontrol_form(\"mpw_form\")'>Activate Missing Player Warning System</a></div>";
    }
    #print "<div><a href='javascript:activate_ad()'>Activate/Modify Auto Dusk</a></div>\n";
  }

  # Controls for the game in sign-up or In progress
  if ( $status == "Sign-up" || $status == "In Progress" ) {
    $output .= "<div><a href='$domain/configure_physics.php?game_id=".$game['id']."'>Activate/Configure Physics System</a></div>\n";
    $sql = sprintf("select name from Players_all, Users where Players_all.user_id=Users.id and Players_all.`type` != 'replaced' and Players_all.game_id=%s",quote_smart($game_id));
    $result = mysql_query($sql);
    while ( $r = mysql_fetch_array($result) ) {
      $player_list[] = $r['name'];
    }
    $output .= random_selector_tool($player_list);
  }
  # Div where controls will show up
  $output .= "<div style='position:absolute; visibility:hidden; background-color:white; border:1px solid black;' id='control_space'></div>";

  $output .= "</td></tr></table></div>";
  return $output;
}

function create_edit_area() {
  $output = "";
  $output .= "<div id='edit_table'>";
  $output .= "<table class='forum_table' width='100%'>";
  $output .= "<tr><th> Edit </th></tr>";
  $output .= "<tr><td align='center'><div id='edit_space'>";
  $output .= clear_editSpace(); 
  $output .= "</div></td></tr>";
  $output .= "</table></div>";
  return $output;
}

function clear_editSpace() {
  global $domain;
  $output = "";
  $output .= "You have edit permissions for this game.  Please click on something you wish to edit.  The edit dialogue will appear here.<br /><a href='$domain/editgame_userguide.html'>Users Guide</a>";
  return $output;
}

function name_form($game_id) {
  $output = "You can change the name of the game or sub-thread.<br /><br />";
  $sql = sprintf("select title from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $title = mysql_result($result,0,0);
  $output .= "<form name='new_title'>\n";
  $output .= "<input type='text' name='title' value='$title' />\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_name()'/>\n";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()'/>\n";
  $output .= "</form>\n";

  return $output;
}

function name_submit($game_id,$title) {
    $cache = init_cache();
    $title = safe_html($title);
    $sql = sprintf("update Games set title=%s where id=%s",quote_smart($title),quote_smart($game_id));
    $result = mysql_query($sql);
    $cache->clean('front-signup-' . $game_id);
    $cache->clean('front-signup-fast-' . $game_id);
    $cache->clean('front-signup-swf-' . $game_id);
    $cache->remove('game-' . $game_id, 'front');
    return show_name($game_id); 
}

function mod_form($game_id) {
  $output = "Select moderators.  Use Control to select more than one.<br /><br />";
  $output .= "<form name='change_mod'>\n";
  $sql = sprintf("select user_id from Users, Moderators where Moderators.user_id
=Users.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $modids[]=$row['user_id'];
  }
  $sql="Select id, name from Users order by name";
  $result = mysql_query($sql);
  unset($options);
  while ( $row = mysql_fetch_array($result) ) {
    $options[$row['id']] = $row['name'];
  }

  $output .= create_dropdown('moderator[]',$modids,$options,"size='4'",true);
  $output .= "<br /><input type=button value='submit' name='submit' onClick='submit_mod()' />\n";
  $output .= "<input type=button value='cancel' name='cancel' onClick='clear_edit()' />\n";
  $output .= "</form>\n";

  return $output;
}

function mod_submit($game_id,$modlist) {
  $cache = init_cache();
  $newidlist = split( ",", $modlist);
  sort($newidlist);
  $sql = sprintf("select user_id from Games, Moderators where Games.id = Moderators.game_id and Games.id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $oldidlist[] = $row['user_id'];
  }
  $cache->clean('front-signup-' . $game_id);
  $cache->clean('front-signup-swf-' . $game_id);
  $cache->clean('front-signup-fast-' . $game_id);
  $cache->remove('games-signup-list', 'front');
  $cache->remove('games-signup-swf-list', 'front');
  $cache->remove('games-signup-fast-list', 'front');

# Find Id's that need to be added.
  foreach ( $newidlist as $newid ) {
    $found = false;
    foreach ( $oldidlist as $oldid ) {
      if ( $newid == $oldid ) $found = true;
    }
    if ( ! $found ) $addlist[] = $newid;
  }

# Add Id's that need to be added.
  if ( $addlist[0] != "" ) {
    foreach ( $addlist as $id ) {
      $sql = sprintf("insert into Moderators ( user_id, game_id ) values ( %s, %s )",quote_smart($id),quote_smart($game_id));
      $result = mysql_query($sql);
    }
  }

# Find id's that need to be deleted.
  foreach ( $oldidlist as $oldid ) {
    $found = false;
    foreach ( $newidlist as $newid ) {
      if ( $newid == $oldid ) $found = true;
    }
    if ( ! $found ) $dellist[] = $oldid;
  }

# Delete id's that need to be deleted.
  if ( $dellist[0] != "" ) {
    foreach ( $dellist as $id ) {
      $sql = sprintf("delete from Moderators where user_id=%s and game_id=%s",quote_smart($id),quote_smart($game_id));
      $result = mysql_query($sql);
    }
  }

  return show_moderator($game_id);
}

function dates_form($game_id) {
  $output = "Edit the start and end dates.<br /><br />";
  $format = "'%Y-%m-%d'";
  $output .= "<form name='edit_date'>\n";
  $sql = sprintf("select date_format(start_date, %s) as start, date_format(end_date, %s) as end from Games where id=%s", $format,$format,quote_smart($game_id));
  $result = mysql_query($sql);
  $date = mysql_fetch_array($result);
  $output .= "<input type=text name='start' value='".$date['start']."' />";
  $output .= " to ";
  $output .= "<input type=text name='end' value='".$date['end']."' />\n";
  $output .= "<br /><input type='button' name='submit' value='submit' onClick='submit_dates()'/>\n";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()'/>\n";
  $output .= "</form>";

  return $output;
}

function dates_submit($game_id,$sdate,$edate) {
  $cache = init_cache();
  $sql = sprintf("update Games set start_date=%s, end_date=%s where id=%s",quote_smart($sdate),quote_smart($edate),quote_smart($game_id));
  $result = mysql_query($sql);
  $cache->clean('front-signup-' . $game_id);
  $cache->clean('front-signup-fast-' . $game_id);
  $cache->clean('front-signup-swf-' . $game_id);
  $cache->remove('games-signup-list', 'front');
  $cache->remove('games-signup-swf-list', 'front');
  $cache->remove('games-signup-fast-list', 'front');
  return  show_dates($game_id,true);

}

function status_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "Change the status of the game.  In-Progress means that the players can only see their own roles, and that nobody can see any of the comments below.  Once you set the game to 'Finished' then everyone will be able to see everything.  When you set a game to 'Finished' please don't forget to set the winner.  If you are using the Automatied vote tally system there should be no need to manually change the period or number.<br /><br />";
  $output .= "<form name='new_status'>\n";
  $status = $game['status'];
  $phase = $game['phase'];
  $day = $game['day'];
  unset($options);
  $sql="show columns from Games where field='status'";
  $result=mysql_query($sql);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      if ( $status != "Sub-Thread" ) {
        $options[$v] = $v;
        if ( $status == $v ) { break; }
      } else {
        $options["Sub-Thread"] = "Sub-Thread";
      }
    }
    $output .= create_dropdown('status',$status,$options);
  }
  unset($options);
  $sql="show columns from Games where field='phase'";
  $result=mysql_query($sql);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      $options[$v] = $v; 
    }
  }
  $output .= create_dropdown('phase',$phase,$options);
  $output .= "<input type='text' size='2' name='day' value='$day' />\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_status()' />\n";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()' />\n";
  $output .= "</form>\n";
  return $output;
}

function status_submit($game_id,$status,$phase,$day) {
  $cache = init_cache();
  $sql = sprintf("update Games set `status`=%s, phase=%s, day=%s where id=%s",quote_smart($status),quote_smart($phase),quote_smart($day),quote_smart($game_id));
  $result = mysql_query($sql);
  if($status == 'In Progress') {
    $cache->remove('total-games', 'front');
    $cache->remove('games-in-progress-list', 'front');
    $cache->remove('current-games', 'front');
    $cache->remove('games-signup-list', 'front');
    $cache->remove('games-signup-swf-list', 'front');
    $cache->remove('games-signup-fast-list', 'front');
    $cache->clean('front-signup-' . $game_id);
    $cache->clean('front-signup-swf-' . $game_id);
    $cache->clean('front-signup-fast-' . $game_id);
  } elseif($status == 'Finished') {
    $cache->remove('current-games', 'front');
    $cache->remove('games-in-progress-list', 'front');
    $cache->remove('games-ended-list', 'front');
  }

   return show_game_status($game_id,true);
}

function deadline_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "Change the deadlines of the game.<br /><br />";
  $output .= "<form name='new_deadline'>\n";
  $lynch_db = $game['lynch_time'];
  $night_db = $game['na_deadline'];
  list($lynch,$x) = split(":",$lynch_db);
  list($night,$x) = split(":",$night_db);
  $output .= "<table>\n";
  $output .= "<tr><td>Lynch:</td><td>".time_dropdown_old('lynch',$lynch)."</td></tr>\n";
  $output .= "<tr><td>Night Action:</td><td>".time_dropdown_old('night',$night)."</td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='button' name='submit' value='submit' onClick='submit_deadline()' /><input type='button' name='cancel' value='cancel' onClick='clear_edit()' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";

  return $output;
}

function deadline_submit($game_id,$lynch,$night) {
  if ( $lynch != "" ) {
    $lynch .= ":00:00";
    $sql = sprintf("update Games set `lynch_time`=%s where id=%s",quote_smart($lynch),quote_smart($game_id));
  } else {
    $sql = sprintf("update Games set `lynch_time`=null where id=%s",quote_smart($game_id));
  }
  $result = mysql_query($sql);
  if ( $night != "" ) {
    $night .= ":00:00";
    $sql = sprintf("update Games set `na_deadline`=%s where id=%s",quote_smart($night),quote_smart($game_id));
  } else {
    $sql = sprintf("update Games set `na_deadline`=null where id=%s",quote_smart($game_id));
  }
  $result = mysql_query($sql);

  return show_deadlines($game_id,true);
}

function maxplayers_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "Change Max number of players.  If you make this number less than or equal to the number of people currently signed up then no more people can sign up via Cassandra.<br /><br />";
  $output .= "<form name='change_maxp'>";
  $max_players = $game['max_players'];
  $output .= "<input type='text' name='max_players' value='$max_players' />";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_maxplayers()' />";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()' />";
  $output .= "</form>";

  return $output;
}

function maxplayers_submit($game_id,$maxplayers) {
  $cache = init_cache();
  $sql = sprintf("update Games set max_players=%s where id=%s",quote_smart($maxplayers),quote_smart($game_id));
  $result = mysql_query($sql);
  $cache->clean('front-signup-' . $game_id);
  $cache->clean('front-signup-fast-' . $game_id);
  $cache->clean('front-signup-swf-' . $game_id);

  return show_maxplayers($game_id,true);
}

function complexity_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "Change the complexity of the game.<br />";
  $output .= "<form name='comp_form'>";
  $complex = $game['complex'];
  $sql="show columns from Games where field='complex'";
  $result=mysql_query($sql);
  unset($options);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      $options[$v] = $v;
    }
  }
  $output .= create_dropdown('complex',$complex,$options);
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_complex()' />";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()' />";
  $output .= "</form>";

  return $output;
}

function complexity_submit($game_id,$complex) {
  $cache = init_cache();
  $sql = sprintf("update Games set complex=%s where id=%s",quote_smart($complex),quote_smart($game_id));
  $result = mysql_query($sql);
  $cache->clean('front-signup-' . $game_id);
  $cache->clean('front-signup-swf-' . $game_id);
  $cache->clean('front-signup-fast-' . $game_id);

  return show_complexity($game_id,true);
}

function winner_form ($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "Change the winner of the game.  If an evil team one select evil.  If the good team won, select good,  If the game was neither good vs evil or had an individual winner then choose 'other'.<br /><br />";
  $output .= "<form name='new_winner'>\n";
  $winner = $game['winner'];
  $sql="show columns from Games where field='winner'";
  $result=mysql_query($sql);
  unset($options);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      $options[$v] = $v;
    }
  }
  $output .= create_dropdown('winner',$winner,$options);
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_winner()' />\n";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()' />\n";
  $output .= "</form>\n";
 
  return $output;
}

function winner_submit($game_id,$winner) {
  $cache = init_cache();
  $sql = sprintf("update Games set winner=%s where id=%s",quote_smart($winner),quote_smart($game_id));
  $result = mysql_query($sql);
  $cache->remove('evil-games', 'front');
  $cache->remove('good-games', 'front');
  $cache->remove('other-games', 'front');

  return show_winner($game_id,true);
}

function thread_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "You can change the BGG thread_id.  This should only be done when changing a game from a sign-up thread to a game thread.<br /><br />";
  $thread_id = $game['thread_id'];
  $output .= "<form name='new_thread'>\n";
  $output .= "<input type='text' name='thread' value='$thread_id' />\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_thread()' />\n";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()' />\n";
  $output .= "</form>\n";

  return $output;
}

function thread_submit($game_id,$thread_id) {
  $cache = init_cache();
  $sql = sprintf("update Games set thread_id=%s where id=%s",quote_smart($thread_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $cache->clean('front-signup-' . $game_id);
  $cache->clean('front-signup-fast-' . $game_id);
  $cache->clean('front-signup-swf-' . $game_id);
  $cache->remove('game-' . $game_id, 'front');
  return show_thread_id($game_id,true);
}

function subt_form($game_id){
  $output = "If your game has sub-threads associated with it, such as threads where team-member can discussthings.  Then you add them here.  Once you have added the BGG thead_id you can edit that 'game' page just as you are editing this one.<br /><br />";
  $sql = sprintf("select * from Games where parent_game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $output .=  $row['title']." - ".$row['thread_id']." <a href='javascript:submit_subt(\"".$row['thread_id']."\",\"delete\")'>delete</a><br />\n";
  }
  $output .= "<form name='new_subt'><input type='text' name='tid' />\n";
  $output .= "<a href='javascript:submit_subt(\"x\",\"add\")'>Add a Sub-Thread</a><br />\n";
  $output .= "<br /><input type='button' name='cancel' value='cancel' onClick='clear_edit()' />";
  $output .= "</form>\n";

  return $output;
}

function subt_submit($game_id,$subthread_id,$action) {
  if ( $action == "add" ) {
    $sql = sprintf("insert into Games (id, title, status, thread_id, parent_game_id) values ( NULL, 'Sub-Thread', 'Sub-Thread', %s, %s)",($subthread_id),quote_smart($game_id));
    $result = mysql_query($sql);
    $new_game_id = mysql_insert_id();
    $sql = sprintf("select user_id from Moderators where game_id=%s",quote_smart($game_id));
    $result = mysql_query($sql);
    while ( $mod = mysql_fetch_array($result) ) {
      $sql2 = "insert into Moderators (user_id, game_id) values ('".$mod['user_id']."', '$new_game_id')";
      $result2 = mysql_query($sql2);
    }
  } elseif ($action == "delete" ) {
    $sql = sprintf("select id from Games where thread_id=%s",quote_smart($subthread_id));
    $result = mysql_query($sql);
    $st_game_id = mysql_result($result,0,0);
    $sql = "delete from Games where id ='$st_game_id'";
    $result = mysql_query($sql);
  } 
  return show_subthreads($game_id);
}

function desc_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "Edit the game description.  To format your text you must use html.<br /><br />";
  $output .= "<form name='new_descrip'>\n";
  $description = $game['description'];
  $output .= "<textarea name='desc' rows='5' cols='50'>$description</textarea>\n";
  $output .= "<br /><input type='button' name='submit' value='submit' onClick='submit_desc()' />\n";
  $output .= "<input type='button' name='cancel' value='cancel' onClick='clear_edit()' />\n";
  $output .= "</form>\n";

  return $output;
}

function desc_submit($game_id,$desc) {
  $desc = safe_html($desc,"<a>");
  $sql = sprintf("update Games set description=%s where id=%s",quote_smart($desc),quote_smart($game_id));
  $result = mysql_query($sql);
  return show_description($game_id,true);
}

function vote_tally_form($game_id) {
  $output = "<form name='tiebreaker'>Choose a Tie Breaker:<br/>";
  $options['lhlv'] = "Longest Held Last Vote";
  $options['lhv'] = "Longest Held Vote";
  $output .= create_dropdown('tieb','lhlv',$options);
  $output .= "<input type='button' value='submit' onClick='javascript:submit_vote_tally(\"activate\")'></form>";
  $output .= "<br>Allow nightfall votes? <input type='checkbox' name='allow_nightfall' id='allow_nightfall' checked=1 />";
  $output .= "<br>Allow No Lynch votes? <input type='checkbox' name='allow_nolynch' id='allow_nolynch' checked=1 />";  
  return $output;
}

function vote_tally_submit($game_id,$action,$tiebreaker) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  if ( $action == "activate" ) {
    $sql = sprintf("update Games set auto_vt=%s where id=%s",quote_smart($toebrealer),quote_smart($game_id));
    $result = mysql_query($sql);
    $message = file_get_contents("cassy_vote_tally.txt");
    $message .= "\n";
    if ( $tiebreaker == "lhv") {
      $message .= "Your Moderator has chosen to use the Longest Held Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n\n";
    } else {
      $message .= "Your Moderator has chosen to use the Longest Held Last Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n\n";
    }
    $message .= "Vote Log Page: http://cassandrawerewolf.com/game/".$game['thread_id']."/votes\n";
    $message .= "Vote Tally Page: http://cassandrawerewolf.com/game/".$game['thread_id']."/tally\n";
    BGG::authAsCassy()->reply_thread($game['thread_id'],$message);
  } elseif ($action == "retrieve" ) {
    $sql = sprintf("update Games set updated_tally=1 where id=%s",quote_smart($game_id));
    $result = mysql_query($sql);
    $sql = sprintf("update Post_collect_slots set last_dumped=NULL where game_id=%s",quote_smart($game_id));
    $result = mysql_query($sql);
  }

  return;
}

function goa_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output .= "<div align='left'>";
  $output .= "<p>Please choose the type of players that the player gets to choose from for their game actions (all actions see the same list) and if they get a user defined field:</p>";
  $output .= "<ul><li>none - no player list</li>";
  $output .= "<li>alive - can only choose from living players</li>";
  $output .= "<li>dead - can only choose from dead players</li>";
  $output .= "<li>all - can choose any player.</li>";
  $output .= "<li>checkbox - can input a player defined value.</ul>";
  $output .= "<p>The Order description should fit in to the phrase 'Player: _______ Player'. Put commas between words to give the player more than one game order possibility.</p>";
  $output .= "<p>You can group players together so that they can see eachother's orders.  This is needed for wolves and maybe masons.  To do this just give them all the same group name.  Make sure you also give them all the same action description.  If they are not part of a group leave it blank.</p>";
  $output .= "</div>";
  $output .= "<form name='game_orders' method='post' action='".$_SERVER['PHP_SELF']."'>";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />";
  $output .= "<table class='forum_table'>";
  $output .= "<tr><th>Player</th><th>Order Description</th><th>Game Order Choices</th><th>Group Name</th></tr>";
  $sql = sprintf("select * from Players, Users where Players.user_id=Users.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $player = mysql_fetch_array($result) ) {
    $output .= "<tr>";
    $output .= "<td>".$player['name']."</td>";
    $output .= "<td><input type='text' width='50' id='desc_".$player['user_id']."' name='desc_".$player['user_id']."' value='".$player['ga_desc']."' />";
    $output .= "<td align='center'>";
    $sql2="show columns from Players where field='game_action'";
    $result2=mysql_query($sql2);
    unset($options);
    while ($row=mysql_fetch_row($result2)) {
      foreach(explode("','",substr($row[1],6,-2)) as $v) {
        $options[$v] = $v;
      }
    }
    $output .= create_dropdown('na_'.$player['user_id'],$player['game_action'],$options);
    $checked = "";
    if ( $player['ga_text'] != "" ) { $checked = "checked = 'checked'"; }
    $output .= "<input type='checkbox' name='text_".$player['user_id']."' id='text_".$player['user_id']."' $checked />";
    $output .= "</td>";
    $output .= "<td><input type='text' width='50' id='group_".$player['user_id']."' name='group_".$player['user_id']."' value='".$player['ga_group']."' />";
    $output .= "</tr>";
  }
  $output .= "<tr><td align='center' colspan='4'><input type='submit' name='submit_goa' value='submit' /></td></tr>";
  $output .= "</table>";
  $output .= "</form>";
  $output .= "<span align='right'><a href='javascript:close()'>[close]</a></span></p>";
  return $output;
}

function goa_submit($data) {
  $sql = sprintf("update Games set game_order='on' where id=%s",quote_smart($data['game_id']));
  $result = mysql_query($sql);
  $sql = sprintf("select * from Players where game_id=%s",quote_smart($data['game_id']));
  $result=mysql_query($sql);
  while ( $player = mysql_fetch_array($result) ) {
    $sql_update = sprintf("update Players set game_action=%s, ga_desc=%s, ga_text=%s, ga_group=%s where user_id=%s and game_id=%s",quote_smart($data['na_'.$player['user_id']]),quote_smart($data['desc_'.$player['user_id']]),quote_smart($data['text_'.$player['user_id']]),quote_smart($data['group_'.$player['user_id']]),quote_smart($player['user_id']),quote_smart($data['game_id']));
    $result_update = mysql_query($sql_update);
  }
  error("Game Order Assistant has been submitted");
  return;

}

function mpw_form($game_id) {
  global $game;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  $output = "<form name='missing'>";
  $output .= "Warn if a player hasn't posted in<input type='text' size='2' name='hr' value='".$game['missing_hr']."'/> hours.<br />";
  $output .= "<input type='button' value='submit' onClick='javascript:submit_mpw()' />";
  $output .= "<span align='right'><a href='javascript:close()'>[close]</a></span></form>";
  return $output;
}

function mpw_submit($game_id,$hr) {
  if ( $hr == "0" || $hr == "" ) {
    $sql = sprintf("update Games set missing_hr=NULL where id=%s",quote_smart($game_id));
  } else {
    $sql = sprintf("update Games set missing_hr=%s where id=%s",quote_smart($hr),quote_smart($game_id));
  }
  $result = mysql_query($sql);
  return " ";
}

function random_selector_tool($list) {
  $output = "<div><a href='javascript:random_selector()'>Random Selector Tool</a></div>\n";
  $output .= "<div id='rand_select_space' style='position:absolute; visibility:hidden; background-color:white; border:1px solid black;'></div>";
  $output .= "<script language='javascript'>";
  $output .= "//<!--\n";
  $string = "";
  $count = 0;
  foreach ( $list as $item ) {
    if ( $string != "" ) { $string .= ", "; }
    $string .= '"'.$item.'"';
    $count ++;
  }
  $output .= "var item_list = new Array($string)\n";
  $output .= "function random_selector() {\n";
  $output .= "  myelement = document.getElementById(\"rand_select_space\")\n";
  $output .= "  myelement.style.visibility='visible'\n";
  $output .= "  myelement.innerHTML = \"<form><table class='forum_table'>\"\n";
  $output .= "  myelement.innerHTML += \"<tr><td>Choose:</td><td><input type='text' size='2' id='rand_count' value='1' /></td></tr>\"\n";
  $output .= "  myelement.innerHTML += \"<tr><td><input type='checkbox' onClick=select_all() id='all' /></td><td>Select All</td></tr>\"\n";
  $output .= "  for(var i=0; i < $count; i++) {\n";
  $output .= "    myelement.innerHTML += \"<tr><td><input type='checkbox' id='\"+item_list[i]+\"' /></td><td>\"+item_list[i]+\"</td></tr>\"\n";
  $output .= "  }\n";
  $output .= "  myelement.innerHTML += \"<tr><td colspan='2'><input type='button' value='Submit' onClick='select_random()' /></td></tr>\"\n";
  $output .= "  myelement.innerHTML += \"</table>\"\n";
  $output .= "  myelement.innerHTML += \"<span align='right'><a href='javascript:close_rand_space()'>[close]</a></span>\"\n";
  $output .= "  myelement.innerHTML += \"</forum>\"\n";
  $output .= "}\n";

  $output .= "function select_all() {\n";
  $output .= "  if ( document.getElementById('all').checked ) {\n";
  $output .= "    for(var i=0; i < $count; i++) {\n";
  $output .= "      document.getElementById(item_list[i]).checked = true\n";
  $output .= "    }\n";
  $output .= "  } else {\n";
  $output .= "    for(var i=0; i < $count; i++) {\n";
  $output .= "      document.getElementById(item_list[i]).checked = false\n";
  $output .= "    }\n";
  $output .= "  }\n";
  $output .= "}\n";

  $output .= "function select_random() {\n";
  $output .= "  var rand_list = new Array()\n";
  $output .= "  c = 0;\n";
  $output .= "  for(var i=0; i < $count; i++) {\n";
  $output .= "    if ( document.getElementById(item_list[i]).checked ) {\n";
  $output .= "     rand_list[c] = item_list[i]\n";
  $output .= "     c++\n";
  $output .= "    }\n";
  $output .= "  }\n";
  $output .= "  num = document.getElementById('rand_count').value\n";
  $output .= "  if ( num <= c ) {\n";
  $output .= "    var r_list = new Array()\n";
  $output .= "    for(var i=0; i < num; i++ ) {\n";
  $output .= "      r = Math.floor(Math.random()*c)\n";
  $output .= "        while ( in_array(r,r_list) ) {\n";
  $output .= "        r = Math.floor(Math.random()*c)\n";
  $output .= "        }\n";
  $output .= "      r_list[i] = r\n";
  $output .= "    }\n";
  $output .= "  }\n";
  $output .= "  myelement = document.getElementById(\"rand_select_space\")\n";
  $output .= "  myelement.style.visibility='visible'\n";
  $output .= "  myelement.innerHTML = \"\"\n";
  $output .= "  if ( num <= c ) {\n";
  $output .= "    myelement.innerHTML += \"<ul>\"\n";
  $output .= "    for(var i=0; i<num; i++ ) {\n";
  $output .= "        myelement.innerHTML += \"<li>\"+rand_list[r_list[i]]+\"</li>\"\n";
  $output .= "    }\n";
  $output .= "    myelement.innerHTML += \"</ul>\";\n";
  $output .= "  } else {\n";
  $output .= "    myelement.innerHTML += \"Not enough items<br />selected to provide<br />results.\";\n";
  $output .= "  }\n";
  $output .= "    myelement.innerHTML += \"<br /><span align='right'><a href='javascript:close_rand_space()'>[close]</a></span>\"\n";
  $output .= "    myelement.innerHTML += \"<br /><span align='right'><a href='javascript:random_selector()'>[repeat]</a></span>\"\n";
  $output .= "}\n";

  $output .= "function in_array(mystring,myarray) {\n";
  $output .= "  for (var i=0; i< myarray.length; i++ ) {\n";
  $output .= "    if ( mystring == myarray[i] ) {\n";
  $output .= "      return true;\n";
  $output .= "    }\n";
  $output .= "  }\n";
  $output .= "  return false;\n";
  $output .= "}\n";
  $output .= "function close_rand_space() {\n";
  $output .= "  document.getElementById(\"rand_select_space\").style.visibility=\"hidden\"\n";
  $output .= "}\n";
  $output .= "//-->\n";
  $output .= "</script>\n";
  return $output;
}

function assign_roles_form($game_id) {
  $sql = sprintf("select role_name from Players where game_id=%s order by role_name",quote_smart($game_id));
  $result = mysql_query($sql);
  $num_players = mysql_num_rows($result);
  $output = "<form name='roles' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<input type='hidden' name='num' value='$num_players' />\n";
  $output .= "<table class='forum_table'>\n";
  $output .= "<tr><th>Enter the Names of all Roles</th><th>hide</th></tr>\n";
  $i=0;
  while ( $player = mysql_fetch_array($result) ) {
    $output .= "<tr><td align='center'><input type='text' name='role_$i' value='".$player{'role_name'}."' /></td><td><input type='checkbox' name='hide_$i' /></td></tr>\n";
    $i++;
  }
    $output .= "<tr><td align='center' colspan='2'><input type='submit' name='submit_assign_roles' value='submit' /></td></tr>\n";
    $output .= "</table>\n";
    $output .= "</form>\n";
    $output .= "<p>If a role is a hidden role<br />click the 'hide' checkbox.<br />\n";
    $output .= "<span align='right'><a href='javascript:close()'>[close]</a></span></p>\n";
  return $output;
}

function assign_roles_submit($data) {
  $sql = sprintf("select user_id from Players where game_id=%s",quote_smart($data['game_id']));
  $result = mysql_query($sql);
  $players = array();
  while ( $row = mysql_fetch_array($result) ) {
    $players[] = $row['user_id'];
  }
  $roles = array();
  for ( $i=0; $i<$data['num'];$i++ ) {
    $roles[] = $data['role_'.$i];
  }
  shuffle($players);
  shuffle($roles);
  for ( $i=0; $i<$data['num'];$i++ ) {
    for ( $j=0; $j<$data['num']; $j++ ) {
      if ( $roles[$i] == $data['role_'.$j] && $data['hide_'.$j] == "on" ) {
        $hide = true;
        break;
      } else {
        $hide = false;
      }
    }
    if ( $hide ) {
      $sql = sprintf("select mod_comment from Players where user_id=%s and game_id=%s",$players[$i],quote_smart($data['game_id']));
      $result = mysql_query($sql);
      $mod_comment = mysql_result($result,0,0);
      $sql = sprintf("update Players set mod_comment=%s where user_id=%s and game_id=%s",quote_smart($mod_comment." ".$roles[$i]),$players[$i],quote_smart($data['game_id']));
      $result = mysql_query($sql);
    } else {
      $sql = sprintf("update Players set role_name=%s where user_id=%s and game_id=%s",quote_smart($roles[$i]),$players[$i],quote_smart($data['game_id']));
      $result = mysql_query($sql);
    }
  }
  error("Roles have been randomly assigned.");
  return;
}

function pm_players_form($game_id) {
  global $domain;
  $output = "<form name='pm_player' action='$domain/pm_players.php' method='POST'>\n";
  $output .= "<table border='0' class='forum_table' >\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $sql = sprintf("select id, name from Users_game_all, Users where Users_game_all.user_id = Users.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $player = mysql_fetch_array($result) ) {
    $output .= "<tr>";
    $output .= "<td><input type='checkbox' name='".$player['name']."' value='".$player['id']."' /></td>";
    $output .= "<td>".$player['name']."</td>";
    $output .= "</tr>\n";
  }
  $output .= "<tr><td><input type='checkbox' name='all' /></td><td><b>ALL la</b></td></tr>\n";
  $output .= "<tr><td colspan='2'><input type='submit' name='submit' value='PM Selected Players' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "<span align='right'><a href='javascript:close_pm()'>[close]</a></span>\n";
  $output .= "</form>\n";

  return $output;
}

function createPlayer_table($edit,$game_id) {
  global $domain, $game, $uid, $rep_id;
  if ( $game['id'] != $game_id ) {
    $game = get_game_info($game_id,"game");
  }
  list ($status, $subthread) = get_game_status($game['status'],$game['parent_game_id']);
  $show_alias = false;
  $show_alias_values = false;
  if ( $game['alias_display'] != 'None' ) { $show_alias = true; }
  if ( $game['alias_display'] == 'Public' ) { $show_alias_values = true; } 
print "VOTE: $show_alias <br />";
  $sql = sprintf("SELECT CASE WHEN Games.status =  'Sign-Up' THEN CONCAT( COUNT( * ) ,  '/', max_players ) ELSE CONCAT( SUM( CASE WHEN (death_phase IS NULL OR death_phase = 'Alive' OR death_phase = '') THEN 1 ELSE 0 END ) ,  '/', COUNT( * ) ) END FROM Players_r, Games WHERE Games.id =%s AND Players_r.game_id = Games.id",quote_smart($game_id));
  $result = mysql_query($sql);
  $players_total = mysql_result($result,0,0);
  $sql = sprintf("select Users.id as uid, name, role_name, `type`, side, death_phase, death_day, mod_comment, need_replace, player_alias, alias_color from Users, Players, Roles where Users.id=Players.user_id and Players.role_id=Roles.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  if ( $edit ) {
    $edit_col[] = "Edit";
  }
  $replace[] = "Repl";
  if ( $show_alias ) {
    $alias[] = "Alias";
  } 
  $players[] = "Players ($players_total)";
  $role_name[] = "Role Name";
  $role_type[] = "Role Type";
  $team[] = "Team";
  $death[] = "Death";
  $death_color[] = "#000000";
  $comment[] = "Comment";
  $count = 0;
  while ( $row = mysql_fetch_array($result) ) {
    $players[] = display_player($row['name'],$row['uid'],$game_id);
    if ( $edit ) { $edit_col[] = "<a href='javascript:edit_player(\"".$row['uid']."\",\"$count\")'><img src='$domain/images/edit.png' border='0' /></a>"; }
    if ( $row['need_replace'] != "" ) {
      // Player needs to be replaced
      $replace[] = "<a href='javascript:go_replace(\"".$row['uid']."\",\"I_replace\")' onMouseOver='show_hint(\"Click to Replace this Player\")' onMouseOut='hide_hint()'><img src='$domain/images/i_replace.png' border='0' /></a>";
    } else {
      if ( ($row['uid'] == $uid || $uid == $rep_id) || ( $edit && $status == "In Progress" ) ) {
        // Icon to request a replacement player
        $replace[] = "<a href='javascript:go_replace(\"".$row['uid']."\",\"replace_me\");' onMouseOver='show_hint(\"Click to Request a Replacement\")' onMouseOut='hide_hint()'><img src='$domain/images/replace_me.png' border='0' /></a>";
      } else {
        $replace[] = "";
      }
      $count++;
    }
    $death[] = $row['death_phase']." ".$row['death_day'];
    if ( $row['death_phase'] == "Alive" ) {
      $death_color[] = "white";
    } else {
      $death_color[] = "#F5F5FF";
    }
    $view = false;
    $viewown = false;
    if ( isset($uid) && $uid == $row['uid'] ) { $viewown = true; }
    if ( isset($uid) && $uid == $rep_id ) { $viewown = true; }
    if ( isset($moderator) && $moderator ) { $view = true; }
    if ( isset($finished) && $finished ) { $view = true; }
    if ( $edit ) { $view = true; }
    if ( $view || $viewown) {
      $role_name[] = $row['role_name'];
      $role_type[] = $row['type'];
      $team[] = $row['side'];
	  if ( $show_alias ) { $alias[] = $row['player_alias']; }
      if ( $view ) {
        $comment[] = $row['mod_comment'];
     } else {
        $comment[] = "";
      }
    } else {
      $role_name[] = "";
      $role_type[] = "";
      $team[] = "";
      $comment[] = "";
	  if ($show_alias_values) { $alias[] = $row['alias_color'] ? "<span style='color:".$row['alias_color'].";'>".$row['player_alias']."</span>" : $row['player_alias']; }
    }
  }

  $attrs = array (
      'border' => '0',
      'class' => 'forum_table',
      'cellpadding' => '4',
      'cellspacing' => '2'
  );

  $table =& new HTML_Table($attrs);

  if ( $edit ) { $table->addCol($edit_col); }
  if ( $status == "In Progress" ) { $table->addCol($replace); }
  $table->addCol($players);
  if ( $show_alias) { $table->addCol($alias); }
  $table->addCol($role_name);
  $table->addCol($role_type);
  $table->addCol($team);
  $table->addCol($death);
  $table->addCol($comment);

  $sql = sprintf("select count(*) from Posts where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $num_post = mysql_result($result,0,0);

  $i = 0;
  if ( $edit ) {
    $table->setHeaderContents(0,0,"Edit");
    $i++;
    if ( $status == "In Progress" ) {
      $table->setHeaderContents(0,0+$i,"Repl");
      $i++;
    }
    $content = create_edit_div($edit,'add_player','Click to Add a player','add_player()',"<img src='$domain/images/add.png' border='0' width='15px' height='15px' />");
    $table->setHeaderContents(0,0+$i,"$content Players ($players_total) <a href='$domain/game/".$game['thread_id']."/all'>($num_post posts)</a>");
  } else {
    if ( $status == "In Progress" ) {
      $table->setHeaderContents(0,0+$i,"Repl");
      $i++;
    }
    $table->setHeaderContents(0,0+$i,"Players ($players_total) <a href='$n/game/".$game['thread_id']."/users'>($num_post posts)</a>");
  }
  if ( $show_alias) {
    $table->setHeaderContents(0,0+$i,"Aliases");
    $i++;
  }
  $content = create_edit_div($edit,'role_names','Click to change all Roles Names','edit_rolename()','Role Name');
  $table->setHeaderContents(0,1+$i,$content);
  $contet = create_edit_div($edit,'role_types','Click to change all Role Types','edit_roletype()','Role Type');
  $table->setHeaderContents(0,2+$i,$content);
  $content = create_edit_div($edit,'teams','Click to change all Teams','edit_teams()','Team');
  $table->setHeaderContents(0,3+$i,$content);
  $content = create_edit_div($edit,'death','Click to change all Deaths','edit_deaths()','Death');
  $table->setHeaderContents(0,4+$i,$content);
  $table->setHeaderContents(0,5+$i,"Comment");

  $row_count = $table->getRowCount();
  if ( $edit ) {
    for ( $r=1; $r<$row_count; $r++ ) {
      for ( $c=1; $c<6; $c++ ) {
        $ro = $r - 1;
        $co = $c - 1;
        $table->setCellAttributes($r,$c,"id='r${ro}_c$co'");
      }
    }
  }
  for ( $r=1; $r<$row_count; $r++ ) {
    $table->setRowAttributes($r,"style='background-color:".$death_color[$r].";'");
  }

  $output = $table->toHTML();
  return $output;
}

function display_player($name,$user_id,$game_id) {
  global $rep_id;
  $rep_id = 0;
  $replace = find_Replacements($user_id,$game_id);
  $sql2 = sprintf("select count(*) from Posts where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($user_id));
  $result2 = mysql_query($sql2);
  $num_post = mysql_result($result2,0,0);
  $current_id = $user_id;
  $current_num_post = $num_post;
  if ( $replace != "" ) {
    $sql2 = sprintf("select replace_id from Replacements where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($user_id));
    $result2 = mysql_query($sql2);
    $current_id = mysql_result($result2,mysql_num_rows($result2)-1,0);
    $sql2 = sprintf("select count(*) from Posts where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($current_id));
    $result2 = mysql_query($sql2);
    $current_num_post = mysql_result($result2,0,0);
  }
  $sql2 = sprintf("select death_phase, status from Players, Games where Players.game_id=Games.id and game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($user_id));
  $result2 = mysql_query($sql2);
  $dead = false;
  if ( (mysql_result($result2,0,0) != "" && mysql_result($result2,0,0) != "Alive") ||  mysql_result($result2,0,1) == "Finished" ) { $dead = true; }
  if ( $current_num_post > 0 ) {
    $sql2 = sprintf("select max(time_stamp) as last_post, if(date_add(max(time_stamp),interval missing_hr hour) < now(), 'Yes','No') as missing from Posts, Games where Posts.game_id=Games.id and game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($current_id));
  } else {
    $sql2 = sprintf("select 'Never' as last_post, if(date_add(start_date, interval missing_hr hour) < now(), 'Yes', 'No') as missing from Games where id=%s",quote_smart($game_id));
  }
  $result2 = mysql_query($sql2);
  $last_post = mysql_result($result2,0,0);
  $missing = mysql_result($result2,0,1);
  $this_player = "";
  if ( $missing == "Yes" && !$dead) {
     $this_player .=  "<span onMouseOver='javascript:{document.getElementById(\"${user_id}_lp\").style.visibility=\"visible\";}' ";
     $this_player .=  "onMouseOut='javascript:{document.getElementById(\"${user_id}_lp\").style.visibility=\"hidden\";}' >";
     $this_player .= "<img src='/images/warning.png' border='0' /> </span>";
     $this_player .= "<span id='${user_id}_lp' style='position:absolute;border:solid black 1px; background-color:white; visibility:hidden;'>Last Post: $last_post</span> ";
  }
  $this_player .= get_player_page($name);
  $this_player .= " <a href='$posts$name'>($num_post posts)</a>".$replace;

  return $this_player;
}

function find_Replacements($user_id,$game_id) {
  global $rep_id;
  $sql = sprintf("Select name, replace_id as id, substring(period,1,1) as p, number from Users, Replacements where Users.id=Replacements.replace_id and game_id=%s and user_id=%s order by number, period",quote_smart($game_id),quote_smart($user_id));
  $result = mysql_query($sql);
  $count = 0;
  $replace = "";
  while ( $rep = mysql_fetch_array($result) ) {
    $rep_id = $rep['id'];
    $sql2 = sprintf("select count(*) from Posts where game_id=%s and user_id='".$rep['id']."'",quote_smart($game_id));
    $result2 = mysql_query($sql2);
    $num_post = mysql_result($result2,0,0);
    if ( $count == 0 ) {
      $replace = "<br /> (replaced by ";
      $replace .= get_player_page($rep['name']);
      $replace .= " <a href='$posts".$rep['name']."'>($num_post posts)</a> on ".$rep['p'].$rep['number'];
    } else {
      $replace .= ",<br /> ";
      $replace .= get_player_page($rep['name']);
      $replace .= " <a href='$posts".$rep['name']."'>($num_post posts)</a> on ".$rep['p'].$rep['number'];
    }
    $count++;
  }
  if ( $replace != "" ) $replace .= ")";

  return $replace;
}


?>


