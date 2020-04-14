#!/usr/local/bin/perl -w

use DBI;
use String::Trigram;
use XML::LibXML;
use strict;

my $pat;
$pat = "<img src=\"http://geekdo-images.com/images/goo.gif\" alt=\"goo\" border=\"0\">";
$pat  =~ s/\<img src=.*\/(.*?)\.gif.*?\>/$1/;
print $pat;
print "\n";

