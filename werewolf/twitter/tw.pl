#!/usr/local/bin/perl -w

use strict;
use Net::Twitter;

my $action;
my $name;
my $message = '';

die "\nUsage $0 action name [\"message\"]\n\n" unless ($#ARGV >=1);
$action = shift;
$name = shift;

if($#ARGV = 2) {
	$message = shift;
}

my $bot = Net::Twitter->new(username=>"cassandra", password=>getenv('BGG_PASSWORD') );

if(defined($message)) {
	$bot->update("$action $name $message");
} else {
	$bot->update("$action $name");
}
