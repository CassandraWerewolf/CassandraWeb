#!/usr/bin/perl -w

# load modules
use strict;
use LWP::UserAgent;
use HTTP::Cookies;

my $usage="\nUsage $0 username password\n\n";
my $url='https://boardgamegeek.com/login';

# parse arguments
die $usage unless ($#ARGV >= 1);
my $username = shift;
my $password = shift;

# CREATE USER AGENT 
my $agent = LWP::UserAgent->new(cookie_jar => {});

# compose the data
my %message;
$message{username}=$username;
$message{password}=$password;

# send data
$agent->post($url, \%message, Referer => 'https://boardgamegeek.com/login');

# retrieve sessionId from cookie jar
print ($agent->cookie_jar->as_string =~ /SessionID=(\w+)/);

exit;