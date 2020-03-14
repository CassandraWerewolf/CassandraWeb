#!/usr/bin/perl -w

# load modules
use strict;

my $usage="\nUsage $0 username password thread_id article_id\n\n";

# parse arguments
die $usage unless ($#ARGV >= 2);
my $username = shift;
my $password = shift;
my $thread_id = shift;
my $article_id = shift;

my @post = <>;
my $body = join('', @post);

# authenticate
my $geekauth = `/opt/werewolf/bgg/authenticate.pl $username $password`;

# reply to thread now that we have geekauth
print system(('/opt/werewolf/bgg/reply_thread.pl', $geekauth, $thread_id, $article_id, $body));

exit;