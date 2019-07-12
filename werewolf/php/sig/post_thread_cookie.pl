#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;
use HTTP::Cookies;

my $mech;
my $player;
my $password;
my $action; # Must be reply, edit, or new
my $id;
my $url; 
my $body;
my $cookie;
my $resp;
my $page;
my $subject;
my $time;
my $usage="\nUsage $0 cookie [reply, edit or new] id \"body\" \"[subject]\"\n\n";

die $usage unless ($#ARGV >= 3);
$cookie = shift;
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


my $cookie_jar = HTTP::Cookies->new;
$cookie_jar->load($cookie);

$mech = WWW::Mechanize->new(autocheck => 1,onwarn => undef,onerror => undef, quiet => 1, cookie_jar => $cookie_jar);

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
