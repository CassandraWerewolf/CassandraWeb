#!/usr/bin/perl -w

# load modules
use strict;
use LWP::UserAgent;
use JSON;

my $usage="\nUsage $0 geekauth id \"body\"\n\n";
my $url='https://api.geekdo.com/api/articles';

# parse arguments
die $usage unless ($#ARGV >= 2);
my $geekauth = shift;
my $id = shift;
my $body = shift;
$body =~ s/\\\'/\'/g;

# CREATE USER AGENT 
my $agent = LWP::UserAgent->new(cookie_jar => {});

# compose the request
my $header = [
    'Referer' => 'https://boardgamegeek.com/', 
    'Content-Type' => 'application/json', 
    'Authorization' => "GeekAuth $geekauth"
];

my %data;
$data{rollsEnabled}=0;
$data{body}=$body;

# send request
my $request = HTTP::Request->new('PATCH', "$url/$id", $header, encode_json(\%data));
my $response = $agent->request($request);
print $response->content;

exit;