#!/usr/bin/perl -w

# load modules
use WWW::Mechanize;
use strict;

my $username;
my $mech;
my $url; 
my $content;
my $bgg = 'http://boardgamegeek.com';

die "\nUsage $0 username\n\n" unless ($#ARGV == 0);
$username = shift;

$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {}, timeout=> 300);

# get first page
$url = "http://boardgamegeek.com/user/$username";
$mech->get($url);

$content =  $mech->content();

if($content =~ /User Profile for (.*)/)
{
	my $name = $1;
	$name =~ s/\s*$//;
	print $name;
}	

exit;
