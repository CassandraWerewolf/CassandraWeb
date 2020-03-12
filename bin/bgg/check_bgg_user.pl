#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $url='http://boardgamegeek.com/user'; 

die "\nUsage $0 user\n\n" unless ($#ARGV == 0);
my $user = shift;

$url = "$url/$user";

my $mech = WWW::Mechanize->new(autocheck => 1);
$mech->get($url);

if($mech->content() =~ /Error: User does not exist./g) {
    print "false";
} else {
    print "true";
}

exit;
