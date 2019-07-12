#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $mech;
my $user;
my $url='http://boardgamegeek.com/user'; 

die "\nUsage $0 user\n\n" unless ($#ARGV == 0);
$user = shift;

$url = "$url/$user";

$mech = WWW::Mechanize->new(autocheck => 1);
$mech->get($url);

if($mech->content() =~ /User\: $user not found/g)
{
	print "false";
}
else
{
	print "true";
}	

exit;
