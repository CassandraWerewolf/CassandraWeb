#!/usr/bin/perl -w

use strict;
use LWP::UserAgent;

my $from;
my $passwd;
my $to;
my $subject;
my $body;
my %message;

my $login_url='https://boardgamegeek.com/login/api/v1';
my $mail_url='https://boardgamegeek.com/geekmail_controller.php';

die "\nUsage $0 from passwd to \"subject\" \"body\"\n\n" unless ($#ARGV == 4);
$from = shift;
$passwd = shift;
$to = shift;
$subject = shift;
$body = shift;

my $agent = LWP::UserAgent->new(
    agent => 'CassandraWerewolf/1.0 https://cassandrawerewolf.com',
    cookie_jar => {}
);

$message{action}='save';
$message{B1}='Send';
$message{touser}=$to;
$message{subject}=$subject;
$message{body}=$body;

# authenticate
my $geekauth = `/opt/werewolf/bgg/authenticate.pl "$from" $passwd`;

$agent->post($mail_url, \%message, Referer => 'https://boardgamegeek.com/geekmail', Authorization => "GeekAuth $geekauth");

exit;
