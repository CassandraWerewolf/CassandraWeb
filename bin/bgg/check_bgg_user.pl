#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $url='https://boardgamegeek.com/user'; 

die "\nUsage $0 user\n\n" unless ($#ARGV == 0);
my $user = shift;

$url = "$url/$user";

my $mech = WWW::Mechanize->new(agent => 'CassandraWerewolf/1.0 https://cassandrawerewolf.com', autocheck => 1);
$mech->get($url);

if($mech->content() =~ /Error: User does not exist./g) {
    print "false";
} else {
    print "true";
}

exit;
