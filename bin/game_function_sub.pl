#!/usr/bin/perl

# These are subroutines used by the game_function.pl as well as the automod.pl
# scripts for automating certain actions.

############################################################################
#            This is code that needs to be run for                         #
#            both automod.pl and game_function.pl                          #
############################################################################
use vars qw($dbh $mech $now $bgg_user $bgg_pswd $weekend);

$bgg_user = "Cassandra Project";
$bgg_pswd = $ENV{'BGG_PASSWORD'};

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

# Log into BGG Web pages to make sure it is up and available.
print "Login to BGG\n";
$mech = WWW::Mechanize->new(autocheck => 1, quiet => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
if(!$mech->success()) {
  print "ERROR: cannot retrieve BGG login page\n";
  exit(1);
}

if($mech->form_number(1)) {
  $mech->field('username',$bgg_user);
  $mech->field('password',$bgg_pswd);
  $mech->submit;
} else {
  print "ERROR: cannot find BGG login form\n";
  exit(2);
}

# Log into Cassy Web pages to make sure they are up and available.
print "Log into Cassy\n";
$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://52.55.166.81/index.php?login=true');
if (!$mech->success()) {
  print "ERROR: cannot retrieve Cassy login page\n";
  exit(3);
}
if($mech->form_name('login_cassy')) {
  $mech->set_fields('uname' => $bgg_user);
  $mech->set_fields('pwd' => $bgg_pswd);
  $mech->set_fields('remember' => 'on');
  $mech->click_button(name => 'login');
} else {
  print "ERROR: cannot find Cassy Login form\n";
  exit(4);
}

# Get Now's date-time (no seconds)
my $format = '%Y-%m-%d %H:%i:00';
my $sth_now = $dbh->prepare("select date_format(now(),?)");
$sth_now->execute($format);
$now = $sth_now->fetchrow_array();
print "Now: $now\n";

# Find out if it is a weekend
$format = '%w';
my $sth_weekend = $dbh->prepare("select if(date_format(?,?)=0 || date_format(?,
?)=6,'1','') as weekend");
$sth_weekend->execute($now,$format,$now,$format);
$weekend = $sth_weekend->fetchrow_array();

############################################################################
#            Subroutines for posting Dawn and Dusk                         #
############################################################################

# Primary call from automod.pl or game_function.pl
sub get_phase_change_message($$$) {
  my $game_id = shift;
  my $phase = shift; # Going into day (post dawn) or night (post dusk)
  my $automod = shift; # true(1) or false();

  my $deadline = next_deadline($game_id,$phase,$automod);

  $message = "[b][Dawn][/b]\n";
  $message .= "Lynch will be at $deadline.\n";
  $m_message = "\nr{Please don't post until your moderator has posted the results of the night actions.}r\n";
  if ( $phase eq "night" ) {
    $message = "[b][Dusk][/b]\n";
    $message .= "Dawn will be at $deadline.\n";
    $message .="\nr{Please don't post until your moderator has posted the results of the lynch.}r\n";
    $m_message = "";
  }
  if ( !$automod ) { $message .= $m_message; }

  return $message;
}

# Should only be called directly if you don't want to post the default message given in get_phase_change_message.
sub next_deadline($$$) {
  my $game_id = shift;
  my $phase = shift; # The phase we are about to enter day or night.
  my $automod = shift; # true or false

  my $phase_length = get_phase_length($game_id,$phase,$automod);

  debug_message($game_id,"Next phase length:$phase_length\n");
  my $format = '%a. %b %e, %Y at %l:%i %p';
  my $sth_deadline=$dbh->prepare("select date_format(addtime(?,?),?) as text, addtime(?,?) as timestamp");
  $sth_deadline->execute($now,$phase_length,$format,$now,$phase_length);
  my $deadline = $sth_deadline->fetchrow_hashref();

  my $sth = $dbh->prepare("update Games set automod_nextdeadline=? where id=?");
  $sth->execute($deadline->{'timestamp'},$game_id);

  return $deadline->{'text'};
}

# This figures out how far into the future from now the next phase change
# should be.
sub get_phase_length($$$) {
  my $game_id = shift;
  my $phase = shift; # the phase we are trying to find the length of
  my $automod = shift; # true or false

  my $min_length = get_min_phase_length($game_id,$phase,$automod);
  debug_message($game_id,"Min phase Length:$min_length\n");

  my $phase_end = "lynch_time";
  if ( $phase eq "night" ) {
    $phase_end = "na_deadline";
  }

  debug_message($game_id,"Phase end:$phase_end\n");
  my $sth_phase_length = $dbh->prepare("select if(deadline_speed='Fast', ?, if(subtime($phase_end,time(?))>='0', subtime($phase_end,time(?)), subtime('24:00',subtime(time(?),$phase_end)))) as phase_length from Games where id=?");
  $sth_phase_length->execute($min_length,$now,$now,$now,$game_id);
  my $phase_length = $sth_phase_length->fetchrow_array();

  # if phase_length is too short add 1 day. (phase lenght will never be too short for a "fast" game.
  debug_message($game_id,"is phase_length ($phase_length) < min_length ($min_length)?\n");
  my $time_diff = time_difference($phase_length,$min_length);
  debug_message($game_id,"time diff = $time_diff");
  if ( $time_diff < 0 ) {
    $sth_phase_length = $dbh->prepare("select addtime(?,'24:00') as phase_length from Games where id=?");
    $sth_phase_length->execute($phase_length,$game_id);
  	$phase_length = $sth_phase_length->fetchrow_array();
	debug_message($game_id,"changed phase length to $phase_length");
  }

  # Adjust for weekend
  my $format = '%w';
  $sth_phase_length = $dbh->prepare("select if(automod_weekend=1,?,if((date_format(addtime(?,?),?)=6 or date_format(addtime(?,?),?)=0), addtime(?,'48:00'), ?)) as phase_length from Games where id=?");
  $sth_phase_length->execute($phase_length,$now,$phase_length,$format,$now,$phase_length,$format,$phase_length,$phase_length,$game_id);
  $phase_length = $sth_phase_length->fetchrow_array();
  debug_message($game_id,"adjusted for weekend to $phase_length");

  return $phase_length;
}

# This figures out the minimum phase length for day1 or for when there is a
# delay in when the game posted dusk or dawn.
sub get_min_phase_length($$$) {
  my $game_id = shift;
  my $phase = shift; # the phase we are finding the min length for.
  my $automod = shift; # true or false

 # for fast games day and night length are set
  my $sth_fast = $dbh->prepare("select deadline_speed, day_length, night_length from Games where id=?");
  $sth_fast->execute($game_id);
  $fast = $sth_fast->fetchrow_hashref();

  if ( $fast->{'deadline_speed'} eq "Fast" ) {
    if ( $phase eq 'day' ) {
      return $fast->{'day_length'};
    }
    return $fast->{'night_length'};
  }

  my $sth_night_length = $dbh->prepare("SELECT if(subtime(na_deadline,lynch_time)<0, addtime('24:00', subtime(na_deadline,lynch_time)), subtime(na_deadline,lynch_time)) as time FROM `Games` where id=?");
  $sth_night_length->execute($game_id);
  $night_length = $sth_night_length->fetchrow_array();

  if ( $phase eq 'night' ) { return $night_length; }

  my $min_time = "18:00:00";
  debug_message($game_id,"Min time should be 18 but is $min_time\n");
  debug_message($game_id,"Automod? $automod \n");
  if ( $automod ) {
    if ((24-$night_length) < $min_time ) { $min_time = (24-$night_length); }
    debug_message($game_id,"is 24-$night_length = $min_time?\n");
    return $min_time;
  }
  debug_message($game_id,"Automod code should not get here\n");

  my $sth_min_time = $dbh->prepare("select auto_deadline from Games where id=?");
  $sth_min_time->execute($game_id);
  $min_time = $sth_min_time->fetchrow_array();
  return $min_time;
}


# Find out if it is time to post dawn or dusk.
sub test_phase_change($game_id) {
  my $game_id = shift;
  my $format = '%Y,%m,%d,%H,%i';

  my $sth = $dbh->prepare("select date_format(automod_nextdeadline,?) as deadline, date_format(?,?) as current from Games where id=?");
  $sth->execute($format,$now,$format,$game_id);
  my $time = $sth->fetchrow_hashref();

  print "Deadline: ".$time->{'deadline'}." Current: ".$time->{'current'}."\n";

  return past_time($time->{'deadline'},$time->{'current'});

}

# This subroutine tests to see if a given time1 (y,m,d,h,min) is after a second given time2 (y,m,d,h,min)
sub past_time($$) {
  my $time1 = shift;
  my $time2 = shift;

  @t1 = split(/,/,$time1);
  @t2 = split(/,/,$time2);

  # Year
  if ( $t2[0] > $t1[0] ) {
    return 1;
  } elsif ( $t2[0] == $t1[0] ) {
    # Month
    if ( $t2[1] > $t1[1] ) {
      return 1;
    } elsif ( $t2[1] == $t1[1] ) {
      # Day
      if ( $t2[2] > $t1[2] ) {
        return 1;
      } elsif ( $t2[2] == $t1[2] ) {
        # Hour
        if ( $t2[3] > $t1[3] ) {
          return 1;
        } elsif ( $t2[3] == $t1[3] ) {
          # Min
          if ( $t2[4] >= $t1[4] ) {
            return 1 ;
          }
        }
      }
    }
  }
  return ;

}

# This is used to get the difference between time1 (hh:mm:ss) and time2 (hh:mm:ss)
sub time_difference($$) {
	my $time1 = shift;
	my $time2 = shift;

  	my @t1 = split(/:/,$time1);
  	my @t2 = split(/:/,$time2);

    my $sec1 = $t1[0]*60*60 + $t1[1]*60 + $t1[2];
    my $sec2 = $t2[0]*60*60 + $t2[1]*60 + $t2[2];

	return ($sec1 - $sec2);
}


# This is used to see if all players have locked their game orders for an
# early dawn.
sub all_game_actions_locked($) {
  my $game_id = shift;

  my $num_living = get_num_players($game_id,"(death_phase='Alive' or death_phase='' or death_phase IS NULL)");
  my $num_locked = get_num_players($game_id,"(death_phase='Alive' or death_phase='' or death_phase IS NULL) and ga_lock is not null");

  if ( $num_living == $num_locked ) {return 1;}
  return ;
}

# checks to see if dusk should fall early due to nightfall votes
sub check_for_nightfall_ending($$)
{
  my $game_id = shift;
  my $type = shift; # tie breaker type

  my $sth_leading_player;
  my $sth_second_player;
  my $sth_leading_nf;
  my $sth_second_nf;
  my $sth_all_non_nf;
  my $leading_nf;
  my $leading_name;
  my $second_nf;
  my $second_name;
  my $all_non_nf;
  my $result;
  my $view;
  my $sth_all_non_vt;
  my $all_non_vt;

  my $sth_phase = $dbh->prepare("select phase from Games where id=?");
  $sth_phase->execute($game_id);
  my $phase = $sth_phase->fetchrow_array();
  if ( $phase eq "night" ) { return; }

  $view = "Tally_display_" . $type;

  $sth_leading_player = $dbh->prepare("select t.votee from $view t, Games g where t.game_id=? and g.id = t.game_id and t.day=g.day limit 1;");
  $sth_leading_player->execute($game_id);
  $sth_leading_player->bind_columns(\$leading_name);
  $sth_leading_player->fetch;

  if($leading_name) {
    $sth_leading_nf = $dbh->prepare("select count(*) from Tally t, Games g, Users u where t.game_id=? and g.id = t.game_id and t.day=g.day and u.name=? and votee=u.id and nightfall=1;");
    $sth_leading_nf->execute($game_id, $leading_name);
    $sth_leading_nf->bind_columns(\$leading_nf);
    $sth_leading_nf->fetch;
  } else {
    $leading_nf = 0;
  }

  $sth_second_player = $dbh->prepare("select t.votee from $view t, Games g where t.game_id=? and g.id = t.game_id and t.day=g.day limit 1,1;");
  $sth_second_player->execute($game_id);
  $sth_second_player->bind_columns(\$second_name);
  $sth_second_player->fetch;

  if($second_name) {
    $sth_second_nf = $dbh->prepare("select count(*) from Tally t, Games g, Users u where t.game_id=? and g.id = t.game_id and t.day=g.day and u.name=? and votee=u.id and nightfall=1;");
    $sth_second_nf->execute($game_id, $second_name);
    $sth_second_nf->bind_columns(\$second_nf);
    $sth_second_nf->fetch;
  } else {
    $second_nf = 0;
  }

  $sth_all_non_nf = $dbh->prepare("select count(*) from Tally t, Games g where t.game_id=? and g.id = t.game_id and t.day=g.day and nightfall=0 and unvote=0;");
  $sth_all_non_nf->execute($game_id);
  $sth_all_non_nf->bind_columns(\$all_non_nf);
  $sth_all_non_nf->fetch;
  $all_non_nf = ($all_non_nf) ? $all_non_nf : 0;

  $sth_all_non_vt = $dbh->prepare("select get_non_voters_count(id, day) from Games where id=?;");
  $sth_all_non_vt->execute($game_id);
  $sth_all_non_vt->bind_columns(\$all_non_vt);
  $sth_all_non_vt->fetch;
  $all_non_vt = ($all_non_vt) ? $all_non_vt : 0;

  $result = 0;
  if(($all_non_nf + $all_non_vt) == 0 and defined($all_non_nf) and defined($all_non_vt)) {
      $result = 1;
  } elsif($type eq "lhlv") {
     if(($leading_nf >= $second_nf + $all_non_nf + $all_non_vt) and ($leading_nf > 0) ) {
      $result = 1;
    }
  } elsif($type eq "lhv") {
      if($leading_nf > $second_nf + $all_non_nf + $all_non_vt) {
      $result = 1;
      }
  } else {
      my $all_non_nf_non_vt = $all_non_nf + $all_non_vt;
      print $all_non_nf_non_vt;
      if($all_non_nf_non_vt == 0) {
        $result = 1;
      }
 }

  return($result);
}


############################################################################
#   Subroutines for Verifying players are still interested in playing      #
############################################################################

sub check_players($$$) {
  my $game_id = shift;
  my $game_name = shift;
  my $game_thread = shift;
  my $update_thread = "";

  print "Checking Players\n";

  # Delete un-confirmed.
  my @player_list = get_player_list($game_id, "update_time <= date_sub('$now', interval 24 hour) and need_to_confirm is not null");
  foreach my $player ( @player_list ) {
    my $sth = $dbh->prepare("delete from Players where user_id=? and game_id=?");
    $sth->execute($player,$game_id);
    system("/var/www/html/freshen_cache.php \"player\" $game_id");
    $update_thread = 1;
  }

  if ( $update_thread ) {
    # update the post in the thread with the correct player list.
    $sth = $dbh->prepare("select player_list_id , thread_id from Games where id=?");
    $sth->execute($game_id);
    my ($player_list_id , $thread_id )= $sth->fetchrow_array();
    @player_list = get_player_list($game_id);
    my $message = "Player List According to Cassandra:\n";
    foreach my $player ( @player_list ) {
      $message .= user_name($player)."\n";
    }
    $message .= "\n".scalar(@player_list)." players are signed up.\n";
    $message .= "\n To sign up for this game go to \n";
    $message .= "http://cassandrawerewolf.com/game/$thread_id\n";
    #system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" \"edit\" \"$player_list_id\" \"$message\"");
    system("/opt/werewolf/post_thread_cookie.pl \"/opt/werewolf/cookie.cassandra\" \"edit\" \"$player_list_id\" \"$message\"");
  }

  # Alert to re-confirm
  my $subject = "Please confirm your interest in playing $game_name";
  $message = "You have 24hrs to confirm that you are still interested in playing in '$game_name'.  If you do not confirm in that time your name will be removed from the player list. \n\n Go to http://cassandrawerewolf.com/game/$game_thread to confirm.\n";
  @player_list = get_player_list($game_id, "update_time <= date_sub('$now', interval 1 week)");
  foreach my $player ( @player_list ) {
    my $name = user_name($player);
    system("/opt/werewolf/send_geekmail.pl \"$bgg_user\" \"$bgg_pswd\" \"$name\" \"$subject\", \"$message\"" );
    my $sth = $dbh->prepare("update Players set need_to_confirm=1 where user_id=? and game_id=?");
    $sth->execute($player,$game_id);
  }

  return;
}

# Get a players name from user_id
sub user_name($) {
  my $user_id = shift;

  my $sth_name = $dbh->prepare("select name from Users where id=?");
  $sth_name->execute($user_id);

  my $name = $sth_name->fetchrow_array();

  return $name;
}


# Request replacements for players that havn't posted since the previous dawn.
# Should only be called at dawn. For Day 2+.
sub check_player_activity($$) {
  my $game_id = shift;
  my $game_thread = shift;

  print "Checking player activity\n";
  my @player_list = get_player_list($game_id,'');
  foreach my $player ( @player_list) {
    my $replace = "";
    if ( is_alive($player,$game_id) ){
      my $sth = $dbh->prepare("select count(*) from Posts where game_id=? and user_id=?");
      $sth->execute($game_id,$player);
      my $post_count = $sth->fetchrow_array();
      if ( $post_count == 0 ) {
        $replace = 1;
      } else {
        $sth = $dbh->prepare("select if(max(time_stamp) < automod_timestamp, 1, '') from Posts, Games where Posts.game_id=Games.id and game_id=? and user_id=?");
        $sth->execute($game_id,$player);
        $replace = $sth->fetchrow_array();
      }
    }
    if ( $replace ) {
      $sth = $dbh->prepare("update Players set need_replace='1' where user_id=? and game_id=?");
      $sth->execute($player,$game_id);
      my $user_name = user_name($player);
      my $message = $message = "r{ We are looking for a player to replace $user_name.  Please go to http://cassandrawerewolf.com/game/".$game_thread." to replace the player. }r";
      #system("/opt/werewolf/post_thread.pl \"$bgg_user\" \"$bgg_pswd\" reply $game_thread \"$message\"");
      system("/opt/werewolf/post_thread_cookie.pl \"/opt/werewolf/cookie.cassandra\" \"reply\" $game_thread \"$message\"");
    }
  }
  return;
}

############################################################################
#                         General use functions                            #
############################################################################

# Get number of players
sub get_num_players($) {
  my $game_id = shift;
  my $where = shift;

  if ( $where ) { $where = "and ".$where; }
  my $sth_count = $dbh->prepare("select count(*) from Players where game_id=? $where");
  $sth_count->execute($game_id);
  my $num_players = $sth_count->fetchrow_array();

  return $num_players;
}

# Find out if player is alive.
sub is_alive($$) {
  my $user_id = shift;
  my $game_id = shift;

  my $sth_alive = $dbh->prepare("select if(death_day is null or death_day = '', '1', '') as status from Players_all, Players where Players_all.original_id=Players.user_id and Players_all.game_id=Players.game_id and Players_all.user_id=? and Players_all.game_id=?");
  $sth_alive->execute($user_id,$game_id);

  my $result = $sth_alive->fetchrow_array();

  return $result;
}

# Get chat_room id based off of chat_room name
sub get_room_id($$$) {
  my $game_id = shift;
  my $user_id = shift;
  my $room_name = shift;

  my $sth_room = $dbh->prepare("select room_id from Chat_users, Chat_rooms where Chat_users.room_id=Chat_rooms.id and game_id=? and user_id=? and name like ?");
  $sth_room->execute($game_id,$user_id,$room_name);
  my $room_id = $sth_room->fetchrow_array();

  return $room_id;
}

sub debug_message($$) {
  my $game_id = shift;
  my $message = shift;

  my $error_room = get_room_id($game_id,'306','zCassyDebug');
  if ( $error_room == "" ) {
    print $message;
  } else {
    post_chat_message($error_room,$message);
  }
  return;
}

return 1;
