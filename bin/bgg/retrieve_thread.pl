#!/usr/bin/perl -w

# load modules
use strict;
use LWP::UserAgent;

my $usage="\nUsage $0 article_id\n\n";

# parse arguments
die $usage unless ($#ARGV == 0);
my $article_id = shift;

my $url = "https://api.geekdo.com/api/articles/$article_id";

# CREATE USER AGENT
my $agent = LWP::UserAgent->new(
    agent => 'CassandraWerewolf/1.0 https://cassandrawerewolf.com',
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
