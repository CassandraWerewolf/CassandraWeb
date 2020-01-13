#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $mech;
my $player;
my $password;
my $action; # Must be Reply or Edit
my $id;
my $url; 
my $body;
my @post;
my $resp;
my $page;
my $usage="\nUsage $0 player password [reply or edit] id\n\n";

die $usage unless ($#ARGV == 3);
$player = shift;
$password = shift;
$action = shift;
$id = shift;

@post = <>;
$body = join('', @post);

if ( $action eq "reply" ) {
$url = "http://boardgamegeek.com/thread/$id";
} elsif ( $action eq "edit" ) {
$url = "http://boardgamegeek.com/geekforum.php3?action=edit&articleid=$id";
}
else{
die $usage;
}

$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
$mech->form_name('the_form');
$mech->field('username',$player);
$mech->field('password',$password);
$mech->submit;

# get the article id of the first post in the thread to sub in for the 
# reply to article_id
if($action eq "reply")
{
	$mech->get($url);
	$page = $mech->content;
	$page =~ /recommendstatus_article(\d+)\'/;
	$url = "http://boardgamegeek.com/article/reply/$1";
}

# get post submit page
$mech->get($url);
#$mech->form_number(2);
$mech->form_name('MESSAGEFORM');
$mech->set_fields('body' => $body);
$resp = $mech->click_button(number => 2);
$page = $resp->content;  

$page =~ /\/article\/(\d+)\#/;
print $1;

exit;
