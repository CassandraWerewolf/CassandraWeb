#!/usr/bin/perl -w

use DBI;
use String::Trigram;
use XML::LibXML;
use strict;

my $dbh;
my $sth;
my $game_id;
my $last_article_id;
my $article_id;
my $user_id;
my $text;
my $cleaner;
my $num_edits;
my $row;
my $user_name;
my $user_alias;
my $game_use_alias;
my $game_allow_nightfall;
my $game_allow_nolynch;
my $alias;
my @player_list;
my $players_sth;
my $moderators_sth;
my %moderators;
my $voter;
my $votee;
my $ngram;
my $user;
my $file;
my %short;
my %long;
my $abb;
my $real;
my %ids;
my %names;
my $edit_flag;
my $tree;
my $root;
my @div;
my $cleaned;
my $game_sth;
my $allplayers_sth;

sub get_match($);
sub get_action($$$);

die "\nUsage: $0 game_id last_article_id short_file\n\n" unless ($#ARGV == 2);
$game_id = shift;
$last_article_id = shift;
$file = shift;

if(!defined($last_article_id) or $last_article_id !~ /\d+/)
{
	$last_article_id = 0;
}

die "Cannot open $file\n" unless open(FILE, $file);
while(<FILE>)
{
	chomp;
	($abb, $real) = split(/,/);
	$abb = lc($abb);
	$real = lc($real);
	$short{$real} = $abb;
	$long{$abb} = $real;
}
close(FILE);

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

$sth = $dbh->prepare("select Posts.article_id, Posts.user_id, Posts.text, Posts.num_edits from Posts where Posts.game_id = ? and Posts.article_id > ? and ( (Posts.user_id in (select user_id from Players_r where Players_r.game_id = Posts.game_id)) OR (Posts.user_id in (select user_id from Moderators where Moderators.game_id = Posts.game_id)) OR Posts.user_id = 306) order by Posts.article_id;");

#$players_sth = $dbh->prepare("select Users.name, Users.id from Players_all, Users where Players_all.game_id = ? and Players_all.user_id = Users.id;");
#$players_sth = $dbh->prepare("select Users.name, Users.id from Players_r, Users where Players_r.game_id = ? and Players_r.user_id = Users.id;");
$players_sth = $dbh->prepare("select LCASE(u.name), u.id, p.player_alias from Players_r p, Users u where p.game_id = ? and ( (p.death_phase is null or p.death_phase ='') and (p.death_day is null or p.death_day ='') ) and p.user_id = u.id ");

$allplayers_sth = $dbh->prepare("select LCASE(name), id from Users u");

$moderators_sth = $dbh->prepare("select LCASE(Users.name), Users.id from Moderators, Users where Moderators.game_id = ? and Moderators.user_id = Users.id;");

$game_sth = $dbh->prepare("select vote_by_alias, allow_nightfall, allow_nolynch from Games where id= ? ;");

$moderators_sth->execute($game_id);
$moderators_sth->bind_columns(\$user_name, \$user_id);
while($moderators_sth->fetch)
{
	$moderators{$user_id} = $user_name;
}
$moderators_sth->finish;
$moderators{306} = "cassandra project";

$game_sth->execute($game_id);
$game_sth->bind_columns(\$game_use_alias, \$game_allow_nightfall, \$game_allow_nolynch);
$game_sth->fetch;

$players_sth->execute($game_id);
$players_sth->bind_columns(\$user_name, \$user_id, \$user_alias);
while($players_sth->fetch)
{
	$ids{$user_name} = $user_id;
	$names{$user_id} = $user_name;
	push(@player_list, $user_name);
	if ($user_alias && $game_use_alias && ($game_use_alias eq "Yes"))
	{
	 	$ids{$user_alias} = $user_id;
	 	push(@player_list, $user_alias);
    }
	if($short{$user_name})
	{
		push(@player_list, $short{$user_name});
	}
}

$game_sth->finish;
$players_sth->finish;
push(@player_list, "nightfall");
push(@player_list, "all");
push(@player_list, "no lynch");

if ($game_id == 3147)
{
    $allplayers_sth->execute();
	$allplayers_sth->bind_columns(\$user_name, \$user_id);
	while($allplayers_sth->fetch)
	{
		if (!$names{$user_id}) {
			$ids{$user_name} = $user_id;
			$names{$user_id} = $user_name;
			push(@player_list, $user_name);
		}
	}
}

$ngram = new String::Trigram(cmpBase => \@player_list,
	ngram => 3, warp => 1.5, minSim => .25);

my $parser = XML::LibXML->new();
$parser->recover(2);

$sth->execute($game_id, $last_article_id);
$sth->bind_columns(\$article_id, \$user_id, \$text, \$num_edits);
while($row = $sth->fetch)
{
	# see if the post was edited
	if($num_edits > 0)
	{
		$edit_flag = 1;
	}
	else
	{
		$edit_flag = 0;
	}


	#print "-----\n$article_id: $text\n----------\n";

	# filter out all the quotes
	$tree = $parser->parse_html_string($text);
	$root = $tree->getDocumentElement;
	@div = $root->getElementsByTagName('div');
	foreach my $node (@div) {
    	#if(defined($node->getAttribute('class')) &&
			#$node->getAttribute('class') =~ /^quote$/i) {
        	$node->unbindNode();
    	#}
	}
	$text = $tree->toString();

	# find any moderator actions
	if($moderators{$user_id})
	{
		if($text !~ /The Cassandra Automatic Vote Tally System/)
		{
			get_action("dusk", $text, $edit_flag);
			get_action("dawn", $text, $edit_flag);
			get_action("killed", $text, $edit_flag);
            get_action("tally", $text, $edit_flag);
		}

		next;
	}

	if($names{$user_id})
	{
	 	get_action("unvote", $text, $edit_flag);
		get_action("vote", $text, $edit_flag);
        get_action("tally", $text, $edit_flag);
	}
}

$sth->finish;

exit;

sub get_match($)
{
	my $name = shift;

	my @matches;

	$ngram->getBestMatch($name, \@matches);

	if($#matches == 0)
	{
		if($long{$matches[0]})
		{
			return($long{$matches[0]});
		}
		else
		{
			return($matches[0]);
		}
	}
	else
	{
		return(undef)
	}
}

# find votes like [b][vote bob][/b]
sub get_action($$$)
{
	my $type = shift;
	my $data = shift;
	my $edited = shift;
	my $votee;
	my $name;
	my $valid;

	$valid = ($edited) ? 0 : 1;

	while($data =~ /\<b\>.*?\[\W*?$type\W*?(.*?)\].*?\<\/b\>/i)
	{
		$votee = lc($1);
		$type = lc($type);
		$votee  =~ s/\<img src=.*\/(.*?)\.gif.*?\>/$1/i;
        $name =  lc($votee);

		if($type eq "dawn" or $type eq "dusk" or $type eq "tally")
		{
			print "$article_id,$user_id,,$type,,1,$edited\n";
		}
		elsif($type eq "vote" or $type eq "unvote" or $type eq "killed")
		{
			if($ids{$votee} or $votee = get_match($votee))
			{
				if($type eq "vote" and $votee eq "nightfall")
				{
					if ($game_allow_nightfall eq "Yes") {
						print "$article_id,$user_id,,$type,nightfall,$valid,$edited\n";
					} else {
						print "$article_id,$user_id,,$type,nightfall is invalid,0,$edited\n";
					}
				}
				elsif($type eq "vote" and $votee eq "no kill")
				{
					if ($game_allow_nolynch eq "Yes") {
						print "$article_id,$user_id,,$type,No Kill,$valid,$edited\n";
					} else {
						print "$article_id,$user_id,,$type,No Kill is invalid,0,$edited\n";
					}
				}
				elsif($type eq "unvote" and $votee eq "all")
				{
					print "$article_id,$user_id,,$type,all,$valid,$edited\n";
				}
				elsif($type eq "unvote" and $votee eq "no kill")
				{
					print "$article_id,$user_id,,$type,No Kill,$valid,$edited\n";
				}
				elsif($type eq "vote"  and $votee eq "all")
				{
					#do nothing as this is an invalid vote
			    	print "$article_id,$user_id,,$type,unknown:$name,0,$edited\n";
				}
				elsif($type eq "unvote" and $votee eq "nightfall")
				{
					#do nothing as this is an invalid unvote
			    	print "$article_id,$user_id,,$type,invalid unvote,0,$edited\n";
				}
				else
				{
					print "$article_id,$user_id,$ids{$votee},$type,,$valid,$edited\n";
					# remove the killed players so they can't vote anymore or be voted upon
					if($type eq "killed")
					{
						$names{$ids{$votee}} = undef;
					}
				}
			}
			else
			{
			    print "$article_id,$user_id,,$type,unknown:$name,0,$edited\n";
			}
		}

		$data =~ s/\<b\>.*?\[\W*?$type\W*?.*?\]/\<b\>/i;
	}
}
