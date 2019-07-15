<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";

dbConnect();

$game_id = $_GET['game_id'];
$from_url = $_GET['from'];

if ( $game_id == "" ) {
  error("Invalid URL request.");
}

# Make sure it is a Moderator who is accessing this page.
$sql = sprintf("select user_id from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($uid));
$request = mysql_query($sql);
if ( mysql_num_rows($request) != 1 ) {
  error("You must be the moderator of the game to access this page.");
}

?>
<html>
<head>
<title>Cassy Rest Page</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<h1>Cassy Reset Page</h1>
<p>This page is to be used ONLY if you have royaly screwed up the Cassy voting system by forgetting to post [dawn], [dusk], or your day count etc is way off and you don't know why. (or maybe you guys will find more ways to screw things up.)  Please do not use this for just minor easy to fix errors, like a player being recorded as a day kill instead of a night kill.</p>
<p>Before you press the "reset button below" Please make sure all YOUR post have been fixed to have the [dawn] and [dusk] and [killed] statements in the correct order.  If this is done, you can then click the reset button below.  You will then have to wait a little while for Cassy to do her thing.  You will know when she is finished becuse she will post an updated vote tally.</p>
<form method='post' action='<?=$_SERVER['PHP_SELF'];?>' >
<input type='hidden' name='game_id' value='<?=$game_id;?>' />
<input type='hidden' name='from' value='<?=$from_url;?>' />
<input type='submit' value='Reset Cassy' />  <input type='button' value='Cancel' onClick='javascript:window.history.back();' />
</form>
</body>
</html>



