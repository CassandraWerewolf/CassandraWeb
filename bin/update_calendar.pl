#!/usr/bin/perl

use DBI;
use WWW::Mechanize;

$bgg_user = "Cassandra Project";
$bgg_pswd = $ENV{'BGG_PASSWORD'};

$dbh = DBI->connect("DBI:mysql:database=$ENV{'MYSQL_DATABASE'};host=$ENV{'MYSQL_HOST'};port=3306",  $ENV{'MYSQL_USER'}, $ENV{'MYSQL_PASSWORD'});

# This code will be used to Update the Google Calendar as the Database needs it to be updated.

# Find out if the Calendar needs to be updated.

$sth_calendar = $dbh->prepare("select * from Update_calendar");
$sth_calendar->execute();
$num = $sth_calendar->rows();
if ( $num < 1) {
exit;
}

# Log into Cassy Web pages
$mech = WWW::Mechanize->new(autocheck => 1, cookie_jar => {});
$mech->get('http://cassandrawerewolf.com/index.php?login=true');
$mech->form_name('login_cassy');
$mech->set_fields('uname' => $bgg_user);
$mech->set_fields('pwd' => $bgg_pswd);
$mech->set_fields('remember' => 'on');
$mech->click_button(name => 'login');

# Update the Calendar using admin/update_calendar.php
$mech->get("http://cassandrawerewolf.com/admin/update_calendar.php");

exit;
