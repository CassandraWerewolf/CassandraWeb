#!/usr/bin/perl -w

# load modules
use strict;
use WWW::Mechanize;

my $mech;
my $player;
my $password;
my $action; # Must be Reply or Edit
my $id;
my $url; 
my $body;
my $resp;
my $page;
my $subject="";
my $usage="\nUsage $0 player password [reply, edit or new] id \"body\" \"[subject]\"\n\n";

die $usage unless ($#ARGV >= 4);
$player = shift;
$password = shift;
$action = shift;
$id = shift;
$body = shift;
$body =~ s/\\\'/\'/g;
if ( $#ARGV == 1 ) {$subject = shift; }
if (defined($subject)) {$subject =~ s/\\\'/\'/g;}

if ( $action eq "reply" ) {
$url = "http://boardgamegeek.com/thread/$id";
} elsif ( $action eq "edit" ) {
$url = "http://boardgamegeek.com/geekforum.php3?action=edit&articleid=$id";
} elsif ( $action eq "new" ) {
$url = "http://boardgamegeek.com/geekforum.php3?action=newpost&forumid=$id&objectid=1&objecttype=forum";
}
else{
die $usage;
}

$mech = WWW::Mechanize->new(autocheck => 1, quiet => 1, cookie_jar => {});
$mech->get('http://boardgamegeek.com/login');
if(!$mech->success()) {
	print "ERROR: cannot retrieve login page\n";
	exit(1);
}

if($mech->form_name('the_form')) {
	$mech->field('username',$player);
	$mech->field('password',$password);
	$mech->submit;
} else {
	print "ERROR: cannot find login form\n";
	exit(2);
}

# get the article id of the first post in the thread to sub in for the 
# reply to article_id
if($action eq "reply")
{
	$mech->get($url);
	if(!$mech->success()) {
		print "ERROR: cannot get article id of first post\n";
		exit(3);
	}

	$page = $mech->content;
	$page =~ /replyto_id=(\d+)\"/;
	$url = "http://boardgamegeek.com/geekforum.php3?action=reply&threadid=$id&replyto_id=$1";
}

# get post submit page
$mech->get($url);
if(!$mech->success()) {
	print "ERROR: cannot retrieve posting page\n";
	exit(4);
}
#$mech->form_number(2);
if(!$mech->form_name('MESSAGEFORM'))
{
	print "ERROR: cannot find posting form\n";
	exit(5);
}
$mech->set_fields('body' => $body);
if (defined($subject)) {
  $mech->set_fields('subject' => $subject);
}
$resp = $mech->click_button(number => 2);
if(!$mech->success()) {
	print "ERROR: cannot click submit button\n";
	exit(6);
}

if(!$resp->is_success) {
	print "ERROR: unable to get posting confirmation page\n";
	exit(7);
}

$page = $resp->content;  

$page =~ /Click \<A href\=\"\/article\/(\d+)\#\d+\"\>Here\<\/A\> to View Your Message/;
if(!defined($1)) {
	print "ERROR: unable to get post id\n";
	exit(8);
}

print $1;

exit;
