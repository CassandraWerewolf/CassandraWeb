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
my $url='http://boardgamegeek.com/newmessage.php3'; 

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

# get geekmail submit page
$mech->get($url);
$mech->form_name('MESSAGEFORM');
$mech->set_fields('body'=>$body, 'touser'=>$to, 'subject'=>$subject);
$mech->click_button(number => 2);

exit;
