<?php
session_start();

//Test to see if idenity of visitor can be determined.
#print "Session: ".$_SESSION['uid']."<br />";
#print "Cookie: ".$_COOKIE['cassy_uid']."<br />";
#print "Login: ".$_REQUEST['login']."<br />";

if ( isset($_SESSION['uid']) || isset($_COOKIE['cassy_uid']) ||isset( $_REQUEST['login']) ) {
  include_once("php/accesscontrol.php");
}

require_once 'setup.php';
require_once('Cache/Lite.php');
include_once("menu.php");

// CONTROLLER
require_once('src/Games/Games.php');
$games = new Games();

$games_stats = $games->get_stats();
$games_in_fast_progress = $games->get_games_in_fast_progress();
$games_in_standard_progress = $games->get_games_in_standard_progress();
$games_recently_ended = $games->get_games_recently_ended();
$games_in_fast_signup = $games->games_in_fast_signup();
$games_in_standard_signup_as_swf = $games->games_in_standard_signup_as_swf();
$games_in_standard_signup_as_date = $games->games_in_standard_signup_as_date();


// RENDER VIEW
require_once 'templates/shared/header.php'; 
if ($show_funding_message) {
    include_once 'templates/shared/_funding_message.php';
}
require_once 'templates/games/recent.php';
require_once 'templates/shared/footer.php'; 
?>

