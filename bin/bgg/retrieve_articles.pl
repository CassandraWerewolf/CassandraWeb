#!/usr/bin/perl -w

# load modules
use strict;
use LWP::UserAgent;

my $usage="\nUsage $0 thread_id\n\n";

# parse arguments
die $usage unless ($#ARGV == 0);
my $thread_id = shift;

my $url = "https://api.geekdo.com/api/articles?threadid=$thread_id";

# CREATE USER AGENT
my $agent = LWP::UserAgent->new(
    # agent => 'CassandraWerewolf/1.0 https://cassandrawerewolf.com',
    agent => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36',
    cookie_jar => {}
);

# send request
my $response = $agent->get(
    $url,
    Referer => 'https://boardgamegeek.com/'
);

# retrieve thread json
print $response->content;


exit;
