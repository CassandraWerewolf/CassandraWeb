#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $usage="\nUsage $0 player password id \"body\" \"[subject]\"\n\n";

die $usage unless ($#ARGV >= 3);
my $player = shift;
my $password = shift;
my $forum_id = shift;
my $body = shift;
$body =~ s/\\\'/\'/g;
my $subject = shift;
if (defined($subject)) {$subject =~ s/\\\'/\'/g;}

my $url = "http://boardgamegeek.com/article/create/region/1/$forum_id";

my $mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
$mech->form_number(1);
$mech->field('username',$player);
$mech->field('password',$password);
$mech->submit;

# get post submit page
$mech->get($url);
#$mech->form_number(2);
$mech->form_name('MESSAGEFORM');
$mech->set_fields('body' => $body);
if (defined($subject)) {
  $mech->set_fields('subject' => $subject);
}

my $response = $mech->click_button(number => 2);
$response->base =~ /article\/(\d+)\#/;
print $1;

exit;
