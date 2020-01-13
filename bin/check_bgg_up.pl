#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $mech;
my $player;
my $password;
my $usage="\nUsage $0 player password\n\n";

die $usage unless ($#ARGV >= 1);
$player = shift;
$password = shift;

$mech = WWW::Mechanize->new(autocheck => 1, quiet => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
if(!$mech->success()) {
	print "ERROR: cannot retrieve login page\n";
	exit(1);
}

if($mech->form_name('the_form')) {
	$mech->field('username',$player);
	$mech->field('password',$password);
	$mech->submit;
} else {
	print "ERROR: cannot find login form\n";
	exit(2);
}

print "BGG is up\n";
exit;
