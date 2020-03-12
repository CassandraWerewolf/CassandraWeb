#!/usr/bin/perl -w

# load modules
use strict;
use LWP::UserAgent;
use HTTP::Cookies;

# set variables
my $agent;
my %message;
my $username;
my $password;
my $usage="\nUsage $0 username password\n\n";
my $url='https://boardgamegeek.com/login';

# parse arguments
die $usage unless ($#ARGV >= 1);
$username = shift;
$password = shift;

# CREATE USER AGENT 
$agent = LWP::UserAgent->new(cookie_jar => {});

# compose the data
$message{username}=$username;
$message{password}=$password;

# send data
$agent->post($url, \%message, Referer => 'https://boardgamegeek.com/login');

# retrieve sessionId from cookie jar
print ($agent->cookie_jar->as_string =~ /SessionID=(\w+)/);

exit;