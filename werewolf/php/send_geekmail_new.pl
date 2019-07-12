#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $mech;
my $from;
my $passwd;
my $to;
my $subject;
my $body;
my $html;
my $url='http://boardgamegeek.com/geekmail'; 

die "\nUsage $0 from passwd to \"subject\" \"body\"\n\n" unless ($#ARGV == 4);
$from = shift;
$passwd = shift;
$to = shift;
$subject = shift;
$body = shift;

$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
$mech->form_name('the_form');
$mech->field('username',$from);
$mech->field('password', $passwd);
$mech->submit;

# get geekmail page
$mech->get($url);
$mech->follow_link(text => "Compose GeekMail");
print $mech->content();


exit;
