#!/usr/bin/perl

use DBI;
use WWW::Mechanize;

$bgg_user = "Cassandra Project";
$bgg_pswd = $ENV{'BGG_PASSWORD'};

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

# This code will be used to send notices to Moderators when they have games in the system that have not yet started, but the start date is already past.  This will be run once a week.

$sth_game = $dbh->prepare("select id, title, thread_id, expired, datediff(now(),start_date) as days_past from Games where start_date < now() and status='Sign-up'");
$sth_game->execute();

while ( $game = $sth_game->fetchrow_hashref() ) {
  # Get Moderators names
  $sth_mod = $dbh->prepare("select name from Moderators, Users where Moderators.user_id=Users.id and game_id=?");
  $sth_mod->execute($game->{'id'});
  $to = "";
  $count = 0;
  while ( $mod = $sth_mod->fetchrow_hashref() ) {
  if ( $count > 0 ) { $to .= ", ";
  $to .= $mod->{'name'};
  $count++;
  }
  if ( $game->{'expired'} = 0 ) {
    $subject = "Past Due Game Starting Date: ".$game->{'title'};
    $message = "Your game ".$game->{'title'}." has a starting date that is now ".$game->{'days_past'}." days past the current date.  Please change your starting date or the game will no longer be visible in the signup list in 7 days.  You will still be able to access it from your personal page.\n\n  http://cassandrawerewolf.com/game/".$game->{'thread_id'};
	$sth_flag = $dbh->"update Games set expired=1 where id=?";
	$sth_flag->execute($game->{'id'});
  } else {
    $subject = "Past Due Game Removal: ".$game->{'title'};
    $message = "Your game ".$game->{'title'}." has an old start date and you have not updated it since your last notice.  The game is now being removed from the sign-up list.  You can still view the game on your personal page.";
	$sth_status = $dbh->"update Games set expired=0, status='Unknown' where game_id=?");
	$sth_status->execute($game->{'id'});
  }
  system("/var/www/html/bgg/send_geekmail.pl \"$bgg_user\" \"$bgg_pswd\" \"$to\" \"$subject\" \"$message\"");
}

