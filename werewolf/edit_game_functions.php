<?php

include_once "HTML/Table.php";
include_once "php/db.php";
include_once "autocomplete.php";
include_once "php/common.php";
dbConnect();

$here = "/";
$player = "${here}player/";
$game_page = "${here}game/";
#$game_page = "${here}dev_game/";
$posts = "";

function setPostsPath($game_id) {
global $here, $posts;
$sql = sprintf("select thread_id from Games where id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$thread_id = mysql_result($result,0,0);
$posts = "${here}game/$thread_id/";
}

function show_moderator($game_id) {
 global $player, $posts;
 if ( $posts == "" ) { setPostsPath($game_id); }
  $sql = sprintf("Select id, name from Users, Moderators where Users.id=Moderators.user_id and Moderators.game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  $count = 0;
  $output = "";
  while ( $mod = mysql_fetch_array($result) ) {
    $sql2 = sprintf("Select count(*) from Posts where game_id=%s and user_id='".$mod['id']."'",quote_smart($game_id));
    $result2=mysql_query($sql2);
    $num_post=mysql_result($result2,0,0);
    if ( $count == 0 ) { 
	  $output = get_player_page($mod['name']);
	  $output .= " <a href='$posts".$mod['name']."'>($num_post post)</a>";
	} else {
	  $output .= ", ";
	  $output .= get_player_page($mod['name']);
	  $output .= " <a href='$posts".$mod['name']."'>($num_post post)</a>";
	}
    $count++;
  }
  print $output;
}

function edit_moderator($game_id) {
  $output = "<form name='change_mod'>\n";
  $output .= "<select name='moderator[]' size='25' multiple>\n";
  $sql = sprintf("select user_id from Users, Moderators where Moderators.user_id=Users.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $id[]=$row['user_id'];
  }
  $sql="Select id, name from Users where level != '0' order by name";
  $result = mysql_query($sql);
  $i = 0;
  while ( $row = mysql_fetch_array($result) ) {
    $selected = "";
    if ( $row['id'] == $id[$i] ) {
      $selected = "selected";
	  $i++;
    }
    $output .= "<option $selected value='".$row['id']."' />".$row['name']."\n";
  }

  $output .= "</select>\n";
  $output .= "<input type=button value='submit' name='submit' onClick='submit_Moderators()' />\n";
  $output .= "</form>\n";

  print $output;
}

function show_dates($game_id) {
  global $open_comment, $close_comment;
  $format = "'%b %e, %Y'";
  $format2 = "'%l:%i %p'";
  $sql = sprintf("select date_format(start_date, %s) as start, date_format(start_date, %s) as start_time, date_format(end_date, %s) as end, swf, status, deadline_speed from Games where id=%s",$format,$format2,$format,quote_smart($game_id));
  $result = mysql_query($sql);
  $date = mysql_fetch_array($result);
  $content = $date['start']." to ".$date['end'];
  if ( $date['status'] == "Sign-up" ) {
    if ( $date['deadline_speed'] == "Fast" ) { $content = $date['start']." ".$date['start_time']." to ".$date['end']; }
    if ( $date['swf'] == "Yes" ) {  $content = "Starts When Full"; }
  }
  $output = "<div $open_comment onMouseOver='show_hint(\"Click to Edit Dates\")' onMouseOut='hide_hint()' onClick='edit_dates()' $close_comment>".$content."</div>";

  print $output;
}

function edit_dates($game_id) {
  $format = "'%Y-%m-%d'";
  $format2 = "'%H:%i'";
  $output = "<form name='edit_date'>\n";
  $sql = sprintf("select date_format(start_date, %s) as start, date_format(start_date, %s) as start_time, date_format(end_date, %s) as end, swf, status, deadline_speed from Games where id=%s", $format,$format2,$format,quote_smart($game_id));
  $result = mysql_query($sql);
  $date = mysql_fetch_array($result);
  $checked = "";
  $value = $date['swf'];
  if ( $date['swf'] == "Yes" ) { $checked = "checked='checked'"; }
  if ( $date['status'] == "Sign-up") {
    $output .= "<input type='checkbox' name='swf' value='No' $checked /> Starts when full<br />\n";
  } else {
    $output .= "<input type='hidden' name='swf' value='$value' />\n";
  }
  $output .= "<input type=text name='start' value='".$date['start']."' />";
  if ( $date['deadline_speed'] == "Fast" ) { 
    $output .= time_dropdown('start_time',$date['start_time'],false,false); 
  } else {
    $output .= "<input type='hidden' name='start_time' value='00:00' />\n";
  }
  $output .= " to ";
  $output .= "<input type=text name='end' value='".$date['end']."' />\n";
  $output .= "<br /><input type='button' name='submit' value='submit' onClick='submit_dates()'/>\n";
  $output .= "</form>";

  print $output;
}

function edit_description($game_id) {
  $output = "<form name='new_descrip'>\n";
  $sql = sprintf("select description from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $description = mysql_result($result,0,0);
  $output .= "<textarea name='desc' rows='5' cols='50'>$description</textarea>\n";
  $output .= "<br /><input type='button' name='submit' value='submit' onClick='submit_desc()' />\n";
  $output .= "</form>\n";

  print $output;
}

function edit_status($game_id) {
  $output = "<form name='new_status'>\n";
  $output .= "<select name='status'>\n";
  $sql=sprintf("select status, phase, day from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $status = mysql_result($result,0,0);
  $phase = mysql_result($result,0,1);
  $day = mysql_result($result,0,2);
  $sql="show columns from Games where field='status'";
  $result=mysql_query($sql);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      $selected = "";
	  if ( $status == $v ) { $selected = "selected"; }
	  $output .= "<option $selected value='$v'>$v</option>";
	  if ( $status == $v ) { break; }
	}
  }
  $output .= "</select><br />\n";
  $output .= "<select name='phase'>\n";
  $sql="show columns from Games where field='phase'";
  $result=mysql_query($sql);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      $selected = "";
	  if ( $phase == $v ) { $selected = "selected"; }
	  $output .= "<option $selected value='$v'>$v</option>";
	}
  }
  $output .= "</select>\n";
  $output .= "<input type='text' size='2' name='day' value='$day' />\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_status()' />\n";
  $output .= "</form>\n";

  print $output;
}

function edit_speed($game_id) {
  $output = "<form name='new_speed'>\n";
  $output .= "<select name='speed'>\n";
  $sql=sprintf("select deadline_speed from Games where id=%s",quote_smart($game_id));
  $result=mysql_query($sql);
  $speed = mysql_result($result,0,0);
  $output .= "Speed: ";
  $sql="show columns from Games where field='deadline_speed'"; 
  $result = mysql_query($sql);
  while ( $row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      $selected = "";
      if ( $speed == $v ) { $selected = "selected='selected'"; }
      $output .= "<option $selected value='$v'>$v</option>\n";
    }
  }
  $output .= "</select><br />\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_speed()' />\n";
  $output .= "</form>\n";
  print $output;
}

function edit_deadline($game_id) {
  $output = "<form name='new_deadline'>\n";
  $sql=sprintf("select lynch_time, na_deadline, day_length, night_length, deadline_speed from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  #$lynch_db = mysql_result($result,0,0);
  #$night_db = mysql_result($result,0,1);
  $lynch = mysql_result($result,0,0);
  $night = mysql_result($result,0,1);
  $day_length = mysql_result($result,0,2);
  $night_length = mysql_result($result,0,3);
  $speed = mysql_result($result,0,4);
  #list($lynch,$lmin) = split(":",$lynch_db);
  #list($night,$nmin) = split(":",$night_db);
  $output .= "<table>\n";
  if ( $speed == "Standard" ) {
    $output .= "<tr><td>Lynch:</td><td>".time_dropdown('lynch',$lynch,false,false)."</td></tr>\n";
    $output .= "<tr><td>Night Action:</td><td>".time_dropdown('night',$night,false,false)."</td></tr>\n";
    $output .= "<input type='hidden' name='day_length' value='$day_length' />\n";
    $output .= "<input type='hidden' name='night_length' value='$night_length' />\n";
  } else {
    $output .= "<tr><td>Day Length:</td><td>".time_dropdown('day_length',$day_length,true,false)."</td></tr>\n";
    $output .= "<tr><td>Night Length:</td><td>".time_dropdown('night_length',$night_length,true,false)."</td></tr>\n";
    $output .= "<input type='hidden' name='lynch' value='$lynch' />\n";
    $output .= "<input type='hidden' name='night' value='$night' />\n";
  }
  $output .= "<tr><td colspan='2' align='center'><input type='button' name='submit' value='submit' onClick='submit_deadline()' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";
  
  print $output;
}

function edit_winner($game_id) {
  $output = "<form name='new_winner'>\n";
  $output .= "<select name='winner'>\n";
  $sql=sprintf("select winner from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $winner = mysql_result($result,0,0);
  $sql="show columns from Games where field='winner'";
  $result=mysql_query($sql);
  while ($row=mysql_fetch_row($result)) {
    foreach(explode("','",substr($row[1],6,-2)) as $v) {
      $selected = "";
	  if ( $winner == $v ) { $selected = "selected"; }
	  $output .= "<option $selected value='$v'>$v</option>";
	}
  }
  $output .= "</select>\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_winner()' />\n";
  $output .= "</form>\n";

  print $output;
}

function edit_subt($game_id) {
  $sql = sprintf("select * from Games where parent_game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $output = "";
  while ( $row = mysql_fetch_array($result) ) {
    $output .=  $row['title']." - ".$row['thread_id']." <a href='javascript:delete_subt(\"".$row['thread_id']."\")'>delete</a><br />\n";
  }
  $output .= "<form name='new_subt'><input type='text' name='tid' />\n";
  $output .= "<a href='javascript:add_subt()'>Add a Sub-Thread</a><br />\n";
  $output .= "</form>\n";

  print $output;
}

function show_subt($game_id) {
  global $game_page, $open_comment, $close_comment;
  $sql = sprintf("select * from Games where parent_game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $output = "";
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<div $open_comment onMouseOver='show_hint(\"Click to Add or Delete a Sub-Thread\")' onMouseOut='hide_hint()' onClick='edit_subt()' $close_comment><a href='$game_page".$row['thread_id']."'>".$row['title']."</a><br /></div>\n";
  }

  print $output;
}

function edit_name($game_id) {
  $sql = sprintf("select title from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $title = mysql_result($result,0,0);
  $output = "<form name='new_title'>\n";
  $output .= "<input type='text' name='title' value='$title' />\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_name()'/>\n";
  $output .= "</form>\n";

  print $output;
}

function edit_thread($game_id) {
  $sql = sprintf("select thread_id from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $thread_id = mysql_result($result,0,0);
  $output = "<form name='new_thread'>\n";
  $output .= "<input type='text' name='thread' value='$thread_id' />\n";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_thread()' />\n";
  $output .= "</form>\n";

  print $output;
}

function createPlayer_table($edit,$game_id) {
  global $here, $posts, $uid, $finished, $rep_id, $status, $open_comment, $close_comment;
  if ( $posts == "" ) { setPostsPath($game_id); }
  $sql = sprintf("select alias_display, automod_id from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $show_alias = false;
  $show_alias_values = false;
  $alias_display = mysql_result($result,0,0);
  if ( $alias_display != 'None' ) { $show_alias = true; }
  if ( $alias_display == 'Public' ) { $show_alias_values = true; } 
  $is_automod = false;
  if ( mysql_result($result,0,1) != "" ) { $is_automod = true; }

  $sql = sprintf("SELECT CASE WHEN Games.status =  'Sign-Up' THEN CONCAT( COUNT( * ) ,  '/', max_players ) ELSE CONCAT( SUM( CASE WHEN (death_phase IS NULL OR death_phase = 'Alive' OR death_phase = '') THEN 1 ELSE 0 END ) ,  '/', COUNT( * ) ) END FROM Players_r, Games WHERE Games.id =%s AND Players_r.game_id = Games.id",quote_smart($game_id));
  $result = mysql_query($sql);
  $players_total = mysql_result($result,0,0);

  $sql = sprintf("select Users.id as uid, name, role_name, `type`, side, death_phase, death_day, mod_comment, need_replace, player_alias, alias_color, automod_role_id from Users, Players, Roles where Users.id=Players.user_id and Players.role_id=Roles.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  $edit_col[] = "Edit"; 
  $replace[] = "Repl";
  $players[] = "Players ($players_total)";
  $alias[] = "Alias";
  $role_id[] = "Automod Role ID";
  $role_name[] = "Role Name";
  $role_type[] = "Role Type";
  $team[] = "Team";
  $death[] = "Death";
  $death_color[] = "#000000";
  $comment[] = "Comment";
  $count = 0;
  while ( $row = mysql_fetch_array($result) ) {
	$players[] = display_player($row['name'],$row['uid'],$game_id);
    if ( $edit ) { $edit_col[] = "<a href='javascript:edit_player(\"".$row['uid']."\",\"$count\")'><img src='/images/edit.png' border='0' /></a>"; }
	if ( $row['need_replace'] != "" ) {
      // Player needs to be replaced
	  $replace[] = "<a href='javascript:go_replace(\"".$row['uid']."\",\"I_replace\")' onMouseOver='show_hint(\"Click to Replace this Player\")' onMouseOut='hide_hint()'><img src='/images/i_replace.png' border='0' /></a>"; 
	} else {
      if ( ($row['uid'] == $uid || $uid == $rep_id) || ( $edit && $status == "In Progress" ) ) {
        // Icon to request a replacement player
		$replace[] = "<a href='javascript:go_replace(\"".$row['uid']."\",\"replace_me\");' onMouseOver='show_hint(\"Click to Request a Replacement\")' onMouseOut='hide_hint()'><img src='/images/replace_me.png' border='0' /></a>";
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
	if ( $show_alias && 
	     ($view || $viewown || $show_alias_values) )
		{ $alias[] = $row['alias_color'] ? "<span style='color:".$row['alias_color'].";'>".$row['player_alias']."</span>" : $row['player_alias']; }
    else { $alias[] = "";}
	if ( $view || $viewown) {
      $role_name[] = $row['role_name'];
	  $role_type[] = $row['type'];
	  $team[] = $row['side']; 
	  if ( $view ) {
        $role_id[] = $row['automod_role_id'];
	    $comment[] = $row['mod_comment'];
      } else {
        $role_id[] = "";
        $comment[] = "";
	  }
	} else {
      $role_name[] = "";
	  $role_type[] = "";
	  $team[] = "";
	  $comment[] = "";
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
  if ( $is_automod ) { $table->addCol($role_id); }
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
	$i = 1;
	if ( $status == "In Progress" ) {
	  $table->setHeaderContents(0,0+$i,"Repl");
      $i = 2;
	}
    $table->setHeaderContents(0,0+$i,"<div $open_comment onMouseOver='show_hint(\"Click to Add a player\")' onMouseOut='hide_hint()' onClick='add_player()' $close_comment><img src='/images/add.png' border='0' width='15px' height='15px' /></div> Players ($players_total) <a href='${posts}all'>($num_post posts)</a>");
  } else {
	if ( $status == "In Progress" ) {
	  $table->setHeaderContents(0,0+$i,"Repl");
      $i = 1;
	}
    $table->setHeaderContents(0,0+$i,"<div $open_comment onMouseOver='show_hint(\"Click to Add a player\")' onMouseOut='hide_hint()' onClick='add_player()' $close_comment>Players ($players_total) <a href='${posts}users'>($num_post posts)</a></div>");
  }
  if ( $show_alias ) {
    $i++;
    $table->setHeaderContents(0,0+$i,"<div $open_comment onMouseOver='show_hint(\"Click to change all Aliases\")' onMouseOut='hide_hint()' onClick='edit_alias()' $close_comment>Alias</div>");
  }
  if ( $is_automod ) { 
    $i++;
    $table->setHeaderContents(0,0+$i,"Automod ID");
  }
  $table->setHeaderContents(0,1+$i,"<div $open_comment onMouseOver='show_hint(\"Click to change all Roles Names\")' onMouseOut='hide_hint()' onClick='edit_rolename()' $close_comment>Role Name</div>");
  $table->setHeaderContents(0,2+$i,"<div $open_comment onMouseOver='show_hint(\"Click to change all Role Types\")' onMouseOut='hide_hint()' onClick='edit_roletype()' $close_comment>Role Type</div>");
  $table->setHeaderContents(0,3+$i,"<div $open_comment onMouseOver='show_hint(\"Click to change all Teams\")' onMouseOut='hide_hint()' onClick='edit_teams()' $close_comment>Team</div>");
  $table->setHeaderContents(0,4+$i,"<div $open_comment onMouseOver='show_hint(\"Click to change all Deaths\")' onMouseOut='hide_hint()' onClick='edit_deaths()' $close_comment>Death</div>");
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

  echo $table->toHTML();

}

function display_player($name,$user_id,$game_id) {
  global $player, $posts, $rep_id;
  if ( $posts == "" ) { setPostsPath($game_id); }
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
  global $posts, $player, $rep_id;
  if ( $posts == "" ) { setPostsPath($game_id); }
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

function edit_player($user_id,$row,$game_id) {
  $output = "<form name='editPlayer'>\n";
  $output .= "<input type='hidden' name='user_id' value='$user_id' />\n";
  $output .= "<input type='hidden' name='row_id' value='$row' />\n";
  $sql = sprintf("select * from Users, Players where Users.id=Players.user_id and Users.id=%s and Players.game_id=%s",quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $data = mysql_fetch_array($result);
  $output .= "<table border='0'>";
  $output .= "<tr><th colspan='2'>Editing ".$data['name']."</th></tr>\n";
  # Show Players that have been asigned as replacements.
  $sql2 = sprintf("Select name, replace_id as id, period, number from Users, Replacements where Users.id=Replacements.replace_id and game_id=%s and user_id=%s order by number, period",quote_smart($game_id),quote_smart($user_id));
  $result2 = mysql_query($sql2);
  $count = 0;
  while ( $rep = mysql_fetch_array($result2) ) {
    $output .= "<tr><td align='right'>Replacement:</td>";
	$output .= "<td>".$rep['name']." on  ".$rep['period']." ".$rep['number'];
	$output .= " - <a href='javascript:delete_replacement(\"".$rep['id']."\")'>delete</a></td></tr>\n";
  }
  # Add a Replacement 
  $output .= "<tr><td align='right'>Add Replacement:</td><td>";
  $output .= player_dropdown("new_rep");
  $output .= "<select name='rep_period'>";
  $output .= "<option value='Day' />Day<option value='Night' />Night";
  $output .= "</select>";
  $output .= "<input type='text' name='rep_number' size='3' value='' /></td></tr>";
  # (Optional) Change Alias
  $sql_alias = sprintf("select alias_display from Games where id = %s",quote_smart($game_id));
  $result_alias = mysql_query($sql_alias);
  if (mysql_result($result_alias,0,0) != 'None')
  {
	$output .= "<tr><td align='right'>Alias:</td><td><input type='text' name='player_alias' value='".$data['player_alias']."' /><br /><input type='text' id='alias_color' name='alias_color' value='".$data['alias_color']."' size='8' /><a href='#' onClick='cp.select(document.editPlayer.alias_color,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a></td></tr>";
  } else {
	$output .= "<input type='hidden' name='player_alias' value='".$data['player_alias']."' />";
	$output .= "<input type='hidden' name='alias_color' value='".$data['alias_color']."' />";	
  }
  # Change Role Name
  $output .= "<tr><td align='right'>Role Name:</td><td><input type='text' name='role_name' value='".$data['role_name']."' /></td></tr>";
  # Change Role type
  $output .= "<tr><td align='right'>Role Type:</td><td>";
  $output .= roletype_dropdown("role_type",$data['role_id']);
  $output .= "</td></tr>";
  $output .= "<tr><td align='right'>Team:</td><td>";
  $output .= team_dropdown("side",$data['side']);
  $output .= "</td></tr>";
  $output .= "<tr><td align='right'>Death:</td><td>";
  $output .= phase_dropdown("d_phase",$data['death_phase']);
  $output .= "<input type='text' size='2' name='d_day' value='".$data['death_day']."' />";
  $output .= "</td></tr>";
  # Text box to inpupt a comment
  $output .="<tr><td align='right'>Your Comments:<br />(These won't be seen by others until you set the game status to finished.)</td><td><textarea name='comment' rows='3' cols='35'>".$data['mod_comment']."</textarea></td></tr>";

  $output .= "<tr><td colspan='2' align='center'><input type='button' name='submit' value='submit' onClick='submit_player()' /> <input type='button' name='delete' value='delete' onClick='delete_player()' /></td></tr>\n";
  $output .= "</form>\n";
  print $output;
}

function add_player($game_id) {
  $output = "<form name='add_pl'>";
  $output .= "<table border='0'><tr><td>";
  $output .= player_autocomplete_form("new_p");
  #$output .= player_dropdown("new_p");
  $output .= "</td><td>";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_new_player()' />";
  $output .= "</td></tr></table>";
  $output .= "</form>";

  print $output;
}

function player_dropdown($name) {
  $sql = "select id, name from Users where level != '0' order by name";
  $result = mysql_query($sql);
  $output .= "<select name='$name'>";
    $output .= "<option value='0' />\n";
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<option value='".$row['id']."' />".$row['name'];
  }
  $output .= "</select>\n";

  return $output;
}
function edit_alias($game_id) {
  $output = "<form name='change_aliases'><table border='0'>";
  $sql = sprintf("select name, id, player_alias, alias_color from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  $count  =0;
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<tr><td align='right'>".$row['name']."</td><td><input type='text' name='alias[$count]' value='".$row['player_alias']."' />";
	$count++;
	$output .= "<input type='text' id='alias_$count' name='alias[$count]' value='".$row['alias_color']."' size='8' /><a href='#' onClick='cp.select(document.change_aliases.alias_$count,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a>";
	$output .= "</td></tr>\n";
    $count++;
  }
  $output .= "<tr><td colspan='2' align='center'><input type='button' value='submit' name='submit' onclick='submit_alias()' /></td></tr>";
  $output .= "</table></form>";

  print $output;
}

function edit_rolename($game_id) {
  $output = "<form name='change_rolenames'><table border='0'>";
  $sql = sprintf("select name, id, role_name from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  $count  =0;
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<tr><td align='right'>".$row['name']."</td><td><input type='text' name='rname[$count]' value='".$row['role_name']."' /></td></tr>\n";
	$count++;
  }
  $output .= "<tr><td colspan='2' align='center'><input type='button' value='submit' name='submit' onclick='submit_rolename()' /></td></tr>";
  $output .= "</table></form>";

  print $output;
}

function edit_roletype($game_id) {
  $output = "<form name='change_roletypes'><table border='0'>";
  $sql = sprintf("select name, id, role_id from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<tr><td align='right'>".$row['name']."</td><td>";
	$output .= roletype_dropdown("rt_".$row['id'],$row['role_id']);
	$output .= "</td></tr>";
  }
  $output .= "<tr><td colspan='2' align='center' ><input type='button' value='submit' name='submit' onClick='submit_roletype()' /></tr></td>";
  $output .= "</table></form>";

  print $output;
}

function roletype_dropdown($name,$type) {
  $output .= "<select name='$name'>\n";
  $sql = "select * from Roles order by `type`";
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    if ( $row['id'] == $type ) {
      $output .= "<option selected value='".$row['id']."'>".$row['type'];
	} else {
      $output .= "<option value='".$row['id']."'>".$row['type'];
	}
  }
  $output .= "</select><br />";
 
  return $output;

}

function edit_team($game_id) {
  $output = "<form name='change_teams'><table border='0'>" ;
  $sql = sprintf("select name, id, side from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<tr><td align='right'>".$row['name']."</td><td>";
	$output .= team_dropdown("t_".$row['id'],$row['side']);
	$output .= "</td></tr>";
  }
  $output .= "<tr><td colspan='2' align='center'><input type='button' value='submit' name='submit' onclick='submit_team()' /></td></tr>";
  $output .= "</table></form>";

  print $output;
}

function team_dropdown($name,$side) {
  $output .= "<select name='$name'>";
  if ( $side == "Good" ) {
    $output .= "<option value='' />";
    $output .= "<option selected value='Good' />Good";
    $output .= "<option value='Evil' />Evil";
    $output .= "<option value='Other' />Other";
  } elseif ( $side == "Evil" ) {
    $output .= "<option value='' />";
    $output .= "<option value='Good' />Good";
    $output .= "<option selected value='Evil' />Evil";
    $output .= "<option value='Other' />Other";
  } elseif ( $side == "Other" ) {
    $output .= "<option value='' />";
    $output .= "<option value='Good' />Good";
    $output .= "<option value='Evil' />Evil";
    $output .= "<option selected value='Other' />Other";
  } else {
    $output .= "<option selected value='' />";
    $output .= "<option value='Good' />Good";
    $output .= "<option value='Evil' />Evil";
    $output .= "<option value='Other' />Other";
  }
  $output .= "</select><br />";

  return $output;
}

function edit_comment($game_id) {
   $output = "<form name='change_comments'><table border='0'>";
   $sql = sprintf("select name, id, mod_comment from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
   $result = mysql_query($sql);
   while ( $row = mysql_fetch_array($result) ) {
     $output .= "<tr><td align='right' valign='center'>".$row['name']."</td><td>";
     $output .= "<textarea name='c_".$row['id']."' rows='1' cols='30'>".$row['mod_comment']."</textarea></td></tr>";
   }
   $output .= "<tr><td colspan='2' align='center'><input type='button' value='submit' name='submit' onClick='sumbit_comment() /></td></tr>";
   $output .= "</table></form>";

   print $output;
}

function clear_editSpace() {
  global $here;
  print "You have edit permissions for this game.  Please click on something you wish to edit.  The edit dialogue will appear here.<br /><a href='${here}editgame_userguide.html'>Users Guide</a>";
}

function edit_maxplayers($game_id) {
  $output = "<form name='change_maxp'>";
  $sql = sprintf("select max_players from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $max_players = mysql_result($result,0,0);
  $output .= "<input type='text' name='max_players' value='$max_players' />";
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_maxplayers()' />";
  $output .= "</form>";
  
  print $output;
}

function edit_deaths($game_id) {
  $output = "<form name='change_deaths'><table border='0'>" ;
  $sql = sprintf("select name, id, death_phase, death_day from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<tr><td align='right'>".$row['name']."</td><td>";
	$output .= phase_dropdown("d_".$row['id'],$row['death_phase']);
	$output .= "<input type='text' size='2' name='d_day' value='".$row['death_day']."' />";
	$output .= "</td></tr>";
  }
  $output .= "<tr><td colspan='2' align='center'><input type='button' value='submit' name='submit' onclick='submit_deaths()' /></td></tr>";
  $output .= "</table></form>";

  print $output;
}

function phase_dropdown($name,$phase) {
  $output .= "<select name='$name'>";
  if ( $phase == "Day" ) {
    $output .= "<option value='' />";
	$output .= "<option value='Alive' />Alive";
    $output .= "<option selected value='Day' />Day";
    $output .= "<option value='Night' />Night";
  } elseif ( $phase == "Night" ) {
    $output .= "<option value='' />";
	$output .= "<option value='Alive' />Alive";
    $output .= "<option value='Day' />Day";
    $output .= "<option selected value='Night' />Night";
  } elseif ( $phase == "Alive" ) {
    $output .= "<option value='' />";
	$output .= "<option selected value='Alive' />Alive";
    $output .= "<option value='Day' />Day";
    $output .= "<option value='Night' />Night";
  } else {
    $output .= "<option selected value='' />";
	$output .= "<option value='Alive' />Alive";
    $output .= "<option value='Day' />Day";
    $output .= "<option value='Night' />Night";
  }
  $output .= "</select>";

  return $output;
}

function edit_complex($game_id) {
  $output = "<form name='comp_form'>";
  $sql = sprintf("select complex from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $complex = mysql_result($result,0,0);
  $output .= complex_dropdown($complex);
  $output .= "<input type='button' name='submit' value='submit' onClick='submit_complex()' />";
  $output .= "</form>";
  
  print $output;
}

function complex_dropdown($complex) {
  $output .= "<select name='complex'>";
  if ( $complex == "Newbie" ) {
    $output .= "<option value='' />";
    $output .= "<option selected value='Newbie' />Newbie";
    $output .= "<option value='Low' />Low";
    $output .= "<option value='Medium' />Medium";
    $output .= "<option value='High' />High";
    $output .= "<option value='Extreme' />Extreme";
  } elseif ( $complex == "Low" ) {
    $output .= "<option value='' />";
    $output .= "<option value='Newbie' />Newbie";
    $output .= "<option selected value='Low' />Low";
    $output .= "<option value='Medium' />Medium";
    $output .= "<option value='High' />High";
    $output .= "<option value='Extreme' />Extreme";
  } elseif ( $complex == "Medium" ) {
    $output .= "<option value='' />";
    $output .= "<option value='Newbie' />Newbie";
    $output .= "<option value='Low' />Low";
    $output .= "<option selected value='Medium' />Medium";
    $output .= "<option value='High' />High";
    $output .= "<option value='Extreme' />Extreme";
  } elseif ( $complex == "High" ) {
    $output .= "<option value='' />";
    $output .= "<option value='Newbie' />Newbie";
    $output .= "<option value='Low' />Low";
    $output .= "<option value='Medium' />Medium";
    $output .= "<option selected value='High' />High";
    $output .= "<option value='Extreme' />Extreme";
  } elseif ( $complex == "Extreme" ) {
    $output .= "<option value='' />";
    $output .= "<option value='Newbie' />Newbie";
    $output .= "<option value='Low' />Low";
    $output .= "<option value='Medium' />Medium";
    $output .= "<option value='High' />High";
    $output .= "<option selected value='Extreme' />Extreme";
  } else {
    $output .= "<option selected value='' />";
    $output .= "<option value='Newbie' />Newbie";
    $output .= "<option value='Low' />Low";
    $output .= "<option value='Medium' />Medium";
    $output .= "<option value='High' />High";
    $output .= "<option value='Extreme' />Extreme";
  }
  $output .= "</select>";

  return $output;
}

function show_complex($complex) {
  if ( $complex == "" ) {
    return "";
  } else {
    return "<img src='/images/${complex}_large.png' />";
  }
}
