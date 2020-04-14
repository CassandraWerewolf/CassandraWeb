#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $mech;
my $player;
my $password;
my $action; # Must be reply, edit, or new
my $id;
my $url; 
my $body;
my $resp;
my $page;
my $subject;
my $time;
my $usage="\nUsage $0 player password [reply, edit or new] id \"body\" \"[subject]\"\n\n";

die $usage unless ($#ARGV >= 4);
$player = shift;
$password = shift;
$action = shift;
$id = shift;
$body = shift;
$body =~ s/\\\'/\'/g;
$subject = shift;
if (defined($subject)) {$subject =~ s/\\\'/\'/g;}

if ( $action eq "reply" ) {
$url = "http://boardgamegeek.com/thread/$id";
} elsif ( $action eq "edit" ) {
$url = "http://boardgamegeek.com/article/edit/$id";
} elsif ( $action eq "new" ) {
$url = "http://boardgamegeek.com/article/create/region/1/$id";
}
else{
die $usage;
}

$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
$mech->form_number(1);
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
if (defined($subject)) {
  $mech->set_fields('subject' => $subject);
}
$resp = $mech->click_button(number => 2);
$resp->base =~ /article\/(\d+)\#/;
print $1;

exit;
