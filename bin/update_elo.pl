#!/usr/bin/perl -w

use DBI;
use strict;
use Switch;

sub get_new_elo($$$$);
sub get_k($$$);

my $dbh;
my $sth_good;
my $sth_evil;
my $game_id;
my $data_good;
my $data_evil;
my %good;
my %evil;
my $player;
my $g; 
my $e;
my $k;
my $elo;
my $elo_total;
my $team_evil_diff;
my $team_good_diff;
my $sth_elo_good;
my $sth_elo_evil;
my $sth_elo_total;
my $sth_all;
my $sth_count;

die "\nUsage: $0 game_id\n\n" unless ($#ARGV == 0);
$game_id = shift;

$dbh = DBI->connect("DBI:mysql:database=".$ENV{MYSQL_DB}.";host=localhost;port=3306", $ENV{MYSQL_USER}, $ENV{MYSQL_PWD});

$sth_good = $dbh->prepare("select p.user_id, p.result, u.elo_good from Players_result p, Users u where p.game_id=? and p.user_id = u.id and p.user_id = p.original_id and p.side='Good';");
$sth_good->execute($game_id);
$data_good = $sth_good->fetchall_arrayref;

$sth_evil = $dbh->prepare("select p.user_id, p.result, u.elo_evil from Players_result p, Users u where p.game_id=? and p.user_id = u.id and p.user_id = p.original_id and p.side='Evil';");
$sth_evil->execute($game_id);
$data_evil = $sth_evil->fetchall_arrayref;

$sth_elo_good = $dbh->prepare("update Users set elo_good = ? where id = ?;");
$sth_elo_evil = $dbh->prepare("update Users set elo_evil = ? where id = ?;");
$sth_elo_total = $dbh->prepare("update Users set elo_total = ? where id = ?;");

$sth_all = $dbh->prepare("select p.user_id, if(p.result='Won',1,if(p.result='Lost',0,-1)) as result, p.side, u.elo_good, u.elo_evil, u.elo_total from Players_result p, Users u where p.game_id=? and p.user_id = u.id and p.user_id = p.original_id;");
$sth_count = $dbh->prepare("select count(*) from Players_result where user_id = ? and game_id <=? and side like ?;");

$team_evil_diff = 0;
$team_good_diff = 0;
foreach $g (@{$data_good}) {
	$good{user_id}=$g->[0];
	$good{result}=$g->[1];
	$good{elo}=$g->[2];

	foreach $e (@{$data_evil}) {
		$evil{user_id}=$e->[0];
		$evil{result}=$e->[1];
		$evil{elo}=$e->[2];

		$team_good_diff += $evil{elo} - $good{elo};
		$team_evil_diff += $good{elo} - $evil{elo};
	}
}
$team_good_diff = $team_good_diff / $sth_evil->rows;
$team_evil_diff = $team_evil_diff / $sth_good->rows;

$sth_all->execute($game_id);
while($player = $sth_all->fetchrow_hashref() )
{
	if($player->{side} eq "Unknown" or $player->{result} eq "Unkown") {
		next;
	}
	if($player->{side} eq "Good") {
		$k = get_k($player->{user_id},$game_id,"Good"); 
		$elo = get_new_elo($player->{elo_good}, $team_good_diff, $player->{result}, $k); 

		$k = get_k($player->{user_id},$game_id,"%"); 
		$elo_total = get_new_elo($player->{elo_total}, $team_good_diff, $player->{result}, $k); 
		$sth_elo_good->execute($elo,$player->{user_id});
	} else {
		$k = get_k($player->{user_id},$game_id,"Evil"); 
		$elo = get_new_elo($player->{elo_evil}, $team_evil_diff, $player->{result}, $k);
		$k = get_k($player->{user_id},$game_id,"%"); 
		$elo_total = get_new_elo($player->{elo_total}, $team_evil_diff, $player->{result}, $k); 
		$sth_elo_evil->execute($elo,$player->{user_id});
	}
	$sth_elo_total->execute($elo_total,$player->{user_id});
}
	
$sth_good->finish;
$sth_evil->finish;
$sth_elo_good->finish;
$sth_elo_evil->finish;
$sth_all->finish;
$sth_count->finish;
exit;

sub get_k($$$) {
	my $user_id = shift;
	my $game_id = shift;
	my $side = shift;
	my $k;
	my $num;

	$sth_count->execute($user_id, $game_id, $side);
	$sth_count->bind_col(1, \$num);
	$sth_count->fetch;

	switch ($num) {
		case {$num <= 10} { $k = 32;}
		case {$num <= 25} { $k = 16;}
		case {$num <= 50} { $k = 10;}
		else 			  { $k = 5; }
	}
	
	return($k);
}

sub get_new_elo($$$$) {
	my $current_elo = shift;
	my $diff = shift;
	my $result = shift;
	my $k = shift;	
	my $new_elo;

	$new_elo = $current_elo + $k * ( $result - ( 1 / ( 1 + 10 ** (( $diff ) / 400))));
	return($new_elo);
}

