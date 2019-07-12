#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;
use HTTP::Cookies;

my $mech;
my $player;
my $password;
my $file;
my $usage="\nUsage $0 player password file\n\n";

die $usage unless ($#ARGV == 2);
$player = shift;
$password = shift;
$file = shift;

my $cookie_jar = HTTP::Cookies->new(
    file => $file, 
    autosave => 1,
  );

$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => $cookie_jar);
$mech->get('http://boardgamegeek.com/login');
$mech->form_number(0);
$mech->field('username',$player);
$mech->field('password',$password);
$mech->submit;

exit;
