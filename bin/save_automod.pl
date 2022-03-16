#!/usr/bin/perl

use DBI;
use WWW::Mechanize;

$bgg_user = "Cassandra Project";
$bgg_pswd = $ENV{'BGG_PASSWORD'};

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

# This code will be used to run all the codes needed for the AutoMod AI.
# It will be run in the cron every minute but not on the weekends.

# Log into Cassy Web pages
$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://cassandrawerewolf.com/index.php?login=true');
$mech->form_name('login_cassy');
$mech->set_fields('uname' => $bgg_user);
$mech->set_fields('pwd' => $bgg_pswd);
$mech->set_fields('remember' => 'on');
$mech->click_button(name => 'login');

# Find out which games are Auto-Mod games

$sth_game = $dbh->prepare("select * from Games where automod_id is not null and status != 'Finished'");
$sth_game->execute();

#Process each non-Finished auto-mod game.
while ( $game = $sth_game->fetchrow_hashref() ) {
  # Get time information
$sth_time = $dbh->prepare("select date_format(now(),'%H:%i:00') as now, date_format(now(),'%H:00:00') as hour, time_format(timediff(now(),start_date),'%H') as since_start from Games where Games.id=?");
$sth_time->execute($game->{'id'});
$time = $sth_time->fetchrow_hashref();
  # Find out which Status the game is in Sign-up or In Progress
  if ( $game->{'status'} eq "Sign-up" ) {
    $sth_template = $dbh->prepare("select * from AM_template where id=?");
	$sth_template->execute($game->{'automod_id'});
	$template = $sth_template->fetchrow_hashref();
    # Check to see if game is full and needs to be moved to In Progress.
	$sth2 = $dbh->prepare("select count(*) from Players where game_id=?");
	$sth2->execute($game->{'id'});
	$num_players = $sth2->fetchrow_array();
	# Check to see if the game is full.  If it is run all the start up stuff.
	if ( $num_players == $game->{'max_players'} ) {
	  # Randomly Assign Roles
	  $sth_players = $dbh->prepare("select user_id from Players where game_id=? order by rand()");
	  $sth_players->execute($game->{'id'});
	  $sth_roles = $dbh->prepare("select role_id, side from AM_roles where template_id = $game->{'automod_id'} order by rand()");
	  $sth_roles->execute();
      $sth_assign = $dbh->prepare("update Players set role_id = ?, side = ? where user_id = ? and game_id = ?");
	  while ( $player = $sth_players->fetchrow_array() ) {
        ($role, $side) = $sth_roles->fetchrow_array();
	    $sth_assign->execute($role,$side,$player,$game->{'id'});
      }
	  # Set game to "In Progress"
   	  $sth_go = $dbh->prepare("update Games set status='In Progress' where id=?");
	  $sth_go->execute($game->{'id'});
	  # Post Auto Vote Tally Rules
	  open (FILE,"< ../werewolf/cassy_vote_tally.txt") or die "Couldn't open file";
	  $message = "";
	  while ( $line = <FILE> ) {
        $message .= $line;
	  }
	  close FILE;
	  $message .= "\n";
	  if ( $game->{'auto_vt'} eq "lhv" ) {
        $message .= "Your Moderator has chosen to use the Longest Held Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n\n";
	  } else {
        $message .= "Your Moderator has chosen to use the Longest Held Last Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n\n";
	  }
	  $thread_id = $game->{'thread_id'};
	  $message .= "Vote Log Page: http://cassandrawerewolf.com/game/".$thread_id."/votes\n";
	  $message .= "Vote Tally Page: http://cassandrawerewolf.com/game/".$thread_id."/tally\n";
	  system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	  # Activate Game Communications System
	  $mech->get("http://cassandrawerewolf.com/configure_chat.php?game_id=$game->{'id'}");
	  $mech->form_name('add_chat_form');
	  $mech->click_button(name => 'submit_all');
	  $sth_wolves = $dbh->prepare("select user_id from Players where role_id='02' and game_id= ?");
      $sth_wolves->execute($game->{'id'});
	  if ( $sth_wolves->rows() > 1 ) {
	    $room_name = ' Wolf Chat';
	    $mech->get("http://cassandrawerewolf.com/configure_chat.php?game_id=$game->{'id'}");
	    $mech->form_name('add_chat_form');
	    $mech->set_fields('chat_name' => $room_name);
	    while ( $user_id = $sth_wolves->fetchrow_array() ) {
	      if ( $user_id < 100 ) { $user_id = substr($user_id,1); }
	      if ( $user_id < 10 ) { $user_id = substr($user_id,2); }
	      $mech->set_fields('player_'.$user_id => 'on');
	    }
	    $mech->click_button(name => 'submit_newchat');
	  }
	  # Give out N0 Views and Activate Game Order System
	    # Seer
		$sth_who = $dbh->prepare("select user_id from Players where game_id=? and role_id=03");
		$sth_who->execute($game->{'id'});
		$who_id = $sth_who->fetchrow_array();
		$sth_orders = $dbh->prepare("update Players set game_action='alive', ga_desc='View' where user_id=? and game_id=?");
		$sth_orders->execute($who_id,$game->{'id'});
		$sth_view = $dbh->prepare("select name from Players, Users where Players.user_id=Users.id and game_id=? and role_id != 03 and role_id != 02 order by rand() limit 0, 1");
		$sth_view->execute($game->{'id'});
		$view = $sth_view->fetchrow_array();
		$sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=? and name like 'Mod%'");
		$sth_room->execute($game->{'id'},$who_id);
		$room_id = $sth_room->fetchrow_array();
		$sth_monitor = $dbh->prepare("update Chat_rooms set monitor=1 where id=?");
		$sth_monitor->execute($room_id);
		$sth_post_view = $dbh->prepare("insert into Chat_messages (room_id, user_id, message, post_time) values ( ? , '306' , ? ,  now() )");
		$message = "You viewed $view as a Non-Wolf";
		if ( $template->{'random_n0'} eq "yes" ) { $sth_post_view->execute($room_id,$message); }
		$message = "Please use the Order Command Form to your right to input your views.  You can change you mind up until the deadline, and the last submitted name will be the one used.";
		$sth_post_view->execute($room_id,$message);
        #Sorceror
		$sth_who = $dbh->prepare("select user_id from Players where game_id=? and role_id=08");
		$sth_who->execute($game->{'id'});
		$who_id = $sth_who->fetchrow_array();
		$sth_orders = $dbh->prepare("update Players set game_action='alive', ga_desc='View' where user_id=? and game_id=?");
		$sth_orders->execute($who_id,$game->{'id'});
		$sth_view = $dbh->prepare("select name from Players, Users where Players.user_id=Users.id and game_id=? and role_id != 08 and role_id != 02 order by rand() limit 0, 1");
		$sth_view->execute($game->{'id'});
		$view = $sth_view->fetchrow_array();
		$sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=? and name like 'Mod%'");
		$sth_room->execute($game->{'id'},$who_id);
		$room_id = $sth_room->fetchrow_array();
		$sth_monitor->execute($room_id);
		if ($template->{'random_n0'} eq "yes" ) {$message = "You viewed $view as a Non-Seer";}
		$sth_post_view->execute($room_id,$message);
		$message = "Please use the Order Command Form to your right to input your views.  You can change you mind up until the deadline, and the last submitted name will be the one used.";
		$sth_post_view->execute($room_id,$message);
		# Priest Instructions
		$sth_who = $dbh->prepare("select user_id from Players where game_id=? and role_id=07");
		$sth_who->execute($game->{'id'});
		if ( $sth_who->rows() != 0 ) {
		  $who_id = $sth_who->fetchrow_array();
		  $sth_orders = $dbh->prepare("update Players set game_action='dead', ga_desc='View' where user_id=? and game_id=?");
		  $sth_orders->execute($who_id,$game->{'id'});
		  $sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=? and name like 'Mod%'");
		  $sth_room->execute($game->{'id'},$who_id);
		  $room_id = $sth_room->fetchrow_array();
		  $sth_monitor->execute($room_id);
		  $message = "Please use the Order Command Form to your right to input your views.  You can change you mind up until the deadline, and the last submitted name will be the one used.";
		  $sth_post_view->execute($room_id,$message);
		}
		# Wolf Instructions
		$sth_who = $dbh->prepare("select user_id from Players where game_id=? and role_id=02");
		$sth_who->execute($game->{'id'});
		while ( $who_id = $sth_who->fetchrow_array() ) {
		  $sth_orders = $dbh->prepare("update Players set game_action='alive', ga_desc='Kill', ga_group='Wolves' where user_id=? and game_id=?");
		  $sth_orders->execute($who_id,$game->{'id'});
		}
		$room_name = ' Wolf Chat';
		if ( $sth_who->rows() == 1 ) { $room_name = 'Mod%'; }
		$sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=306 and name like ?");
		$sth_room->execute($game->{'id'},$room_name);
		$room_id = $sth_room->fetchrow_array();
		$message = "Please use the Order Command Form to your right to input your views.  You can change you mind up until the deadline, and the last submitted name will be either wolf will be the one used.";
		$sth_post_view->execute($room_id,$message);
	  # Activate Missing Player Warning System
	  $sth_missing = $dbh->prepare("update Games set missing_hr=24 where id=?");
	  $sth_missing->execute($game->{'id'});
	  # GeekMail players that game has started
	  $mech->get("http://cassandrawerewolf.com/pm_players.php");
      $mech->form_name('pm_player');
	  $mech->set_fields('all' => 'on');
	  $mech->click_button(name => 'submit');
	  $mech->form_name('send_message');
	  $mech->set_fields('bggpwd' => $bgg_pswd);
	  $mech->set_fields('subject' => 'Auto-Mod Game Starting');
	  $mech->set_fields('message' => "Auto-Mod Game http://www.boardgamegeek.com/thread/$thread_id is starting.  Please view the Cassy page to see your role and the Cassy chat to learn any N0 information your role may need. http://cassandrawerewolf.com/game/$thread_id");
	  $mech->click_button(name => 'submit');
	  # Post Dawn if the N0 views are random.
	  if ( $template->{'random_n0'} eq "yes" ) {
	    $message = "[b][Dawn][/b]\nThe first dusk will be at ".$game->{'lynch_time'}." provided it is at least 12hrs away.  Other wise it will be tomorrow at that time.";
	    system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	  } else {
        $message = "It is Night 0.  If you have N0 orders please do that now.  Dawn will be posted at ".$game->{'na_deadline'}." provided it is at least 4hrs away. Otherwise it will be tomorrow at that time.";
	    system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	  }
	}
  } else {
    # Game is In-Progress so check for needed actions.
	$thread_id = $game->{'thread_id'};
	if ( $game->{'day'} eq '0' and $time->{'since_start'} < 4 ) {
    # Check to see if needed N0 actions are in yet.
	$sth_check = $dbh->prepare("select (select count(*) from Players, AM_roles, Games where Players.role_id=AM_roles.role_id and Players.game_id=Games.id and AM_roles.template_id=Games.automod_id and n0_view='yes' and game_id=?) as need, (select count(*) from Game_orders, Players, AM_roles, Games where Game_orders.user_id=Players.user_id and Game_orders.game_id=Players.game_id and Players.role_id=AM_roles.role_id and AM_roles.template_id=Games.automod_id and Game_orders.game_id=Games.id and Players.game_id=Games.id and AM_roles.n0_view='yes' and Game_orders.game_id=?  and Game_orders.day=?) as want");
	$sth_check->execute($game->{'id'},$game->{'id'},$game->{'day'});
	$check = $sth_check->fetchrow_hashref();
	 if ( $check->{'need'} ne $check->{'want'} ) {

	  }
	  }
    #Check to see if Dawn should be posted.
    if ( $game->{'phase'} eq 'night' && $game->{'na_deadline'} eq $time->{'now'} ) {
	  # Give out Night Action Results
	  # Seer
	  $sth_who = $dbh->prepare("select user_id from Players where game_id=? and role_id=03");
	  $sth_who->execute($game->{'id'});
	  $who_id = $sth_who->fetchrow_array();
	  $sth_order = $dbh->prepare("select name, role_id from Game_orders, Users, Players, Players_all where Game_orders.target_id=Users.id and Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Game_orders.target_id=Players_all.user_id and Game_orders.game_id=Players_all.game_id and `desc`='View' and Game_orders.user_id=? and Game_orders.game_id=? and day=? order by last_updated desc limit 0,1");
      $sth_order->execute($who_id,$game->{'id'},$game->{'day'});
      $target = $sth_order->fetchrow_hashref();
	  $message = $target->{'name'};
	  if ( $target->{'role_id'} == 02 ) {
        $message .= " is a wolf";
	  } else {
	    $message .= " is not a wolf";
	  }
	  $sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=? and name like 'Mod%'");
	  $sth_room->execute($game->{'id'},$who_id);
	  $room_id = $sth_room->fetchrow_array();
	  $sth_post_view = $dbh->prepare("insert into Chat_messages (room_id, user_id, message, post_time) values ( ? , '306' , ? ,  now() )");
	  $sth_post_view->execute($room_id,$message);
	  # Sorceror
	  $sth_who = $dbh->prepare("select user_id from Players where game_id=? and role_id=08");
	  $sth_who->execute($game->{'id'});
	  $who_id = $sth_who->fetchrow_array();
	  $sth_order = $dbh->prepare("select name, role_id from Game_orders, Users, Players, Players_all where Game_orders.target_id=Users.id and Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Game_orders.target_id=Players_all.user_id and Game_orders.game_id=Players_all.game_id and `desc`='View' and Game_orders.user_id=? and Game_orders.game_id=? and day=? order by last_updated desc limit 0,1");
      $sth_order->execute($who_id,$game->{'id'},$game->{'day'});
      $target = $sth_order->fetchrow_hashref();
	  $message = $target->{'name'};
	  if ( $target->{'role_id'} == 03 ) {
        $message .= " is the seer";
	  } else {
	    $message .= " is not the seer";
	  }
	  $sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=? and name like 'Mod%'");
	  $sth_room->execute($game->{'id'},$who_id);
	  $room_id = $sth_room->fetchrow_array();
	  $sth_post_view = $dbh->prepare("insert into Chat_messages (room_id, user_id, message, post_time) values ( ? , '306' , ? ,  now() )");
	  $sth_post_view->execute($room_id,$message);
	  # Priest
	  $sth_who = $dbh->prepare("select user_id from Players where game_id=? and role_id=07");
	  $sth_who->execute($game->{'id'});
	  $who_id = $sth_who->fetchrow_array();
	  $sth_order = $dbh->prepare("select name, Roles.`type` from Game_orders, Users, Players, Players_all, Roles where Game_orders.target_id=Users.id and Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Game_orders.target_id=Players_all.user_id and Game_orders.game_id=Players_all.game_id and Roles.id=Players.role_id and `desc`='View' and Game_orders.user_id=? and Game_orders.game_id=? and day=? order by last_updated desc limit 0,1");
      $sth_order->execute($who_id,$game->{'id'},$game->{'day'});
      $target = $sth_order->fetchrow_hashref();
	  $message = $target->{'name'}." was a ".$target->{'type'};
	  $sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=? and name like 'Mod%'");
	  $sth_room->execute($game->{'id'},$who_id);
	  $room_id = $sth_room->fetchrow_array();
	  $sth_post_view = $dbh->prepare("insert into Chat_messages (room_id, user_id, message, post_time) values ( ? , '306' , ? ,  now() )");
	  $sth_post_view->execute($room_id,$message);
	  # Post Dawn
	  $message = "[b][Dawn][/b]\n";
	  $sth_order = $dbh->prepare("select name from Game_orders, Users, Players, Players_all where Game_orders.target_id=Users.id and Players.game_id = Players_all.game_id and Players.user_id=Players_all.original_id and Game_orders.user_id=Players_all.user_id and Game_orders.game_id = Players_all.game_id and ga_group='Wolves' and `desc`='Kill' and Game_orders.game_id=? and day=? order by last_updated desc limit 0,1");
      $sth_order->execute($game->{'id'},$game->{'day'});
      $target = $sth_order->fetchrow_hashref();
	  $message .= "[b][Killed ".$target->{'name'}."][/b]";
	  system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	  $sth_collect = $dbh->prepare("update Post_collect_slots set last_dumped=Null where game_id=?");
	  $sth_collect->execute($game->{'id'});
	}
	# Check to see if Dusk should be posted.
	if ( $game->{'phase'} eq 'day' && $game->{'lynch_time'} eq $time->{'now'} && $time->{'since_start'} >= 12 ) {
	  $message = "[b][Dusk][/b]";
	  system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	  $mech->get('http://cassandrawerewolf.com/vote_tally.php?action=retrieve&game_id='.$game->{'id'});
	  $sth_lynch = $dbh->prepare("update Games set automod_state='lynch' where id=?");
	  $sth_lynch->execute($game->{'id'});
	}
	# Check to see if Vote Tally is Up to date.
	if ( $game->{'phase'} eq 'night' && $game->{'automod_state'} eq "lynch" ) {
	  $table = "Tally_display_".$game->{'auto_vt'};
      $sth_vote = $dbh->prepare("select votee from $table where game_id=? and day=?  and  total > 0 limit 0,1");
	  $sth_vote->execute($game->{'id'},$game->{'day'});
	  $votee = $sth_vote->fetchrow_array();
	  $message = "[b][killed $votee][/b]";
	  system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	  $sth_lynch = $dbh->prepare("update Games set automod_state=NULL where id=?");
	  $sth_lynch->execute($game->{'id'});
	  $sth_collect = $dbh->prepare("update Post_collect_slots set last_dumped=Null where game_id=?");
	  $sth_collect->execute($game->{'id'});

	}
	# Check to see if the game is finished.
	# Count Wolves
	$sth_wolves = $dbh->prepare("select count(*) from Players where role_id=02 and death_day is null and game_id=? group by game_id");
	$sth_wolves->execute($game->{'id'});
	$num_wolves = $sth_wolves->fetchrow_array();
	if ( $num_wolves == 0 ) {
      # Good wins.  They have killed all the wolves.
	  $message = "Game Over.\nVillage has killed all the wolves.";
	  system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	  $sth_end = $dbh->prepare("update Games set winner='Good', status='Finished' where id=?");
	  $sth_end->execute($game->{'id'});
	} else {
	  # Count Non-Wolves
	  $sth_nonw = $dbh->prepare("select count(*) from Players where role_id != 02 and death_day is null and game_id=? group by game_id");
	  $sth_nonw->execute($game->{'id'});
	  $num_nonw = $sth_nonw->fetchrow_array();
	  if ( $num_nonw == $num_wolves ) {
        # Wolves have reached Parity.  Is the Hunter blocking their victory?
		if ( $num_wolves == 1 ) {
           $sth_hunter = $dbh->prepare("select count(*) from Players where role_id=05 and death_day is null and game_id=? group by game_id");
		   $sth_hunter->execute($game->{'id'});
		   $num_hunter = $sth_hunter->fetchrow_array();
		   if ( $num_hunter == 1 ) {
             # Hunter wins for the village.
			 $message ="Game Over.\nHunter kills the final wolf.";
	         system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	         $sth_end = $dbh->prepare("update Games set winner='Good', status='Finished' where id=?");
	         $sth_end->execute($game->{'id'});
		   } else {
             # Hunter is not alive, so Wolves win.
			 $message = "Game Over.\nWolves have reached parity.";
	         system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	         $sth_end = $dbh->prepare("update Games set winner='Evil', status='Finished' where id=?");
	         $sth_end->execute($game->{'id'});
		   }
		} else {
        # Wolves Won, they have reached parity.
		$message = "Game Over.\nWolves have reached parity.";
	    system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $thread_id \"$message\"");
	    $sth_end = $dbh->prepare("update Games set winner='Evil', status='Finished' where id=?");
	    $sth_end->execute($game->{'id'});
		}
	  }
	}
  } # If for game status
} # Loop for each Auto-mod game.
