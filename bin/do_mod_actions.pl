#!/usr/bin/perl

die "\nUsage: $0 game_id\n\n" unless ($#ARGV == 0);
$game_id = shift;

use DBI;

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

$game_set_sth = $dbh->prepare("update Games set phase = ?, day = ? where id = ?;");

$game_get_sth = $dbh->prepare("Select day, phase from Games where id = ?;");

$game_get_sth->execute($game_id);
($day, $phase) = $game_get_sth->fetchrow_array();

while ( $line = <> )
{
	chomp($line);
	($article_id, $voter, $votee, $type, $misc, $valid, $edited) = split( /,/, $line);

	if($type eq "dawn")
	{
		$day++;
		$phase = "day";
		$game_set_sth->execute($phase, $day, $game_id);
	}
	elsif($type eq "dusk")
	{
		$phase = "night";
		$game_set_sth->execute($phase, $day, $game_id);
	}
}

$game_set_sth->finish;
$game_get_sth->finish;

exit;
