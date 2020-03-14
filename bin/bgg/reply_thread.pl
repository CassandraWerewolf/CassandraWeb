#!/usr/bin/perl -w

# reply_thread.pl with geekauth

# load modules
use strict;
use LWP::UserAgent;

my $usage="\nUsage $0 geekauth thread_id article_id \"body\"\n\n";
my $url='https://api.geekdo.com/api/articles';

# parse arguments
die $usage unless ($#ARGV >= 2);
my $geekauth = shift;
my $thread_id = shift;
my $article_id = shift;
my $body = shift;
$body =~ s/\\\'/\'/g;

# CREATE USER AGENT 
my $agent = LWP::UserAgent->new(cookie_jar => {});

# compose the request
my %message;
$message{rollsEnabled}=0;
$message{threadid}=$thread_id;
$message{replytoid}=$article_id;
$message{body}=$body;

# send request
my $response = $agent->post(
    $url, \%message, Referer => 'https://boardgamegeek.com/', Authorization => "GeekAuth $geekauth"
);
print $response->content;

exit;