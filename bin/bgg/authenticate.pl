#!/usr/bin/perl -w

# load modules
use strict;
use LWP::UserAgent;
use HTTP::Cookies;

my $usage="\nUsage $0 username password\n\n";
my $url='https://boardgamegeek.com/login/api/v1';

# parse arguments
die $usage unless ($#ARGV >= 1);
my $username = shift;
my $password = shift;

# CREATE USER AGENT
my $agent = LWP::UserAgent->new(
#    agent => 'CassandraWerewolf/1.0 https://cassandrawerewolf.com',
    agent => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36',
    cookie_jar => {}
);

# compose the request
my $json = "{
    \"credentials\": {
        \"username\": \"$username\",
        \"password\": \"$password\"
    }
}";

# send request
my $request = HTTP::Request->new('POST', $url);
$request->header(
    'Content-Type' => 'application/json',
    'Referer' => 'https://boardgamegeek.com/'
);
$request->content($json);
$agent->request($request);

# retrieve sessionId from cookie jar
print ($agent->cookie_jar->as_string =~ /SessionID=(\w+)/);

exit;
