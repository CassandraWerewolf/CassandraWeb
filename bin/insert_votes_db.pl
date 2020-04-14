#!/usr/bin/perl

die "\nUsage: $0 game_id\n\n" unless ($#ARGV == 0);
$game_id = shift;

use DBI;

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

$vote_sth = $dbh->prepare("insert into Votes(game_id, article_id, day, voter, votee, type, misc, valid, edited) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");

$game_set_sth = $dbh->prepare("update Games set phase = ?, day = ?, updated_tally = ? where id = ?;");
$reset_moves_sth = $dbh->prepare("update Players set phys_moves=0 where game_id = ?;");

$original_id_sth = $dbh->prepare("select original_id from Players_result where game_id= ? and user_id = ?;");
$loc_id_sth = $dbh->prepare("select loc_id from Players where game_id= ? and user_id = ?;");

$death_sth = $dbh->prepare("update Players set death_phase = ?, death_day = ?, loc_id=NULL where user_id = ? and game_id = ?;");
$death_items_sth = $dbh->prepare("update Items set owner_type='loc', owner_ref_id= ? where game_id= ? and owner_type='user' and owner_ref_id= ?;");
$death_corpse_sth = $dbh->prepare("insert into Items(game_id, template_id, name, owner_ref_id, owner_type, description, mobility, created) VALUES (?, 1, ?, ?, 'loc', ?, 'heavy', now());");
$p_name_sth = $dbh->prepare("select name from Users where id= ?;");

$target_sth = $dbh->prepare("update Game_orders set target_id = ? where game_id = ? and target_id = 0;");

$game_get_sth = $dbh->prepare("Select day, phase, phys_reset_moves from Games where id = ?;");

$game_get_sth->execute($game_id);
($day, $phase, $reset_moves) = $game_get_sth->fetchrow_array();

while ( $line = <> )
{
	chomp($line);
	($article_id, $voter, $votee, $type, $misc, $valid, $edited) = split( /,/, $line);

    if ($type eq $reset_moves)
    {
       $reset_moves_sth->execute($game_id);
    }

	if($type eq "dawn" and $phase eq "night")
	{
		$day++;
		$phase = "day";
		$game_set_sth->execute($phase, $day, 0, $game_id);
	}
	elsif($type eq "dusk")
	{
		$phase = "night";
		$game_set_sth->execute($phase, $day, 1, $game_id);
	}
	elsif($type eq "tally" and $phase eq "day")
	{
		$game_set_sth->execute($phase, $day, 1, $game_id);
	}
	elsif($type eq "killed")
	{
		if($phase eq "day")
		{
			$death_phase = "night";
			$death_day = $day - 1;
		}
		else
		{
			$death_phase = "day";
			$death_day = $day;

			$target_sth->execute($votee, $game_id);
		}

        # get the original id in case the killed player was a replacement, gets the same id if not
		$original_id_sth->execute($game_id,$votee);
		#($orig_id,$loc_id) = $original_id_sth->fetchrow_array();
		@orig_ids = $original_id_sth->fetchrow_array();
		$orig_id = $orig_ids[0];
        $loc_id_sth->execute($game_id,$orig_id);
        @loc_ids = $loc_id_sth->fetchrow_array();
        $loc_id = $loc_ids[0];

		$death_sth->execute($death_phase, $death_day, $orig_id, $game_id);
        if (defined $loc_id) {
          $p_name_sth->execute($votee);
          @names = $p_name_sth->fetchrow_array();
          $name = $names[0];
          $death_items_sth->execute($loc_id, $game_id, $orig_id);
          $death_corpse_sth->execute($game_id,"$name\'s corpse",$loc_id,"You are holding $name\'s corpse. You cannot take it with you.");
        }
	}
	elsif($phase eq "day" and ($type eq "vote" or $type eq "unvote"))
	{
		$vote_sth->execute($game_id, $article_id, $day, $voter, $votee, $type, $misc, $valid, $edited);
	}
}

$vote_sth->finish;
$game_set_sth->finish;
$game_get_sth->finish;

exit;
