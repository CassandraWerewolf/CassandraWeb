#!/usr/bin/perl -w

use DBI;
use strict;

my $dbh;
my $sth;
my $sth_nonvoter;
my $sth_times;
my $game_id;
my $row;
my $text;
my $type;
my $lynch_time;
my $action_time;

die "\nUsage: $0 game_id\n\n" unless ($#ARGV == 0);
$game_id = shift;
$type = shift;

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

$sth = $dbh->prepare("select Get_tally(id,day,'inv','bgg') from Games where id = ?");
#$sth = $dbh->prepare("select concat(t.voter, ' - ',t.total,' - ',t.votes_bgg)as vote_row from Tally_display_inverted t, Games g where t.game_id=? and g.id = t.game_id and t.day=g.day;");
$sth_nonvoter = $dbh->prepare("select get_non_voters(id, day) from Games where id=?;");
$sth_times = $dbh->prepare("select TIME_FORMAT(lynch_time, '%l:%i %p BGG'), DATE_FORMAT(na_deadline, '%l:%i %p BGG') from Games where id = ?;");

print "g{[b]INVERTED TALLY[/b]\n\nVoter - # - Voted on\n\n";

$sth->execute($game_id);
$sth->bind_columns(\$text);
while($row = $sth->fetch)
{
	print "$text";
}

$sth_nonvoter->execute($game_id);
$sth_nonvoter->bind_columns(\$text);
$sth_nonvoter->fetch;
$text = ($text) ? $text : "N/A";
print "\nNot Voting: $text\n}g";

$sth->finish;
$sth_nonvoter->finish;
exit;
