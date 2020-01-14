#!/usr/bin/perl -w

# This script will be used to read a thread parsed file and put it into the database.

use DBI;

$last_article = shift;
if(!defined($last_article) or $last_article !~ /\d+/)
{
	$last_article = 0;
}

$d = chr(167);

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

$user_sth = $dbh->prepare("select id from Users where name=?;");
$game_sth = $dbh->prepare("select id from Games where thread_id=?;");
$post_sth = $dbh->prepare("insert into Posts (article_id, game_id, user_id, time_stamp, text, page) VALUES (?, ?, ?, ?, ?, ?);");

open (ERROR, ">>/opt/werewolf/db_errors.txt") or die ("Can't open db_errors.txt");

while ( $line = <> )
{
	($name, $thread_id, $article_id, $date_time, $page, $text) = split( /$d/, $line);
	if($article_id > $last_article)
	{
		$user_sth->execute($name);
		@user_id = $user_sth->fetchrow_array();
		$user_id = $user_id[0];

		$game_sth->execute($thread_id);
		@game_id = $game_sth->fetchrow_array();
		$game_id = $game_id[0];

		$result = $post_sth->execute($article_id, $game_id, $user_id, $date_time, $text, $page);
		if ( !$result )
		{
  			print ERROR $line;
		}
	}
}

$user_sth->finish;
$game_sth->finish;
$post_sth->finish;

close ERROR;
