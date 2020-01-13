#!/usr/bin/perl -w

use strict;
use LWP::UserAgent;

my $agent;
my $from;
my $passwd;
my $to;
my $subject;
my $body;
my %login;
my %message;

my $login_url='https://boardgamegeek.com/login';
my $mail_url='https://boardgamegeek.com/geekmail_controller.php';

die "\nUsage $0 from passwd to \"subject\" \"body\"\n\n" unless ($#ARGV == 4);
$from = shift;
$passwd = shift;
$to = shift;
$subject = shift;
$body = shift;

$agent = LWP::UserAgent->new(cookie_jar => {});

$login{username}=$from;
$login{password}=$passwd;
$login{B1}='Submit';

$message{action}='save';
$message{B1}='Send';
$message{touser}=$to;
$message{subject}=$subject;
$message{body}=$body;

$agent->post($login_url, \%login);
$agent->post($mail_url, \%message);

exit;
