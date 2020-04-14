#!/usr/bin/perl -w

use strict;
use WWW::Mechanize;

my $article_id;
my $mech;
my $url;
my $content;
my @lines;
my $line;

die "\nUsage $0 article_id\n\n" unless ($#ARGV == 0);
$article_id = shift;

$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {}, timeout=> 300);
#$mech->get('http://boardgamegeek.com/login');
#$mech->form_name('the_form');
#$mech->field('username','Cassandra Project');
#$mech->field('password', $ENV{'BGG_PASSWORD'});
#$mech->submit;

# get first page
$url = "http://bgg.geekdo.com/geekrecommend.php?action=recspy&itemid=$article_id&itemtype=article";
$mech->get($url);
$content =  $mech->content();
$content =~ s/\|/\n/g;
@lines = split(/\n/, $content);

while($line = pop(@lines)){
	if($line =~ /\/user\/(.+?)"/){
		print "$1\n";
	}
}

exit;
