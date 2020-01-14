#!/usr/bin/perl

use List::Util qw(shuffle);

# This file contians the subroutines used in automod.pl

# Updates the game status field.
sub set_game_status($) {
  my $game_id = shift;
  my $status = shift;
  my $winner = shift;

  if ( $winner ne "" ) {
    my $sth_winner = $dbh->prepare("update Games set winner=? where id=?");
	$sth_winner->execute($winner,$game_id);
  }

  my $sth_status = $dbh->prepare("update Games set `status`=? where id=?");
  $sth_status->execute($status,$game_id);
  
  if ( $status eq "In Progress" ) {
    update_phase_change($game_id);
    system("/var/www/html/freshen_cache.php \"start\" $game_id");
  }
  if ( $status eq "Finished" ) {
    system("/var/www/html/freshen_cache.php \"end\" $game_id");
  }
  return;
}

# Updates the 'automod_phase_change' field in the Games table
sub update_phase_change() {
  my $game_id = shift;

  my $sth_phase_change = $dbh->prepare("update Games set automod_phase_change=date_sub(now(), interval 1 minute) where id=?");
  $sth_phase_change->execute($game_id);

  return;
}

# Update Players Table to give user Night Actions
sub update_night_actions($) {
  my $game_id = shift;

  my @player_list = get_player_list($game_id);
  foreach my $player ( @player_list ) {
    my $role_info = get_role_info($player,$game_id);
    my $sth_user = $dbh->prepare("select original_id from Players_all where user_id=? and game_id=?");
    $sth_user->execute($player,$game_id);
    $user_id = $sth_user->fetchrow_array();
    my $sth_orders = $dbh->prepare("update Players set game_action=?, ga_desc=?, ga_group=? where user_id=? and game_id=?");
    $sth_orders->execute($role_info->{'game_action'},$role_info->{'action_desc'},$role_info->{'group_name'},$user_id,$game_id);
  }

  return;
}

# Get random negative view
sub get_random_view($@) {
  my $game_id = shift;
  my @not_view = @_;

  my $not_statement = "";
  foreach my $nv (@not_view) {
    $not_statement .= "and role_id != $nv";
  }
  my $sth_view = $dbh->prepare("select user_id from Players where game_id=? and (mod_comment is null or mod_comment not like '\%Tinker\%') $not_statement order by rand() limit 0, 1");
  $sth_view->execute($game_id);
  my $view_id = $sth_view->fetchrow_array();

  return $view_id;
}

# Post a message to the chat room.
sub post_chat_message($$) {
  my $room_id = shift;
  my $message = shift;

  my $sth_post_view = $dbh->prepare("insert into Chat_messages (room_id, user_id, message, post_time) values ( ? , '306' , ? ,  now() )");
  $sth_post_view->execute($room_id,$message);

  return;
}

# Give the view result.
sub get_view_result($$$) {
  my $view_id = shift;
  my $view_result = shift;
  my $game_id = shift;
  my $message = "";

  my $sth_role = $dbh->prepare("select name, role_id, Roles.`type`, side from Players_all, Users, Players, Roles where Players_all.user_id=Users.id and Players_all.original_id=Players.user_id and Players_all.game_id=Players.game_id and Players.role_id=Roles.id and Players.game_id=? and Players_all.user_id=?");
  $sth_role->execute($game_id,$view_id);
  if ( $sth_role->rows() > 0 ) {
    my $view = $sth_role->fetchrow_hashref();
  
    ($look_for,$see_as) = split(/\sas\s/,$view_result);
    @look_choices = split(/\sor\s/,$look_for);

    $found = "not";
	my $not_tinker;
    foreach $look (@look_choices) {
      if ( $look eq "all" ) {
	    $found = "";
		$not_tinker = 1;
      } else {
        my $sth_type = $dbh->prepare("select `type` from Roles where id=?");
	    $sth_type->execute($look);
	    $type = $sth_type->fetchrow_array();
	    if ( $view->{'role_id'} eq $look) {
          $found = "";
        }
      }
    }
	# Check if tinker
	if ( ! $not_tinker ) {
	  #my $sth = $dbh->prepare("select * from Players where user_id=? and game_id=? and mod_comment like '\%Tinker\%'");
	  #$sth->execute($view_id,$game_id);
	  #if ( $sth->rows() == 1 ) {
	  if ( is_attribute($view_id,$game_id,"Tinker") ) {
         if ( $found == "not" ) { 
	       $found = "";
	     } else {
           $found = "not";
	     }
	  }
	}
    if ( $see_as eq 'role' ) {
	  if ( $found eq "not" ) {
	    $message = "You can not determine ".$view->{'name'}."'s role.";
	  } else {
        $message = $view->{'name'}." is a ".$view->{'type'};
	  }
    } elsif ( $see_as eq 'side' ) {
	  if ( $found eq "not" ) {
        $message = "You can not determine ".$view->{'name'}."'s side.";
	  } else {
        $message = $view->{'name'}." is ".$view->{'side'};
      }
    } elsif ( $see_as > 0 ) {
      my $sth_type = $dbh->prepare("select `type` from Roles where id=?");
      $sth_type->execute($see_as);
      $type = $sth_type->fetchrow_array();
      $message = $view->{'name'}." is $found a $type";
    } else {
      $message = $view->{'name'}." is $found $see_as";
    }
  } else {
    $message = "No orders found";
  }
  return $message;
}

# Randomly assigns players roles
sub randomly_assign_roles($) {
  my $game_id = shift;
  my $automod_id = shift;

  my $sth_players = $dbh->prepare("select user_id from Players where game_id=? order by rand()");
  $sth_players->execute($game_id);

  my @roles;
  my @sets = split(/,/,$template->{'num_player_sets'});
  for ( $i=0; $i<scalar(@sets); $i++ ) {
  my $set_num = $i+1;
    my $sth_roles = $dbh->prepare("select id, role_id, side from AM_roles where require_role=? and template_id = ? order by require_role  desc limit 0,".$sets[$i]);
    $sth_roles->execute($set_num,$automod_id);
    while ( my $role = $sth_roles->fetchrow_hashref() ) { 
      push @roles, $role; 
    }
  }
  my $sth_assign = $dbh->prepare("update Players set role_id = ?, side = ?, automod_role_id=? where user_id = ? and game_id = ?");
  my $count = 0;
  while ( my $player = $sth_players->fetchrow_array() ) {
    my $id = $roles[$count]->{'id'};
    my $role = $roles[$count]->{'role_id'};
    my $side = $roles[$count]->{'side'};
    $sth_assign->execute($role,$side,$id,$player,$game_id);
    $count++;
  }

  return;
}

# Get Group names from AM_roles
sub get_group_names($) {
  my $template_id = shift;

  my $sth = $dbh->prepare("select distinct group_name from AM_roles where template_id=? and group_name is not null");
  $sth->execute($template_id);
  my @groups;
  while ( my $row = $sth->fetchrow_array() ) {
    push @groups, $row;
    
  }

  return @groups;
}

# Give out N0 Views
sub night_0_views($) {
  my $game_id = shift;

  my @player_list = get_player_list($game_id,"ga_desc is not null and ga_desc != ''");
  foreach my $player ( @player_list ) {
    my $role_info = get_role_info($player,$game_id);
	if ( $role_info->{'n0_view'} ne "none" && $role_info->{'n0_view'} ne "user_choice") {
      my $room_id = get_room_id($game_id,$player,'Mod%');
	  my @not = split(/,/,$role_info->{'n0_view'});
	  my $view_id = get_random_view($game_id,@not);
	  my $message = get_view_result($view_id,$role_info->{'view_result'},$game_id);
	  post_chat_message($room_id,$message);
	}
  }

  return;
}


# Gets a required player list
sub get_player_list($$) {
  my $game_id = shift;
  my $where = shift;
  my $order = shift;

  if ( $where ) { $where = "and ".$where; }
  if ( $order eq "" ) { $order = "rand()"; }
  my $sth_list = $dbh->prepare("select Players_all.user_id from Players, Players_all where Players_all.original_id=Players.user_id and Players_all.game_id=Players.game_id and Players.game_id=? and `type` != 'replaced' $where order by $order");
  $sth_list->execute($game_id);
  my @player_list;
  while ( $user_id = $sth_list->fetchrow_array() ) {
    push @player_list, $user_id;
  }

  return @player_list;
}

sub get_role_list($$) {
  my $game_id = shift;
  my $role_id = shift;
  
  @player_list = get_player_list($game_id,"role_id='$role_id'");
  # Get promoted players
  my $sth_list = $dbh->prepare("select Players_all.user_id from Players, Players_all, AM_roles where Players_all.original_id=Players.user_id and Players_all.game_id=Players.game_id and Players.automod_promoted_id=AM_roles.id and Players.game_id=? and `type` != 'replaced' and AM_roles.id=? order by rand()");
  $sth_list->execute($game_id,$role_id);
  while ( $user_id = $sth_list->fetchrow_array() ) {
    push @player_list, $user_id;
  }
  @player_list = shuffle @player_list;
  return @player_list;
}

# Gets AM_role info for a given user_id
# Adjust for if the player is promoted or not
sub get_role_info($$) {
  my $user_id = shift;
  my $game_id = shift;

  my $sth = $dbh->prepare("select automod_role_id as original, automod_promoted_id as promoted from Players, Players_all where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players_all.user_id=? and Players_all.game_id=?");
  $sth->execute($user_id,$game_id);
  my $automod_id = $sth->fetchrow_hashref();

  $sth = $dbh->prepare("select * from AM_roles where id=?");
  $sth->execute($automod_id->{'original'});
  my $original_role_info = $sth->fetchrow_hashref();
  my $role_info = $original_role_info;
  if ( $automod_id->{'promoted'} ne "" ) {
    $sth = $dbh->prepare("select * from AM_roles where id=?");
    $sth->execute($automod_id->{'promoted'});
    my $promoted_role_info = $sth->fetchrow_hashref();
    $role_info->{'role_id'} = $promoted_role_info->{'role_id'};
    $role_info->{'game_action'} = $promoted_role_info->{'game_action'};
    $role_info->{'action_desc'} = $promoted_role_info->{'action_desc'};
    $role_info->{'group_name'} = $promoted_role_info->{'group_name'};
    $role_info->{'view_result'} = $promoted_role_info->{'view_result'};
    $role_info->{'reveal_as'} = $promoted_role_info->{'reveal_as'};
    $role_info->{'promotion'} = $promoted_role_info->{'promotion'};
    if ( $original_role_info->{'promotion_parity'} eq "no" ) {
      $role_info->{'parity'} = $promoted_role_info->{'parity'};
    }
  }
  return $role_info;
}

sub role_in_game($$) {
  my $role_id = shift;
  my $game_id = shift;

  my $sth = $dbh->prepare("select count(*) from Players where role_id=? and game_id=?");
  $sth->execute($role_id,$game_id);
  my $count = $sth->fetchrow_array();

  return $count;
}

# Create a messge of who is what based on a list of roles.
sub reveal_who_is($) {
  my $game_id = shift;
  my $role_list = shift;

  my $message;
  my @role_list = split(/,/,$role_list);
  foreach my $role_id ( @role_list ) {
    my @players = get_player_list($game_id,"role_id='$role_id'");
	foreach my $player ( @players ) {
      $message .= get_view_result($player,'all as role',$game_id);
	  $message .= "<br />";
	}
  }

  return $message;
}

# Appends a string onto the end of a given field in the Players table.
sub append_to_players_field($$$$) {
  my $user_id = shift;
  my $game_id = shift;
  my $field = shift;
  my $comment = shift;

  my $sth = $dbh->prepare("update Players set $field= (concat_ws(', ',if($field='',NULL,$field),?)) where user_id=? and game_id=?");
  $sth->execute($comment,$user_id,$game_id);

  return;
}

# Finds out if the game is in the process of being run by another run of automod.pl
sub is_running_now($) {
  my $game_id = shift;

  my $sth = $dbh->prepare("select automod_running from Games where id=?");
  $sth->execute($game_id);

  my $running = $sth->fetchrow_array();

  return $running;
}

# Updates the automod_running field in the Games tabel for when automod is processing a game to when it is finished
sub update_running($) {
  my $game_id = shift;
  my $running = shift;

  my $sth = $dbh->prepare("update Games set automod_running=$running where id=?");
  $sth->execute($game_id);

  return;
}

# Updates the automod_state field in the Games table.
sub update_state($$) {
  my $game_id = shift;
  my $state = shift;

  my $sth = $dbh->prepare("update Games set automod_state=? where id=?");
  $sth->execute($state,$game_id);

  return;
}

# Gets the most recient order information from the Game_orders table
sub get_order_info($$$$) {
  my $user_id = shift;
  my $game_id = shift;
  my $day = shift;
  my $order = shift;

  my $order_info;

  my $sth = $dbh->prepare("select if(cancel is not null,'',target_id) as target_id, last_updated from Game_orders where user_id=? and game_id=? and day=? and `desc`=? order by last_updated desc limit 0,1");
  $sth->execute($user_id, $game_id, $day, $order);

  my $order_info = $sth->fetchrow_hashref();
  
  return $order_info;
}

# Checks to see if the user has the specified attribute in the mod_comment field.
sub is_attribute($$$) {
  my $user_id = shift;
  my $game_id = shift;
  my $attribute = shift;

  #Get original user_id if user_id is a replacement player
  my $sth = $dbh->prepare("select user_id from Replacements where replace_id=? and game_id=?");
  $sth->execute($user_id,$game_id);
  my $original_id = $sth->fetchrow_array(); 
  if ( $original_id ne "" ) { $user_id = $original_id; }
  $sth = $dbh->prepare("select * from Players where mod_comment like '\%$attribute\%' and user_id = ? and game_id=?");
  $sth->execute($user_id,$game_id);

  my $count = $sth->rows();

  my $return = "";
  if ( $count > 0 ) { $return = 1; }

  return $return;
}

# This checks to see if a player's order is blocked by a witch.
sub is_blocked($$$) {
  my $player_id = shift;
  my $game_id = shift;
  my $game_day = shift;

  # Get a list of witches
  my @witches_list = get_role_list($game_id,"14");

  foreach my $witch (@witches_list) {
    if ( is_alive($witch,$game_id) ) {
      my $role_info = get_role_info($witch,$game_id);
      my $order_info = get_order_info($witch,$game_id,$game_day,$role_info->{'action_desc'});
      my $order_id = $order_info->{'target_id'};
      if ( $order_id eq "" ) { next; }
      if ( $game_day > 1 ) {
        my $yesterday_order_info = get_order_info($witch,$game_id,$game_day-1,$role_info->{'action_desc'});
        my $yesterday_order_id = $yesterday_order_info->{'target_id'};
        # Can't block the same person two days in a row.
        if ( $yesterday_order_id == $order_id ) { next; }
      }
      if ( $order_id == $player_id ) {
        # Check that the blocking witch wasn't blocked.
        # Witches' order should be locked even if it was blocked.
        lock_order($witch,$game_id);
        if ( !is_blocked($witch,$game_id,$game_day) ) { 
          return 1; 
        }
      }
    }
  }

  # no witch blocked
  return ;
}

sub lock_witches($$) {
  my $target_id = shift;
  my $game_id = shift;
  my $game_day = shift;

  # if a witch was targeting a lynch victim her order is now locked.
  my @witches_list = get_role_list($game_id,"14");
  foreach my $witch (@witches_list) {
    if ( is_alive($witch,$game_id) ) {
      my $role_info = get_role_info($witch,$game_id);
      my $order_info = get_order_info($witch,$game_id,$game_day,$role_info->{'action_desc'});
      my $order_id = $order_info->{'target_id'};
      debug_message($game_id,"Witch:$witch targeted $order_id, $target_id was killed\n");
      if ( $order_id eq "" ) { next; }
      if ( $order_id == $target_id ) { lock_order($witch,$game_id); }
    }
  }
  return;
}

sub lock_order($$) {
  my $user_id = shift;
  my $game_id = shift;

  debug_message($game_id,"User $user_id 's order is being locked.\n");
  my $sth=$dbh->prepare("update Players set ga_lock='1' where user_id=? and game_id=?");
  $sth->execute($user_id,$game_id);

  my $room_id = get_room_id($game_id,$user_id,'Mod%');
  my $message = "Your order ability has been locked.";
  post_chat_message($room_id,$message);

  return;
}

#Checks if the target was protected, and returns null if BG blocked, the martyrs id if the martyr protected, or the original target id, if nobody protected.
sub protection_check($$$) {
  my $target_id = shift;
  my $game_id = shift;
  my $game_day = shift;
  
  # Check for bodyguards
  @bodyguard_list = get_role_list($game_id,"06");
  foreach my $bg ( @bodyguard_list ) {
    if ( is_alive($bg,$game_id) && !is_blocked($bg,$game_id,$game_day)) {
      my $role_info = get_role_info($bg,$game_id);
      my $guard_info = get_order_info($bg,$game_id,$game_day,$role_info->{'action_desc'});
      # Verify they didn't guard this person yesterday
      my $yesterday = $game_day-1;
      if ( $yesterday > 0 ) {
        my $yesterday_info = get_order_info($bg,$game_id,$yesterday,$role_info->{'action_desc'});
        if ( $yesterday_info->{'target_id'} == $guard_info->{'target_id'} ) { next; }
      }
      if ( $target_id == $guard_info->{'target_id'} ) {
        return "";
      } 
    }
  }

  # Check for martyrs
  @martyr_list = get_role_list($game_id,"23");
  foreach my $martyr (@martyr_list) {
    if ( is_alive($martyr,$game_id) && !is_blocked($martyr,$game_id,$game_day)){
      my $role_info = get_role_info($martyr,$game_id);
      my $protect_info = get_order_info($martyr,$game_id,$game_day,$role_info->{'action_desc'});
      if ( $target_id == $protect_info->{'target_id'} ) {
        return $martyr;
      }
    }
  }
  
  return $target_id;
}

sub tough_lives($$) {
  my $user_id = shift;
  my $game_id = shift;

  my $sth = $dbh->prepare("select tough_lives, Players.user_id from Players, Players_all where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players_all.user_id=? and Players_all.game_id=?");
  $sth->execute($user_id,$game_id);

  my $row = $sth->fetchrow_hashref();
  my $current_lives = $row->{'tough_lives'};
  my $player_id = $row->{'user_id'};
  if ( $current_lives > 0 ) {
    my $new_lives = $current_lives - 1;
	$sth = $dbh->prepare("update Players set tough_lives = ? where user_id=? and game_id=?");
	$sth->execute($new_lives,$player_id,$game_id);
	my $room_id = get_room_id($game_id,$user_id,'Mod%');
	my $message = "You were targeted for a Kill you have $new_lives left.";
    post_chat_message($room_id,$message);
  }

  return $current_lives;
}

# Creates the [Killed] message for a brutal kill
sub get_brutal_kill($) {
  my $user_id = shift;
  my $game_id = shift;
  my $day = shift;
  my $phase = shift;
  my @alreadyKilled = @_;

  if ( is_blocked($user_id,$game_id,$day) ) { return; }
  my $message;
  my @targets;
  my @return_info;
  my $brutal_info = get_order_info($user_id,$game_id,$day,' Brutal Kill');
  my $brutal_id = $brutal_info->{'target_id'};
  if ( $brutal_id eq "" ) {
    $brutal_info = get_order_info($user_id,$game_id,$day,'Brutal Kill');
    $brutal_id = $brutal_info->{'target_id'};
  }
  my $brutal_name = user_name($brutal_id);
  if ( $brutal_id ne "" && !(grep/$brutal_id/, @alreadyKilled) ) {
     # Find out if target is already killed
     if ( is_attribute($brutal_id,$game_id,'Tough') ) {
       if ( tough_lives($brutal_id,$game_id) > 0 ) {
         #push @targets, $brutal_id;
         #push @alreadyKilled, $brutal_id;
         push @return_info, $message;
         push @return_info, @targets;
         return @return_info;
	   }
	 }

     if ( $brutal_name ne "" ) {
       $message .= "[b][Killed ".$brutal_name."][/b]\n";
	   $message .= role_reveal($brutal_id,$game_id);
       push @targets, $brutal_id;
	 }

	 if ( is_attribute($brutal_id,$game_id,'Brutal') ) {
	   my @brutal_return_info = get_brutal_kill($brutal_id,$game_id,$day,$phase,@alreadyKilled);
       $message .= shift(@brutal_return_info);
       push @targets, @brutal_return_info;
       push @alreadyKilled, @brutal_return_info;
	 }
  }

  push @return_info, $message;
  push @return_info, @targets;
  return @return_info;
}

#Creates the role reveal message if needed.
sub role_reveal($) {
  my $user_id = shift;
  my $game_id = shift;

  my $message = "";
  my $show;
  if ( $template->{'role_reveal'} eq "yes" ) {
    $show = 1;   
  } else {
    # Check for passive priest
	if ( $template->{'priest_type'} eq "passive") {
      my @players = get_player_list($game_id,"role_id='07'");
	  foreach my $player ( @players ) {
        if ( is_alive($player,$game_id) ) {
          $show = 1;
		}
	  }
	}
	# Check for white hat
	if ( is_attribute($user_id,$game_id,'White Hat') ) {
      $show = 1;
	}
	if ( is_role($user_id,$game_id,'25') ) {
      $show = 1;
	}
  }

  if ( $show ) {
    my $role_info = get_role_info($user_id,$game_id);
	my $reveal_as = $role_info->{'reveal_as'};
	if ( $reveal_as eq 'role' ) {
	  my $sth = $dbh->prepare("select `type` from Roles where id=?");
	  $sth->execute($role_info->{'role_id'});
	  my $role = $sth->fetchrow_array();
	  $message = user_name($user_id)." was a $role\n";
	} elsif ( $reveal_as eq 'side' ) {
	  $message = user_name($user_id)." was ".$role_info->{'side'}."\n";
    } elsif ( $reveal_as eq 'both' ) {
	  my $sth = $dbh->prepare("select `type` from Roles where id=?");
	  $sth->execute($role_info->{'role_id'});
	  my $role = $sth->fetchrow_array();
      $message = user_name($user_id)." was a ".$role_info->{'side'}." $role\n";
	} elsif ( $reveal_as eq 'none' ) {
	  $message = "";
	} else {
	  $message = user_name($user_id)." was $reveal_as\n";
	}
  }

  return $message;
}

#Finds out if the player is a given role_id
sub is_role($$$) {
  my $user_id = shift;
  my $game_id = shift;
  my $role_id = shift;

  my $answer;
  my $role_info = get_role_info($user_id,$game_id);
  if ( $role_info->{'role_id'} eq $role_id ) {
     $answer = 1;
  } 

  return $answer;
}

#gets a user_id from a name
sub user_id($) {
  my $name = shift;

  my $sth = $dbh->prepare("select id from Users where name=?");
  $sth->execute($name);

  my $user_id = $sth->fetchrow_array();

  return $user_id;
}

# Gets the count of teh two gropus for parity checks;
sub count_for_parity($) {
  my $game_id = shift;

  $num_wolves = 0;
  $num_nonw = 0;
  $num_hunters = 0;
  my @living_players = get_player_list($game_id,"(death_phase='Alive' or death_phase='' or death_phase IS NULL)");
  foreach $player ( @living_players ) {
    my $role_info = get_role_info($player,$game_id);
    if ( $role_info->{'parity'} ) { 
      $num_wolves++; 
    } else {
      $num_nonw++;
      if ( $role_info->{'role_id'} eq "05" ) { $num_hunters++; }
    }
  } 

  my @array;
  push @array, $num_wolves;
  push @array, $num_nonw;
  push @array, $num_hunters;

  return @array;

}

sub promote_player($) {
  my $killed_id = shift;
  my $game_id = shift;

  # Find out of the killed players role_id had anybody that would promote to
  # it.
  
  my $killed_role_info = get_role_info($killed_id,$game_id);
  my $sth=$dbh->prepare("select AM_roles.id as id from AM_roles, Games where promotion like '%".$killed_role_info->{'role_id'}."%' and AM_roles.template_id=Games.automod_id and Games.id=? order by rand()");
  $sth->execute($game_id);
  
  while ( $promotion = $sth->fetchrow_array() ) {
    @players = get_player_list($game_id,"automod_role_id='$promotion' or automod_promoted_id='$promotion'");
    foreach my $player (@players) {
      my $player_info = get_role_info($player,$game_id);
      if ( is_alive($player,$game_id) && $player_info->{'role_id'} ne $killed_role_info->{'role_id'} && $player_info->{'promotion'} =~ /$killed_role_info->{'role_id'}/ ) {
        # This player will be promoted. 
        # Notify the player in their Mod chat that they have been promoted.
        my $room_id = get_room_id($game_id,$player,'Mod%');
        my $message = "You have been promoted to ".user_name($killed_id)."'s role.  Please refresh your chat room page to have access to that players powers and/or chat room.";
        post_chat_message($room_id,$message); 

        # Change Player Information
        my $sth=$dbh->prepare("select original_id from Players_all where user_id=? and game_id=?");
        $sth->execute($player,$game_id);
        my $original_id = $sth->fetchrow_array();
        $sth=$dbh->prepare("update Players set automod_promoted_id=?, game_action=?, ga_desc=?, ga_group=? where user_id=? and game_id=?");
        $sth->execute($killed_role_info->{'id'},$killed_role_info->{'game_action'},$killed_role_info->{'action_desc'},$killed_role_info->{'group_name'},$original_id,$game_id);  
 
        # Add player to any Chat rooms needed.
        my $room_id = get_room_id($game_id,$killed_id,"%".$killed_role_info->{'group_name'}); 
        my $mod_room_id = get_room_id($game_id,$killed_id,'Mod%');
        if ( $room_id != "" && $room_id != $mod_room_id ) {
          my $chat_color = get_chat_color($player);
          $sth=$dbh->prepare("insert into Chat_users (room_id, user_id, color, open) VALUES(?,?,?,'2007-01-01 00:00:00')");
  $sth->execute($room_id,$player,$chat_color);
          my $message = user_name($player)." has been promoted and can join this chat room.";
          post_chat_message($room_id,$message);
        }
        return;
      }
    }
  }

  return;
}

sub move_to_deadchat($$) {
  my $target = shift;
  my $game_id = shift;
 
  my $player_info = get_role_info($target,$game_id);
  debug_message($game_id,"Player in group chat: ".$player_info->{'group_name'}."\n");
  if ( $player_info->{'group_name'} ne "" ) {
    my $room_id = get_room_id($game_id,$target," ".$player_info->{'group_name'});
    debug_message($game_id,"locking $room_id for $target\n");
    my $sth=$dbh->prepare("update Chat_users set `lock`='Secure' where room_id=? and user_id=?");
    $sth->execute($room_id,$target);
  } 
  my $room_id = get_room_id($game_id,'306',' Dead Chat'); 
  my $chat_color = get_chat_color($target);
  $sth=$dbh->prepare("insert into Chat_users (room_id, user_id, color, open) VALUES(?,?,?,'2007-01-01 00:00:00')");
  $sth->execute($room_id,$target,$chat_color);

  return;
}

sub get_chat_color($) {
  my $player_id = shift;

  my $def_color = "#000000";
  my $sth=$dbh->prepare("select chat_color from Bio where user_id=?");
  $sth->execute($player_id);
  my $color = $sth->fetchrow_array();
  if ( $color eq "" ) { return $def_color; }
  return $color;
}

#Test to see if the game should be starting
sub time_to_start($) {
  my $game_id = shift;

  my $f1 = '%Y,%m,%d,%H,%i';
  my $f2 = '%Y,%m,%d,0,0';

  $sth=$dbh->prepare("select if ( deadline_speed='Fast', date_format(start_date,?), date_format(start_date,?) ) as start, date_format(?,?) as current from Games where id=?");
  $sth->execute($f1,$f2,$now,$f1,$game_id);
  my $time = $sth->fetchrow_hashref();

  return past_time($time->{'start'},$time->{'current'});
}

# Checks to see if a game has been with out players in sign-up for over a week.  If so delete and return true.
sub check_game($$) {
  my $game_id = shift;

  if ( get_num_players($game_id) > 0 ) {
    return;
  } else {
    my $sth = $dbh->prepare("select if(automod_timestamp < date_sub(?, interval 1 week), 1, '' ) from Games where id=?");
    $sth->execute($now,$game_id);
    my $old = $sth->fetchrow_array();

    if ( $old ) {
      print "Deleting Game\n";
      $sth = $dbh->prepare("delete from Games where id=?");
      $sth->execute($game_id);
      return 1;
    } else {
      return;
    }
  }
}

# Updates the Game timestamp - should only be used at dawn.
sub update_timestamp($) {
  my $game_id = shift;

  print "Updating Timestamp\n";
  my $sth = $dbh->prepare("update Games set automod_timestamp=? where id=?");
  $sth->execute($now,$game_id);

  return;
}


1;
