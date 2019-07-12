#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $mech;
my $player;
my $password;
my $id;
my $url; 
my $resp;
my $page;
my $usage="\nUsage $0 player password id \n\n";

die $usage unless ($#ARGV == 2);
$player = shift;
$password = shift;
$id = shift;

$url = "http://boardgamegeek.com/article/$id#$id";

$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
$mech->form_name('the_form');
$mech->field('username',$player);
$mech->field('password',$password);
$mech->submit;

# get the article id of the first post in the thread to sub in for the 
# reply to article_id
$mech->get($url);
$page = $mech->content;
$page =~ /\/thread\/(\d+)\"/;
print $1;

exit;
