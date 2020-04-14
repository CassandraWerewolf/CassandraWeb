#!/usr/bin/perl -w

use DBI;
use String::Trigram;
use strict;

my $dbh;
my $sth;
my $game_id;
my $last_article_id;
my $article_id;
my $user_id;
my $text;
my $row;
my $edit_flag;

sub get_action($$$);

die "\nUsage: $0 game_id last_article_id\n\n" unless ($#ARGV == 1);
$game_id = shift;
$last_article_id = shift;

if(!defined($last_article_id) or $last_article_id !~ /\d+/)
{
	$last_article_id = 0;
}

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

$sth = $dbh->prepare("select Posts.article_id, Posts.user_id, Posts.text from Posts where Posts.game_id = ? and Posts.article_id > ? and Posts.user_id in (select user_id from Moderators where Moderators.game_id = Posts.game_id) order by Posts.article_id;");

$sth->execute($game_id, $last_article_id);
$sth->bind_columns(\$article_id, \$user_id, \$text);
while($row = $sth->fetch)
{
	# see if the post was edited
	if($text =~ /\<div class\=\'smallfont\'\>\<br\>\<br\>Last edited/)
	{
		$edit_flag = 1;
	}
	else
	{
		$edit_flag = 0;
	}

	#print "-----\n$article_id: $text\n----------\n";

	# delete the quotes incase they contain votes
	$text =~ s/\<div class\=\'quote\'\>.*\<\/div\>\s*\<BR\>//g;

	# get moderator actions
	get_action("dawn", $text, $edit_flag);
	get_action("dusk", $text, $edit_flag);
}

$sth->finish;

exit;

# find votes like [b][vote bob][/b]
sub get_action($$$)
{
	my $type = shift;
	my $data = shift;
	my $edited = shift;

	my $arg;
	my $valid;

	$valid = ($edited) ? 0 : 1;

	while($data =~ /\<b\>.*?\[\W*?$type\W*?(.*?)\]/i)
	{
		$arg = $1;
		if($type eq "dawn" or $type eq "dusk")
		{
			print "$article_id,$user_id,,$type,$arg,1,$edited\n";
		}

		$data =~ s/\<b\>.*?\[\W*?$type\W*?.*?\]/\<b\>/i;
	}
}

