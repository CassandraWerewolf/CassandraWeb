#!/usr/bin/perl 


use DBI;
use WWW::Mechanize;
use Data::Dumper;
use strict;

use vars qw($template $dbh $mech $now $bgg_user $bgg_pswd $weekend);

require '/opt/werewolf/game_function_sub.pl';
require '/opt/werewolf/automod_sub.pl';

# Set Cassy posting variables so that if they need to change they change everywhere.
my $post_script = "/opt/werewolf/post_thread_cookie.pl";
my $cassy_cookie = "/opt/werewolf/cookie.cassandra";

# Find out which games are Auto-Mod games
my $weekend_where = "";
if ( $weekend ) { $weekend_where = "and automod_weekend is not null"; }
my $sth_game = $dbh->prepare("select * from Games where automod_id is not null and status != 'Finished' $weekend_where");
$sth_game->execute();

#Process each non-Finished auto-mod game.
while ( my $game = $sth_game->fetchrow_hashref() ) {
  my $thread_id = $game->{'thread_id'};
  if ( is_running_now($game->{'id'}) ) {
    # Game is being processed by another run.
	next;
  }
  update_running($game->{'id'},"'".$now."'");
  print "Processing ".$game->{'id'}."\n";
  # Get template information
  my $sth_template = $dbh->prepare("select * from AM_template where id=?");
  $sth_template->execute($game->{'automod_id'});
  $template = $sth_template->fetchrow_hashref();
  # Find out which Status the game is in Sign-up or In Progress
  if ( $game->{'status'} eq "Sign-up" ) {
    print "Game in Sign-up mode\n";
	# Check to see if any players need to verify they are still interested in the game
	check_players($game->{'id'},$game->{'title'},$thread_id);
	if ( check_game($game->{'id'}) ) { next; }
    # Check to see if game is full and needs to be moved to In Progress.
	my $num_players = get_num_players($game->{'id'},"update_time > date_sub('$now', interval 1 week) and need_to_confirm is null");
	# Check to see if the game is full.  If it is run all the start up stuff.
	if ( $num_players == $game->{'max_players'} && time_to_start($game->{'id'})) {
	  print "Start Game\n";
	  # Randomly Assign Roles
	  print "Assign roles\n";
	  randomly_assign_roles($game->{'id'},$game->{'automod_id'});
      print "Activate night actions\n";
	  update_night_actions($game->{'id'});
	  set_game_status($game->{'id'},'In Progress');
	  print "Post Vote Tally Rules\n";
	  # Post Auto Vote Tally Rules
	  open (FILE,"< /var/www/html/cassy_vote_tally.txt") or die "Couldn't open file";
	  my $message = "";
	  while ( my $line = <FILE> ) {
        $message .= $line;
	  }
	  close FILE;
	  $message .= "\n";
	  if ( $game->{'auto_vt'} eq "lhv" ) {
        $message .= "Your Moderator has chosen to use the Longest Held Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n\n";
	  } else {
        $message .= "Your Moderator has chosen to use the Longest Held Last Vote method for a tiebreaker - This is just for Cassandra system, and there may be a different tiebreaker specified by your Moderator in the ruleset.\n\n";
	  }
	  $message .= "Vote Log Page: http://cassandrawerewolf.com/game/".$thread_id."/votes\n";
	  $message .= "Vote Tally Page: http://cassandrawerewolf.com/game/".$thread_id."/tally\n";
	  system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
	  # Activate Game Communications System
	  print "Activate Communication System\n";
      my @player_list = get_player_list($game->{'id'},"ga_desc is not null and ga_desc != ''");
	  $mech->get("http://52.55.166.81/configure_chat.php?game_id=$game->{'id'}");
	  $mech->form_name('add_chat_form');
      foreach my $player ( @player_list ) {
       $mech->set_fields('defcol_'.$player => 'on');
      }
	  $mech->click_button(name => 'submit_all');
	  my @groups = get_group_names($template->{'id'});
      push(@groups, 'Dead Chat', 'zCassyDebug');
	  foreach my $group ( @groups ) {
        my $sth_members = $dbh->prepare("select user_id from Players where ga_group=? and game_id=?");
		$sth_members->execute($group,$game->{'id'});
        my $room_name = $group;
        if ( $group ne "zCassyDebug") { $room_name = ' '.$group; }
	    $mech->get("http://52.55.166.81/configure_chat.php?game_id=$game->{'id'}");
	    $mech->form_name('add_chat_form');
	    $mech->set_fields('chat_name' => $room_name);
		if ( $sth_members->rows() >= 1 ) {
	      while ( my $user_id = $sth_members->fetchrow_array() ) {
	        if ( $user_id < 100 ) { $user_id = substr($user_id,1); }
	        if ( $user_id < 10 ) { $user_id = substr($user_id,2); }
	        $mech->set_fields('player_'.$user_id => 'on');
            $mech->set_fields('defcol_'.$user_id => 'on');
	      } 
		}
	    $mech->click_button(name => 'submit_newchat');
	  }
	  # Post instruction for Game Orders
	  print "Give instructions for Orders\n";
	  foreach my $player ( @player_list ) {
        print "Player: $player\n";
	    my $room_id = get_room_id($game->{'id'},$player,'Mod%');
        print "Room_id: $room_id\n";
	    my $message = "Please use the Order Command Form to your right to input your night_actions.  You can change you mind up until the deadline, and the last submitted name will be the one used.";
		post_chat_message($room_id,$message);
	  }
	  #Enable Attributes
	  print "Enable Attributes\n";
	  @player_list = get_player_list($game->{'id'});
	  foreach my $player ( @player_list ) {
        my $role_info = get_role_info($player,$game->{'id'});
		if ( $role_info->{'attribute'} ne "" ) {
		  append_to_players_field($player,$game->{'id'},'mod_comment',$role_info->{'attribute'});
          if ( $role_info->{'a_hidden'} == 0) {
		    my $room_id = get_room_id($game->{'id'},$player,"Mod%");
			my $message = "You have a ".$role_info->{'attribute'}." attribute.";
			post_chat_message($room_id,$message);
		  }
		}
		if ( $role_info->{'attribute'} eq "Brutal" ) {
		  append_to_players_field($player,$game->{'id'},'ga_desc','Brutal Kill');
		  my $sth_action = $dbh->prepare("update Players set game_action='alive' where user_id=? and game_id=?");
		  $sth_action->execute($player,$game->{'id'});
		} 
		if ( $role_info->{'attribute'} eq "Tough" ) {
		  my $sth_tough = $dbh->prepare("update Players set tough_lives=1 where user_id=? and game_id=?");
		  $sth_tough->execute($player,$game->{'id'});
		} 
	  }
	  # Randomply Assign Tinker and/or white hat to a villager.
	  print "Assing tinker and white_hat if needed\n";
	  if ( $template->{'random_tinker'} == 1 ) {
	    my @villagers = get_role_list($game->{'id'},"04");
		my $num_villagers = $#villagers;
		my $villager_id = $villagers[int(rand($num_villagers))];
		append_to_players_field($villager_id,$game->{'id'},'mod_comment','Tinker');
	  }
	  if ( $template->{'random_whitehat'} == 1 ) {
	    my @villagers = get_role_list($game->{'id'},"04");
		my $num_villagers = $#villagers;
		my $villager_id = $villagers[int(rand($num_villagers))];
		append_to_players_field($villager_id,$game->{'id'},'mod_comment','White Hat');
	  }
	  # Give out N0 Views
	  print "Give out N0 Views\n";
	  if ( $template->{'random_n0'} eq 'yes' ) {
        night_0_views($game->{'id'});
	  }
	  # Let any players that get N0 role info learn it now
	  print "Give out N0_knows info\n";
	  @player_list = get_player_list($game->{'id'});
	  foreach my $player ( @player_list ) {
	    my $role_info = get_role_info($player,$game->{'id'});
		if ( $role_info->{'n0_knows'} ne "none" ) {
	      my $message = reveal_who_is($game->{'id'},$role_info->{'n0_knows'});
		  my $room_id = get_room_id($game->{'id'},$player,'Mod%');
		  post_chat_message($room_id,$message);
		}
	  }
	  print "Activate Missing warning system\n";
	  # Activate Missing Player Warning System
	  my $sth_missing = $dbh->prepare("update Games set missing_hr=24 where id=?");
	  $sth_missing->execute($game->{'id'});
	  # GeekMail players that game has started
	  $mech->get("http://52.55.166.81/pm_players.php?game_id=".$game->{'id'});
      $mech->form_name('pm_player');
	  $mech->set_fields('all' => 'on');
	  $mech->click_button(name => 'submit');
	  $mech->form_name('send_message');
	  $mech->set_fields('bggpwd' => $bgg_pswd);
	  $mech->set_fields('subject' => 'Auto-Mod Game Starting');
	  $mech->set_fields('message' => "Auto-Mod Game http://www.boardgamegeek.com/thread/$thread_id is starting.\n  Please view the Cassy page to see your role and the Cassy chat to learn any N0 information your role may need.\n http://cassandrawerewolf.com/game/$thread_id");
	  $mech->click_button(name => 'submit');
	  print "Start Game\n";
	  if ( $template->{'random_n0'} eq "yes" ) {
	    $message = get_phase_change_message($game->{'id'},'day',1);
	    system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
		update_phase_change($game->{'id'});
		update_state($game->{'id'},'Day');
		update_timestamp($game->{'id'});
	  } else {
	    my $nighttime = next_deadline($game->{'id'},'night',1);
        $message = "It is Night 0.  If you have N0 orders please do that now.  Dawn will be posted $nighttime";
	    system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
		update_state($game->{'id'},'Night');
	  }
	}
  } else {
    # Game is In-Progress so check for needed actions.
	# Find out if enough players have nightfalled
	my $early_dusk;
	if (check_for_nightfall_ending($game->{'id'},$game->{'auto_vt'}) && $game->{'automod_state'} ne "lynch" ) {
      $early_dusk = 1;
	}
    #Check to see if Dawn should be posted.
	if ( $game->{'phase'} eq 'night' && ( test_phase_change($game->{'id'}) || all_game_actions_locked($game->{'id'}) )) {
	  print "Proccess Dawn\n";
	  if ( $game->{'day'} > 0 ) { check_player_activity($game->{'id'},$thread_id); }
	  # Give out Night View Results
	  print "Give out Night Views\n";
	  my @players = get_player_list($game->{'id'},"ga_desc like '\%view\%'");
	  # Add Random Role Seer to the list if in the game
	  my @rrs_list = get_role_list($game->{'id'},"26");
      push(@players, @rrs_list);
	  foreach my $player (@players) {
	    if ( is_alive($player,$game->{'id'}) ) {
          my $message = "";
          if ( is_blocked($player,$game->{'id'},$game->{'day'}) ) {
            $message = "A witch blocked your view.";
          } else {
		    my $role_info = get_role_info($player,$game->{'id'});
		    if ( $role_info->{'role_id'} eq '26' ) { $role_info->{'action_desc'} = 'View'; }
		    my $order_info = get_order_info($player,$game->{'id'},$game->{'day'},$role_info->{'action_desc'});
		    my $order_id = $order_info->{'target_id'};
		    $message = get_view_result($order_id,$role_info->{'view_result'},$game->{'id'});
          }
	      my $room_id = get_room_id($game->{'id'},$player,'Mod%');
		  post_chat_message($room_id,$message); 
	  	}
	  }
	  # Give out Night view results to Priest if they don't get to choose their night view.
	  print "Give out Priest View\n";
	  if ( role_in_game('07',$game->{'id'}) > 0 ) {
	    if ( $template->{'priest_type'} ne "choose" ) {
	      my @who_ids = get_role_list($game->{'id'},"07");
		  foreach my $who_id (@who_ids) {
		    if ( is_alive($who_id,$game->{'id'}) ) {
              my $message;
		  	  if ( $template->{'priest_type'} eq "lynch" || $template->{'priest_type'} eq "all" ) {
                if ( is_blocked($who_id,$game->{'id'},$game->{'day'}) ) {
                  $message = "A witch blocked your view.";
                } else {
		          my $role_info = get_role_info($who_id,$game->{'id'});
		          my $order_info = get_order_info($who_id,$game->{'id'},$game->{'day'},'Lynch View');
		          my $order_id = $order_info->{'target_id'};
		          $message = get_view_result($order_id,$role_info->{'view_result'},$game->{'id'});
                }
		        my $room_id = get_room_id($game->{'id'},$who_id,'Mod%');
		        post_chat_message($room_id,$message); 
		  	  }
			  if ( $template->{'priest_type'} eq "all" ) {
                my $message;
                if ( is_blocked($who_id,$game->{'id'},$game->{'day'}) ) {
                  $message = "A witch blocked your view.";
                } else {
		          my $role_info = get_role_info($who_id,$game->{'id'});
		          my $order_info = get_order_info($who_id,$game->{'id'},$game->{'day'},'Night Kill View');
		          my $order_id = $order_info->{'target_id'};
		          $message = get_view_result($order_id,$role_info->{'view_result'},$game->{'id'});
                }
		        my $room_id = get_room_id($game->{'id'},$who_id,'Mod%');
		        post_chat_message($room_id,$message); 
			  }
		    }
		  }
	    }
	  }
	  # Post Dawn
	  debug_message($game->{'id'},"Post Dawn\n");
	  my $message = get_phase_change_message($game->{'id'},'day',1);
	  update_phase_change($game->{'id'});
	  update_timestamp($game->{'id'});
	  if ( $game->{'day'} != 0 ) {
	    # Find out who the wolves killed.
        @players = get_player_list($game->{'id'},"ga_group is not null","ga_group");
		my $group;
		my $target_id;
		my $timestamp;
		my @targets;
		foreach my $player (@players) {
		  my $role_info = get_role_info($player,$game->{'id'});
          my $order_info = get_order_info($player,$game->{'id'},$game->{'day'},'Kill');
		  if ( $group eq $role_info->{'group_name'} && $role_info->{'group_name'} ne "") {
		    if ( $order_info->{'last_updated'} gt $timestamp ) {
              $target_id = $order_info->{'target_id'};
			  $timestamp = $order_info->{'last_updated'};
			}
		  } else {
		    if ( $group ne "" ) { push @targets, $target_id; }
			$group = $role_info->{'group_name'};
			$target_id = $order_info->{'target_id'};
			$timestamp = $order_info->{'last_updated'};
		  }
		}
		push @targets, $target_id;
        my @priestTargets;
		foreach my $target ( @targets ) {
          if ( $target eq "" ) { next; }
          my $target_name = user_name($target);
          debug_message($game->{'id'}, "$target_name was targeted, finding out if they are killed or not.\n");
	      # Find out if player was protected.
          $target = protection_check($target,$game->{'id'},$game->{'day'});
          if ( $target eq "" ) { next; }
		  if ( is_attribute($target,$game->{'id'},'Tough') ) {
		    if ( tough_lives($target,$game->{'id'}) > 0 ) {
              next;
			}
		  }
		  if ( $target ne "" ) {
		  print "Post who was killed\n";
	        $message .= "[b][Killed ".$target_name."][/b]\n";
			$message .= role_reveal($target,$game->{'id'});
            push @priestTargets, $target;
		  } else {
		    $message .= "[b]No Night Death[/b]";
		  }
		  if ( is_attribute($target,$game->{'id'},'Brutal') ) {
		    my @brutal_info = get_brutal_kill($target,$game->{'id'},$game->{'day'},$game->{'phase'},@priestTargets);
            $message .= shift(@brutal_info);
            push @priestTargets, @brutal_info;
		  }
        }
        # Promote any players if needed - also move players to dead chat and lock them out of any current chat rooms.
        foreach my $target ( @priestTargets ) {
          debug_message($game->{'id'},"Processing kill: $target");
          promote_player($target,$game->{'id'});
          move_to_deadchat($target,$game->{'id'});
        }
		# Reveal results to priest if needed.
		if ( $template->{'priest_type'} eq 'all' ) {
           my @who_ids = get_role_list($game->{'id'},"07");
           foreach my $who_id ( @who_ids ) {
		     if ( is_alive($who_id,$game->{'id'}) ) {
               foreach my $ptarget ( @priestTargets ) {
	             my $sth_priest_view = $dbh->prepare("insert into Game_orders (user_id, game_id, `desc`, target_id, day) values ( ?, ?, 'Night Kill View', ?, ?)");
		         my $tomorrow = $game->{'day'} + 1;
		         $sth_priest_view->execute($who_id,$game->{'id'},$ptarget,$tomorrow);
               }
		     }	 
		   }
		}
	  }
	  system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
	  my $sth_collect = $dbh->prepare("update Post_collect_slots set last_dumped=Null where game_id=?");
	  $sth_collect->execute($game->{'id'});
	}
	# Check to see if Dusk should be posted.
	if ( $game->{'phase'} eq 'day' && ( test_phase_change($game->{'id'}) || $early_dusk)) {
      print "Post Dusk\n";
	  my $message = get_phase_change_message($game->{'id'},'night',1);
	  update_phase_change($game->{'id'});
	  system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
	  $mech->get('http://52.55.166.81/vote_tally.php?action=retrieve&game_id='.$game->{'id'});
	  update_state($game->{'id'},'lynch');
	}
	# Check to see if Vote Tally is Up to date.
	if ( $game->{'phase'} eq 'night' && $game->{'automod_state'} eq "lynch" ) {
	  print "Posting Lynch Results\n";
	  my $table = "Tally_display_".$game->{'auto_vt'};
      #my $sth_vote = $dbh->prepare("select votee from $table where game_id=? and day=?  and  total > 0 limit 0,1");
			my $sth_vote = $dbh->prepare("select votee from (select votee, total from $table where game_id=? and day=?) tally where total > 0 limit 0,1");
	  $sth_vote->execute($game->{'id'},$game->{'day'});
	  my $votee = $sth_vote->fetchrow_array();
	  my $votee_id = user_id($votee);
	  my $killed = 1;
	  if ( is_attribute($votee_id,$game->{'id'},'Tough') ) {
        if ( tough_lives($votee_id,$game->{'id'}) > 0 ) {
        # if a witch targeted the expected lynchee their order should still be locked
        lock_witches($votee_id,$game->{'id'},$game->{'day'});
        $killed = "";
		}
	  }
	  my $message;
      my @priestTargets = ();
	  if ( $killed && $votee ne "") {
	    $message = "[b][Killed $votee][/b]\n";
        push @priestTargets, $votee_id;
	    $message .= role_reveal($votee_id,$game->{'id'});
	    if ( is_attribute($votee_id,$game->{'id'},'Brutal') ) {
          my @brutal_info = get_brutal_kill($votee_id,$game->{'id'},$game->{'day'},$game->{'phase'},@priestTargets);
          $message .= shift(@brutal_info);
          push @priestTargets, @brutal_info;
		}
	  } else {
        $message = "[b]No Lynch[/b]\n";
	  }
      debug_message($game->{'id'},"Night kills:".$message.", IDs:".@priestTargets."\n");
      # Promote any players if needed - also move players to dead chat and lock them out of any current chat rooms.
      foreach my $target ( @priestTargets ) {
		if ( $target == 578 || $target == 580) { next; }
        debug_message($game->{'id'},"Processing kill: $target\n");
        promote_player($target,$game->{'id'});
        move_to_deadchat($target,$game->{'id'});
        lock_witches($target,$game->{'id'},$game->{'day'});
      }
	  # Reveal results to priest if needed.
	  if ( $template->{'priest_type'} eq 'lynch' || $template->{'priest_type'} eq 'all' ) {
        my @who_ids = get_role_list($game->{'id'},"07");
        foreach my $who_id ( @who_ids ) {
	      if ( is_alive($who_id,$game->{'id'}) ) {
            foreach my $ptarget ( @priestTargets) {
			  if ( $ptarget == 578 || $ptarget == 580) { next; }
	          my $sth_priest_view = $dbh->prepare("insert into Game_orders (user_id, game_id, `desc`, target_id, day) values ( ?, ?, 'Lynch View', ?, ?)");
		      $sth_priest_view->execute($who_id,$game->{'id'},$ptarget,$game->{'day'});
            }
	      }	 
	    }
	  }
	  # Choose Random Role Seer's View
	  if ( role_in_game('26',$game->{'id'}) > 0 ) {
        my @rrs_list = get_role_list($game->{'id'},"26");
		foreach my $rrs ( @rrs_list ) {
          my $sth_votes = $dbh->prepare('select voter from Tally where game_id=? and votee=? and day=? and unvote=0 order by rand() limit 0,1');
		  $sth_votes->execute($game->{'id'},$rrs,$game->{'day'});
		  if ( $sth_votes->rows() > 0 ) {
		    my $view_id = $sth_votes->fetchrow_array();
            my $sth_view = $dbh->prepare("insert into Game_orders (user_id, game_id, `desc`, target_id, day) VALUES (?, ?, 'View', ?, ?)");
			$sth_view->execute($rrs,$game->{'id'},$view_id,$game->{'day'});
		  }
		}
	  }
	  system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
	  update_state($game->{'id'},'Night');
	  my $sth_collect = $dbh->prepare("update Post_collect_slots set last_dumped=Null where game_id=?");
	  $sth_collect->execute($game->{'id'});

	}
	# Check to see if the game is finished.
	# Count Wolves
	( my $num_wolves, my $num_nonw , my $num_hunters) = count_for_parity($game->{'id'});
	print "Wolves: $num_wolves Non: $num_nonw Hunt: $num_hunters\n";
	if ( $num_wolves == 0 ) {
      # Good wins.  They have killed all the wolves.
	  my $message = "Game Over.\nVillage has killed all the wolves.";
	  system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
	  set_game_status($game->{'id'},'Finished','Good');
	} elsif ( $num_nonw == $num_wolves ) {
      # Wolves have reached Parity.  Is the Hunter blocking their victory?
	  if ( $num_wolves == $num_hunters ) {
	    my $message ="Game Over.\nHunter kills the final wolf.";
	    system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
	    set_game_status($game->{'id'},'Finished','Good');
	  } else {
        # Hunter is not alive, so Wolves win.
	    my $message = "Game Over.\nWolves have reached parity.";
	    system("$post_script \"$cassy_cookie\" reply $thread_id \"$message\"");
	    set_game_status($game->{'id'},'Finished','Evil');
	  }
	}
  } # If for game status
  update_running($game->{'id'},'NULL');
} # Loop for each Auto-mod game.

