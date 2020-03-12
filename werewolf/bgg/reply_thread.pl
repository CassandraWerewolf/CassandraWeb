#!/usr/bin/perl -w

# load modules
use strict;
use LWP::UserAgent;

# set variables
my $agent;
my %message;
my $geekauth;
my $id;
my $body;
my $response;
my $usage="\nUsage $0 geekauth id \"body\"\n\n";
my $url='https://api.geekdo.com/api/articles';

# parse arguments
die $usage unless ($#ARGV >= 2);
$geekauth = shift;
$id = shift;
$body = shift;
$body =~ s/\\\'/\'/g;

# CREATE USER AGENT 
$agent = LWP::UserAgent->new(cookie_jar => {});

# compose the data
$message{rollsEnabled}=0;
$message{threadid}=$id;
$message{replytoid}='34242817';
$message{body}=$body;

# send data
$response = $agent->post(
    $url, \%message, Referer => 'https://boardgamegeek.com/', Authorization => "GeekAuth $geekauth"
);
print $response->content;

exit;