<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );

include_once "edit_game_functions.php";
include_once "php/bgg.php";
include_once "php/common.php";

$cache = init_cache();

if ( ! isset($_REQUEST['q']) ) {
clear_editSpace();
exit;
}

$game_id = $_REQUEST['game_id'];

switch ( $_REQUEST['q'] ) {

# Replace text with form to edit_moderators.
case 'e_moderator':
  print "Select moderators.  Use Control to select more than one.<br /><br />";
  edit_moderator($game_id);
  break;

# Edit database with new Moderator list and return text to original.
case 's_moderator':
  $newidlist = split( ",", $_REQUEST['modlist']);
  sort($newidlist);
  $sql = sprintf("select user_id from Games, Moderators where Games.id = Moderators.game_id and Games.id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $oldidlist[] = $row['user_id'];
  }
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
	$cache->remove('games-signup-fast-list', 'front');
	$cache->remove('games-signup-swf-list', 'front');
	$cache->remove('games-signup-list', 'front');

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
      $result = mysqli_query($mysql, $sql);
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
      $result = mysqli_query($mysql, $sql);
    }
  }

  show_moderator($game_id);
  break;

# Replace text with form to edit_dates.
  case 'e_date':
    print "Edit the start and end dates.<br /><br />";
    edit_dates($game_id);
  break;

# Edit database with new Dates and return text to original.
  case 's_date':
    if ( isset($_REQUEST['speed']) ) {

    } else {
      $start = $_REQUEST['sdate']." ".$_REQUEST['stime'];
      $sql = sprintf("update Games set start_date=%s, end_date=%s, swf=%s where id=%s",quote_smart($start),quote_smart($_REQUEST['edate']),quote_smart($_REQUEST['swf']),quote_smart($game_id));
	  $result = mysqli_query($mysql, $sql);
    }
	$cache->remove('games-signup-fast-list', 'front');
	$cache->remove('games-signup-swf-list', 'front');
	$cache->remove('games-signup-list', 'front');
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
    show_dates($game_id);
  break;

# Repace text with from to edit description.
  case 'e_description':
    print "Edit the game description.  To format your text you must use html.<br /><br />";
    edit_description($game_id);
  break;

# Edit database with new Description return text to original.
  case 's_description':
    $_REQUEST['desc'] = safe_html($_REQUEST['desc'],"<a>");
    $sql = sprintf("update Games set description=%s where id=%s",quote_smart($_REQUEST['desc']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	print "<div onMouseOver='show_hint(\"Click to change Description\")' onMouseOut='hide_hint()' onclick='edit_desc()' >".stripslashes($_REQUEST['desc'])."</div>";
  break;

# Replace text with form to change Status.
  case 'e_status':
    print "Change the status of the game.  In-Progress means that the players can only see their own roles, and that nobody can see any of the comments below.  Once you set the game to 'Finished' then everyone will be able to see everything.  When you set a game to 'Finished' please don't forget to set the winner.  If you are using the Automatied vote tally system there should be no need to manually change the period or number.<br /><br />";
    edit_status($game_id);
  break;

# Edit database with new Status return text to original.
  case 's_status':
    $sql = sprintf("update Games set `status`=%s, phase=%s, day=%s where id=%s",quote_smart($_REQUEST['status']),quote_smart($_REQUEST['phase']),quote_smart($_REQUEST['day']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	if($_REQUEST['status'] == 'In Progress') {
		$cache->remove('total-games', 'front');
		$cache->remove('games-in-progress-fast-list', 'front');
		$cache->remove('games-in-progress-list', 'front');
		$cache->remove('current-games', 'front');
		$cache->remove('games-signup-fast-list', 'front');
		$cache->remove('games-signup-swf-list', 'front');
		$cache->remove('games-signup-list', 'front');
		$cache->clean('front-signup-' . $game_id);
		$cache->clean('front-signup-swf-' . $game_id);
		$cache->clean('front-signup-fast-' . $game_id);
	} elseif($_REQUEST['status'] == 'Finished') {
		$cache->remove('current-games', 'front');
		$cache->remove('games-in-progress-fast-list', 'front');
		$cache->remove('games-in-progress-list', 'front');
		$cache->remove('games-ended-list', 'front');
        $sql = sprintf("delete from Physics_processing where game_id=%s",quote_smart($game_id));
        mysqli_query($mysql, $sql);
	}

	print "<div onMouseOver='show_hint(\"Click to Change Status\")' onMouseOut='hide_hint()' onClick='edit_status()'>".$_REQUEST['status']." - ".$_REQUEST['phase']." ".$_REQUEST['day']."</div>";
  break;

# Replace text with form to change speed.
  case 'e_speed':
    print "Change the speed of the game.<br /><br />";
    edit_speed($game_id);
  break;

# Edit database with new Speed, return text to original.
  case 's_speed':
    $sql = sprintf("update Games set deadline_speed=%s where id=%s",quote_smart($_REQUEST['speed']),quote_smart($game_id));
    $result = mysqli_query($mysql, $sql);
    print "<div onMouseOver='show_hint(\"Click to Change Speed\")' onMouseOut='hide_hint()' onClick='edit_speed()'>".$_REQUEST['speed']."</div>";
	$cache->remove('games-in-progress-fast-list', 'front');
	$cache->remove('games-in-progress-list', 'front');
	$cache->remove('games-signup-fast-list', 'front');
	$cache->remove('games-signup-swf-list', 'front');
	$cache->remove('games-signup-list', 'front');
  break;

# Replace text with form to change deadlines.
  case 'e_deadline':
    print "Change the deadlines of the game.<br /><br />";
    edit_deadline($game_id);
  break;

# Edit database with new Deadlines return text to original.
  case 's_deadline':
	print "<td id='deadline_td'><div onMouseOver='show_hint(\"Click to Change Deadlines\")' onMouseOut='hide_hint()' onClick='edit_deadline()'>";
    if ( isset($_REQUEST['speed']) ) {
    # A change in the speed is requireing this to be refreshed
      $sql = sprintf("select lynch_time, na_deadline, day_length, night_length from Games where id=%s",quote_smart($game_id));
      $result = mysqli_query($mysql, $sql);
      $deadlines = mysqli_fetch_array($result);
      list($lynch,$lmin,$x) = split(":",$deadlines['lynch_time']);
      list($night,$nmin,$x) = split(":",$deadlines['na_deadline']);
      list($day_length,$dlmin,$x) = split(":",$deadlines['day_length']);
      list($night_length,$nlmin,$x) = split(":",$deadlines['night_length']);
      if ( $_REQUEST['speed'] == "Standard" ) {
        if ( $lynch != "" ) {
          print "Dusk: ".time_24($lynch,$lmin)." BGG<br />";
        }
        if ( $night != "" ) {
          print "Dawn: ".time_24($night,$nmin)." BGG";
        }
      } else {
        print "Day Length: $day_length:$dlmin <br />\n";
        print "Night Length: $night_length:$nlmin <br />\n";
      }
    } else {
      if ( $_REQUEST['lynch'] != "" ) {
        $lynch = $_REQUEST['lynch'];
        $sql = sprintf("update Games set `lynch_time`=%s where id=%s",quote_smart($lynch),quote_smart($game_id));
	    $result = mysqli_query($mysql, $sql);
	  } else {
	    $sql = sprintf("update Games set `lynch_time`=null where id=%s",quote_smart($game_id));
	    $result = mysqli_query($mysql, $sql);
      }
  	  if ( $_REQUEST['night'] != "" ) {
        $night = $_REQUEST['night'];
        $sql = sprintf("update Games set `na_deadline`=%s where id=%s",quote_smart($night),quote_smart($game_id));
	    $result = mysqli_query($mysql, $sql);
	  } else {
	    $sql = sprintf("update Games set `na_deadline`=null where id=%s",quote_smart($game_id));
	    $result = mysqli_query($mysql, $sql);
	  }
      $sql = sprintf("update Games set `day_length`=%s where id=%s",quote_smart($_REQUEST['day_length']),quote_smart($game_id));
      $result = mysqli_query($mysql, $sql);
      $sql = sprintf("update Games set `night_length`=%s where id=%s",quote_smart($_REQUEST['night_length']),quote_smart($game_id));
      $result = mysqli_query($mysql, $sql);
      $sql = sprintf("select deadline_speed from Games where id=%s",quote_smart($game_id));
      $result = mysqli_query($mysql, $sql);
      $speed = mysqli_result($result,0,0);
      if ( $speed == "Standard" ) { 
         list($lynch,$lmin,$x) = split(":",$_REQUEST['lynch']);
         list($night,$nmin,$x) = split(":",$_REQUEST['night']);    
         print "Dusk: ".time_24($lynch,$lmin)." BGG<br />";
         print "Dawn: ".time_24($night,$nmin)." BGG";
      } else {
         print "Day Length: ".$_REQUEST['day_length']."<br />\n";
         print "Night Length: ".$_REQUEST['night_length']."<br />\n";
      }
    }
	print "</div>\n";
	$cache->remove('games-in-progress-fast-list', 'front');
	$cache->remove('games-in-progress-list', 'front');
	$cache->remove('games-signup-fast-list', 'front');
	$cache->remove('games-signup-swf-list', 'front');
	$cache->remove('games-signup-list', 'front');
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
  break;

# Replace text with form to change Winner
  case 'e_winner':
    print "Change the winner of the game.  If an evil team one select evil.  If the good team won, select good,  If the game was neither good vs evil or had an individual winner then choose 'other'.<br /><br />";
    edit_winner($game_id);
  break;

# Edit database with new Winner return text to original.
  case 's_winner':
    $sql = sprintf("update Games set winner=%s where id=%s",quote_smart($_REQUEST['winner']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$cache->remove('evil-games', 'front');
	$cache->remove('good-games', 'front');
	$cache->remove('other-games', 'front');

	print "<div onMouseOver='show_hint(\"Click to Change Winner\")' onMouseOut='hide_hint()' onClick='edit_winner()'>".$_REQUEST['winner']."</div>";
  break;

# Replace text with form to add or delete sub-threads.
  case 'e_subthread':
    print "If your game has sub-threads associated with it, such as threads where team-member can discussthings.  Then you add them here.  Once you have added the BGG thead_id you can edit that 'game' page just as you are editing this one.<br /><br />";
    edit_subt($game_id);
  break;

# Delete a Sub-Thread
  case 'd_subthread':
    $sql = sprintf("select id from Games where thread_id=%s",quote_smart($_REQUEST['thread_id']));
	$result = mysqli_query($mysql, $sql);
	$st_game_id = mysqli_result($result,0,0);
	$sql = "delete from Games where id ='$st_game_id'";
	$result = mysqli_query($mysql, $sql);
	# The following is no longer needed because of sql triggers
	#$sql = "delete from Moderators where game_id='$st_game_id'";
	#$result = mysqli_query($mysql, $sql);
	#$sql = "delete from Players where game_id='$st_game_id'";
	#$result = mysqli_query($mysql, $sql);
	#$sql = "delete from Replacements where game_id='$st_game_id'";
	#$result = mysqli_query($mysql, $sql);
	#$sql = "delete from Posts where game_id='$st_game_id'";
	#$result = mysqli_query($mysql, $sql);
    show_subt($game_id);
  break;

# Add a Sub-Thread
  case 'a_subthread':
    $sql = sprintf("insert into Games (id, title, status, thread_id, parent_game_id) values ( NULL, 'Sub-Thread', 'Sub-Thread', %s, %s)",quote_smart($_REQUEST['thread_id']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$new_game_id = mysqli_insert_id();
	$sql = sprintf("select user_id from Moderators where game_id=%s",quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	while ( $mod = mysqli_fetch_array($result) ) {
      $sql2 = "insert into Moderators (user_id, game_id) values ('".$mod['user_id']."', '$new_game_id')";
	  $result2 = mysqli_query($mysql, $sql2);
	}
	show_subt($game_id);
  break;

# Replace Title with form to change title
  case 'e_name':
    print "You can change the name of the game or sub-thread.<br /><br />";
    edit_name($game_id);
  break;

# Change name and replace text with new name
  case 's_name':
    $_REQUEST['title'] = safe_html($_REQUEST['title']);
    $sql = sprintf("update Games set title=%s where id=%s",quote_smart($_REQUEST['title']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
	$cache->remove('game-' . $game_id, 'front');

	$sql = "select number, title from Games where id='$game_id'";
	$result = mysqli_query($mysql, $sql);
	$game = mysqli_fetch_array($result);
	$output = "";
	if ( $game['number'] != "" ) { $output .= $game['number'].") "; }
	$output .= $_REQUEST['title'];

	print $output;
  break;

# Replace Thread ID with form to change thread id
  case 'e_thread':
    print "You can change the BGG thread_id.  This should only be done when changing a game from a sign-up thread to a game thread.<br /><br />";
    edit_thread($game_id);
  break;

# Change thread_id and replace text with new id
  case 's_thread':
    $sql = sprintf("update Games set thread_id=%s where id=%s",quote_smart($_REQUEST['thread_id']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
	$cache->remove('game-' . $game_id, 'front');
	$output = "<div onMouseOver='show_hint(\"Click to change BGG Thread id\")' onMouseOut='hide_hint()' onClick='edit_thread()'>".$_REQUEST['thread_id']."</div>";

	print $output;
  break;

# Show Player edit dialog.
  case 'e_player':
    print "You can edit or delete a player.  If the player is being replaced after the game has started, please use the replace function instead of deleting.<br /><br />";
    edit_player($_REQUEST['uid'],$_REQUEST['row'],$game_id);
  break;

# Delete a replacement.
  case 'd_replace':
    $sql = sprintf("delete from Replacements where user_id=%s and replace_id=%s and game_id=%s",quote_smart($_REQUEST['user_id']),quote_smart($_REQUEST['replace_id']),quote_smart($game_id));
	$result=mysqli_query($mysql, $sql);
	$sql = sprintf("select name from Users where id=%s",quote_smart($_REQUEST['user_id']));
	$result=mysqli_query($mysql, $sql);
	$name = mysqli_result($result,0,0);
	print display_player($name,$_REQUEST['user_id'],$game_id);
  break;

# Change Players details.
  case 's_player':
    $user_id = $_REQUEST['uid'];
	if ( $_REQUEST['rep_id'] != "0" ) {
      $sql = sprintf("insert into Replacements (user_id, game_id, replace_id, period, number) values ( %s, %s, %s, %s, %s )",quote_smart($user_id),quote_smart($game_id), quote_smart($_REQUEST['rep_id']), quote_smart($_REQUEST['rep_p']), quote_smart($_REQUEST['rep_n']));
	  $result = mysqli_query($mysql, $sql);
	}
	if ( $_REQUEST['d_day'] == "" ) {
	  $sql = sprintf("update Players set role_name=%s, role_id=%s, side=%s, death_phase=%s, death_day=NULL, mod_comment=%s, player_alias=%s, alias_color=%s where user_id=%s and game_id=%s",quote_smart($_REQUEST['r_name']), quote_smart($_REQUEST['r_id']), quote_smart($_REQUEST['side']), quote_smart($_REQUEST['d_phase']), quote_smart($_REQUEST['comment']), quote_smart($_REQUEST['player_alias']), quote_smart($_REQUEST['alias_color']), quote_smart($user_id), quote_smart($game_id));
	} else {
	  $sql = sprintf("update Players set role_name=%s, role_id=%s, side=%s, death_phase=%s, death_day=%s, mod_comment=%s, player_alias=%s, alias_color=%s where user_id=%s and game_id=%s",quote_smart($_REQUEST['r_name']), quote_smart($_REQUEST['r_id']), quote_smart($_REQUEST['side']), quote_smart($_REQUEST['d_phase']), quote_smart($_REQUEST['d_day']), quote_smart($_REQUEST['comment']), quote_smart($_REQUEST['player_alias']), quote_smart($_REQUEST['alias_color']), quote_smart($user_id), quote_smart($game_id));
	}
	$result = mysqli_query($mysql, $sql);
	$sql = sprintf("update Players set need_replace=null where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($user_id));
	$result = mysqli_query($mysql, $sql);
	createPlayer_table(true,$game_id);
  break;
  
# Show Add Player dialog.
  case 'a_player':
    print "You can add a player to your game.  If this player is a replacement player, please edit the player he is replacing rather than adding him/her here.<br /><b>Important:</b>If the player is listed in the autocomplete drop down, please select their name from that list, rather than just typing it in.  Only type in names that are not in the drop-down list, these should be newbies.  And make sure you spell the name correctly.";
    add_player($game_id);
  break;

# Add New Player.
  case 'an_player':
    if ( $_REQUEST['s'] == "new" ) {
     // Check to see if the username is a banned palyer
     $sql = sprintf("select level from Users where name=%s",quote_smart($_REQUEST['user_id']));
     $result = mysqli_query($mysql, $sql);
	if(mysqli_num_rows($result)==0) {
		$level = 3;
	}
	else {
    	$level = mysqli_result($result,0,0);
	}

    if ( $level == '0' ) {
    	print "<span style='color:red;'>The player you just tried to add has been banned from Cassandra.  </span><br />\n";
		createPlayer_table(true,$game_id);
		break;
   } 
	 // Check to see if the username is a valid BGG username
	 print "<!--\n";
	 $bgg_result = (new BGG)->is_bgg_user($_REQUEST['user_id']);
	 print "-->\n";
	 if ( $bgg_result == "true" ) {
       $sql = sprintf("insert into Users (id, name) values ( NULL, %s ) ",quote_smart($_REQUEST['user_id']));
	   $result = mysqli_query($mysql, $sql);
	   $id = mysqli_insert_id();
	 } else {
	  print "<span style='color:red;'>The player you just tried to add is not a valid BGG user.  You can only add BGG users.</span><br />\n";
	  createPlayer_table(true,$game_id);
	  break;
	 }
	   if ( $id == 0 ) {
         $sql = sprintf("select id from Users where name=%s",quote_smart($_REQUEST['user_id']));
	   	 $result = mysqli_query($mysql, $sql);
		 $id = mysqli_result($result,0,0);
	   }
	  $_REQUEST['user_id'] = $id;
	}
    $sql = sprintf("insert into Players (user_id, game_id) values (%s, %s)",quote_smart($_REQUEST['user_id']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
	print "<!--";
    edit_playerlist_post($game_id);
	print "-->\n";
	createPlayer_table(true,$game_id);
  break;

# Delete a Player
  case 'd_player':
    $sql = sprintf("delete from Players where user_id=%s and game_id=%s",quote_smart($_REQUEST['user_id']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
	edit_playerlist_post($game_id);
	createPlayer_table(true,$game_id);
  break;

# Show Alias Name Edit Dialog
  case 'e_alias':
    print "Enter the alias to be used in voting for each of the players.  Do not use the same alias twice.<br /><br />";
    edit_alias($game_id);
  break;

# Change Aliases for all players.
  case 's_alias':
    $sql = sprintf("select Users.id from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$aliases = split(",", $_REQUEST['aliases']);
	$colors = split(",", $_REQUEST['colors']);
	$i = 0;
	while ( $user = mysqli_fetch_array($result) ) {
      $sql2 = sprintf("update Players set player_alias=%s, alias_color=%s where user_id=%s and game_id=%s",quote_smart($aliases[$i]),quote_smart($colors[$i]),quote_smart($user['id']), quote_smart($game_id));
	  $result2 = mysqli_query($mysql, $sql2);
	  $i++;
	}
	createPlayer_table(true,$game_id);
  break;

# Show Role Name Edit Dialog.
  case 'e_rolename':
    print "Enter the names of each of the players roles.  Please do not use comma's in the name.<br /><br />";
    edit_rolename($game_id);
  break;

# Change Role Names for all players.
  case 's_rolename':
    $sql = sprintf("select Users.id from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$rnames = split(",", $_REQUEST['rnames']);
	$i = 0;
	while ( $user = mysqli_fetch_array($result) ) {
      $sql2 = sprintf("update Players set role_name=%s where user_id=%s and game_id=%s",quote_smart($rnames[$i]),quote_smart($user['id']), quote_smart($game_id));
	  $result2 = mysqli_query($mysql, $sql2);
	  $i++;
	}
	createPlayer_table(true,$game_id);
  break;

# Show Role Type Edit Dialog.
  case 'e_roletype':
    print "Select the role type of each of the players.<br /><br />";
    edit_roletype($game_id);
  break;

# Change the Role Type for each player.
  case 's_roletype':
    $sql = sprintf("select Users.id from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$rtypes = split(",", $_REQUEST['rtypes']);
	$i = 0;
	while ( $user = mysqli_fetch_array($result) ) {
	  $sql2 = sprintf("update Players set role_id=%s where user_id=%s and game_id=%s",quote_smart($rtypes[$i]), $user['id'], quote_smart($game_id));
	  $result2 = mysqli_query($mysql, $sql2);
	  $i++;
    }
	createPlayer_table(true,$game_id);
  break;

# Show Team Edit Dialog.
  case 'e_team':
    print "Select the team of each of the players.<br /><br />";
    edit_team($game_id);
  break;

# Change the Teams for each player
  case 's_team':
    $sql = sprintf("select Users.id from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$teams = split(",", $_REQUEST['teams']);
	$i = 0;
    while ( $user = mysqli_fetch_array($result) ) {
      $sql2 = sprintf("update Players set side=%s  where user_id='".$user['id']."' and game_id=%s",quote_smart($teams[$i]), quote_smart($game_id));
 	  $result2 = mysqli_query($mysql, $sql2);
 	  $i++;
	}
	createPlayer_table(true,$game_id);
  break;

# Show Comment Edit Dialog.
  case 'e_comments':
    print "Edit Player comments.  These won't be seen by others until you set the game status to finished.<br /><br />";
    edit_comment($game_id);
  break;

# Show change Max Players Dialoge Box
  case 'e_maxplayers':
    print "Change Max number of players.  If you make this number less than or equal to the number of people currently signed up then no more people can sign up via Cassandra.<br /><br />";
	edit_maxplayers($game_id);
  break;

  case 's_maxplayers':
    $sql = sprintf("update Games set max_players=%s where id=%s",quote_smart($_REQUEST['max_players']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
	print "<div onMouseOver='show_hint(\"Click to Change Max Players\")' onMouseOut='hide_hint()' onClick='edit_maxplayers()'>".$_REQUEST['max_players']."</div>";
  break;

  case 'e_deaths':
    print "Record when each player died in the game.<br />";
	edit_deaths($game_id);
  break;

  case 's_deaths':
    $sql = sprintf("select Users.id from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$phases = split(",", $_REQUEST['phases']);
	$days = split(",", $_REQUEST['days']);
	$i = 0;
    while ( $user = mysqli_fetch_array($result) ) {
	  if ( $days[$i] == "" ) { 
        $sql2 = sprintf("update Players set death_phase=%s, death_day=NULL  where user_id=%s and game_id=%s",quote_smart($phases[$i]), quote_smart($user['id']), quote_smart($game_id));
	  } else {
        $sql2 = sprintf("update Players set death_phase=%s, death_day=%s  where user_id=%s and game_id=%s",quote_smart($phases[$i]), quote_smart($days[$i]), quote_smart($user['id']), quote_smart($game_id));
      }
 	  $result2 = mysqli_query($mysql, $sql2);
 	  $i++;
	}
	createPlayer_table(true,$game_id);
  break;

  case 'e_complex':
    print "Change the complexity of the game.<br />";
	edit_complex($game_id);
  break;

  case 's_complex':
    $sql = sprintf("update Games set complex=%s where id=%s",quote_smart($_REQUEST['complex']),quote_smart($game_id));
	$result = mysqli_query($mysql, $sql);
	$cache->clean('front-signup-' . $game_id);
	$cache->clean('front-signup-swf-' . $game_id);
	$cache->clean('front-signup-fast-' . $game_id);
	print show_complex($_REQUEST['complex']);
  break;
}

?>
