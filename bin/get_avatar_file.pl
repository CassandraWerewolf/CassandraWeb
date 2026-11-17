#!/usr/bin/perl -w

# load modules
use WWW::Mechanize;
use strict;

my $username;
my $mech;
my $url; 
my $content;
my $bgg = 'https://boardgamegeek.com';

die "\nUsage $0 username\n\n" unless ($#ARGV == 0);
$username = shift;

$mech = WWW::Mechanize->new(agent => 'CassandraWerewolf/1.0 https://cassandrawerewolf.com', autocheck => 1, cookie_jar => {}, timeout=> 300);

# get first page
$url = "https://boardgamegeek.com/user/$username";
$mech->get($url);

$content =  $mech->content();

if($content =~ /\/avatars\/(avatar_\d+\.jpg)/)
{
	#print $1;
	$url = "http://files.boardgamegeek.com/avatars/$1";
	$mech->get($url);
	print $mech->content();
}	

exit;
