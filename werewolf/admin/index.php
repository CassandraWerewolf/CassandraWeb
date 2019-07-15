<?php
include_once "../php/accesscontrol.php";
checkLevel($level,2);
include_once "../menu.php";
?>
<html>
<head>
<title>Admin Index Page</title>
<link rel='stylesheet' type='text/css' href='../assets/css/application.css'>
</head>
<body>
<?php display_menu();?>
<h1>Admin Index Page</h1>
<ul>
<li><a href='signup.php'>Add a BGG user to DB and send them a password</a></li>
<li><a href='game_ids.php'>Game ID's</a></li>
<li><a href='user_ids.php'>User ID's</a></li>
<li><a href='privileged_users.php'>Privileged Users</a></li>
<li><a href='add_wotw.php'>Add Wolf of the Week</a></li>
<li><a href='automod_running.php'>Running Automod Games</a></li>
<li><a href='automod_post.php'>Post to All Automod games</a></li>
<li><a href='clean_cache.php'>Clean the cache</a></li>
<li><a href='eaccel.php'>EAccelerator Control Panel</a></li>
<li><a href='reset_cal.php'>Reset Calendar</a></li>
<li><a href='update_calendar.php'>Update Calendar</a> (This is the script Cassy Calls)</li>
</ul>
</body>
</html>
