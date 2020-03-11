<?php // signup.php

include_once "../php/common.php";
include_once "../php/db.php";
include_once "../php/bgg.php";
include_once "../php/accesscontrol.php";

checkLevel($level,2);

if ( isset($_POST['request']) ) {
dbConnect();

$bggid = $_POST['bggid'];

// Removing requierement that user must already be in DB.  The will get a passwords as long as they have a BGG id.
$sql = "Select count(*) from Users where name='$bggid'";
$result = mysql_query($sql);
if ( !$result) {
  error ($sql);
  error('Database error # 100 occurred in proccessing your submission.\\nIf this error persists, please e-mail cassandra.project@gmail.com');
} 
if ( @mysql_result($result,0,0)==0) {
//  error('According to our database you have not played in an BGG werewolf games.  Please go join a game and then come back to gain access to all the cool stuff available here.');
$sql = "insert into Users set name='$bggid', password='none'";
$result = mysql_query($sql);
}
$newpass = substr(md5(time()),0,6);
$sql = "update Users set password = MD5('$newpass') where name='$bggid'";
if ( !mysql_query($sql) ) {
  error('Database error #200 occurred in processing your submission.\\nIf this error persists, please contact cassandra.project@gmail.com');
}
$to = $bggid;
$subject = "Cassandra Project Password";
$message = "Welcome to Cassandra Werewolf.\n\nYou can now log in and edit your data, and have access to some very cool, fun, and useful tools for playing BGG Werewolf.\n\nYour username is $bggid \n and your password is = $newpass\n\nPlease [url=https://cassandrawerewolf.com/password.php]login and change[/url] it to something easier to remember. Feel free to swing by [url=https://boardgamegeek.com/thread/1897478]this thread[/url] and say hi.\n\nEnjoy,\nMelsana, Pilotbob\n";
send_geekmail($to, $subject, $message);
?>
<html>
<head>
<title>Password Created</title>
</head>
<body>
<h3>Welcome to the Cassandra Werewolf Site</h3>
<p>A GeekMail has just been sent to you with your password.  If you don't get one please <a href='https://boardgamegeek.com/geekmail/compose?touser=Cassandra%20Project&subject=Cassandra%20New%20User%20Password%20Problems'>Click Here</a>
<p>Our “new player” liasons are TFang and nolemonplease. If you have any questions about BGG Werewolf, please feel free to reach out to them!
<p>To sign up for a game, go to the <a href='index.php'>Main Page</a>. There are three categories of games on the right side in tables: Fast games (run in less than 2 hours), Standard (scheduled to start on a certain date), and Standard- Starts When Full (moderators will launch these as soon as the player count is reached).
<p>We have a series of games intended for new players called “Hi, I want to play Werewolf online”. Feel free to sign up for the next one, usually in the Standard- Starts When Full category. To join, simply click on the game name and then click on the “Sign Me UP!!!” link at the top of the page.
</body>
</html>
<?php
} else {
?>
<html>
<head>
<title>Request Password </title>
</head>
<body>
<center>
<h3>Request Password Access to Cassandra Werewolf</h3>
<form method='post' action='<?=$_SERVER['PHP_SELF']?>'>
BGG UserID: <input type='text' name='bggid' value='' />
<br /><br />
<input type='submit' name='request' value=" Request Access " />
</form>
</center>
</body>
</html>
<?php
}
?>
