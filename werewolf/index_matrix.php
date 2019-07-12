<?php
//Test to see if idenity of visitor can be determined.
#print "Session: ".$_SESSION['uid']."<br />";
#print "Cookie: ".$_COOKIE['cassy_uid']."<br />";
#print "Login: ".$_REQUEST['login']."<br />";
if ( isset($_SESSION['uid']) || isset($_COOKIE['cassy_uid']) ||isset( $_REQUEST['login']) ) {
  include_once("php/accesscontrol.php");
}
session_start();
require_once('Cache/Lite.php');
include_once("menu.php");
include_once("php/common.php");

require("timer.php");
$timer = new BC_Timer;
$timer->start_time();


$cache = init_cache();

$site = '';

echo "<html> <head> <title>BGG Werewolf Game Stats</title>";
?>
<link rel='stylesheet' type='text/css' href='<?=$site;?>/matrix.css'>
</head>
<body>
<?php display_menu() ?>
<center>
<h1>BGG Werewolf Game Stats</h1>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">

<br />

<?php
echo "<table class='forum_table' border='0'><tr><th>Game #'s</th><th>Total</th><th>Won by Evil</th><th>Won by Good</th><th>Other type of game</th><th>In Progress</th></tr>";

if($data = $cache->get('total-games', 'front')) {
	echo $data;
}
else {
	$sql = "Select count(*) from Games where status in ('In Progress', 'Finished') and number != 0;";
	$result = mysql_query($sql);
	$value = mysql_result($result,0,0);
	$data = "<tr><td></td><td><a href='$site/show_games.php?type=all'>$value</a></td>";
	echo $data;
	$cache->save($data, 'total-games', 'front');
}	

if($data = $cache->get('evil-games', 'front')) {
	echo $data;
}
else {
	$sql = "Select count(*) from Games where winner='evil' AND status = 'Finished';";
	$result = mysql_query($sql);
	$value = mysql_result($result,0,0);
	$data = "<td><a href='$site/show_games.php?type=evil'>$value</a></td>";
	echo $data;
	$cache->save($data, 'evil-games', 'front');
}	

if($data = $cache->get('good-games', 'front')) {
	echo $data;
}
else {
	$sql = "Select count(*) from Games where winner='good' AND status = 'Finished';";
	$result = mysql_query($sql);
	$value = mysql_result($result,0,0);
	$data = "<td><a href='$site/show_games.php?type=good'>$value</a></td>";
	echo $data;
	$cache->save($data, 'good-games', 'front');
}	

if($data = $cache->get('other-games', 'front')) {
	echo $data;
}
else {
	$sql = "Select count(*) from Games where winner='other' AND status = 'Finished';";
	$result = mysql_query($sql);
	$value = mysql_result($result,0,0);
	$data = "<td><a href='$site/show_games.php?type=other'>$value</a></td>";
	echo $data;
	$cache->save($data, 'other-games', 'front');
}	

if($data = $cache->get('current-games', 'front')) {
	echo $data;
}
else {
	$sql = "Select count(*) from Games where status='In Progress' and number != 0";
	$result = mysql_query($sql);
	$value = mysql_result($result,0,0);
	$data = "<td>$value</td>";
	echo $data;
	$cache->save($data, 'current-games', 'front');
}	

echo "</tr></table>"
?>

<br>

<!-- Structer Main Table -->
<table border='0' cellspacing=5>
<tr valign='top'>

<td valign='top'>
<!-- Start Games In Progress -->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th colspan=2>Games in Progress</th></tr>
<?php

if($data = $cache->get('games-in-progress-list', 'front')) {
	$list = unserialize($data);
}
else {
	$sql = "Select id from Games where status='In Progress' and number is not null order by start_date, number";
	$result = mysql_query($sql);
	$list = array();
	while ( $game = mysql_fetch_array($result) ) {
		array_push($list, $game['id']);
	}	
	$cache->save(serialize($list), 'games-in-progress-list', 'front');
}	

foreach($list as $id) {
	if(isset($_SESSION['uid'])) {
		$sql = sprintf("select * from Users_game_all where game_id=%s and user_id=%s",$id,$_SESSION['uid']);
		$result = mysql_query($sql);
		if ( mysql_num_rows($result) != 0 ) {
			$in_game = true;
		}
		else {
			$in_game = false;
		}
	}
	else {
		$in_game = false;
	}

	if( $in_game = true) {
		$data = "<tr><td>" . get_game($id,"num") .  "</td><td>";
		$data .= get_game($id,"repl, in, chat, title") . "</td></tr>\n";
		echo $data;
	} 
	elseif ($data = $cache->get('game-' . $id, 'front')) {
		echo $data;
	}
	else {
		$data = "<tr><td>" . get_game($id,"num") .  "</td><td>";
		$data .= get_game($id,"repl, title") . "</td></tr>\n";
		echo $data;
		$cache->save($data, 'game-' . $id, 'front');
	}
}
?>
</table>
<!-- End Games In Progress -->
<br />
<!-- Start Recently Ended Games -->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th colspan=2>10 Most Recently Ended Games</th></tr>
<?php
if($data = $cache->get('games-ended-list', 'front')) {
	$list = unserialize($data);
}
else {
	$sql = "Select id from Games where status = 'Finished' and number is not null order by end_date desc Limit 0, 10";
	$result = mysql_query($sql);
	$list = array();
	while ( $game = mysql_fetch_array($result) ) {
		array_push($list, $game['id']);
	}	
	$cache->save(serialize($list), 'games-ended-list', 'front');
}	

foreach($list as $id) {
	if(isset($_SESSION['uid'])) {
		$sql = sprintf("select * from Users_game_all where game_id=%s and user_id=%s",$id,$_SESSION['uid']);
		$result = mysql_query($sql);
		if ( mysql_num_rows($result) != 0 ) {
			$in_game = true;
		}
		else {
			$in_game = false;
		}
	}
	else {
		$in_game = false;
	}

	if( $in_game = true) {
		$data = "<tr><td>" . get_game($id,"num") .  "</td><td>";
		$data .= get_game($id,"in, chat, title") . "</td></tr>\n";
		echo $data;
	} 
	elseif ($data = $cache->get('game-' . $id, 'front')) {
		echo $data;
	}
	else {
		$data = "<tr><td>" . get_game($id,"num") .  "</td><td>";
		$data .= get_game($id,"title") . "</td></tr>\n";
		echo $data;
		$cache->save($data, 'game-' . $id, 'front');
	}
}
?>
</table>
<!-- End Recently Ended Games -->


</td>


<td>
<!-- Start Games in Signup Table-->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th>Games in Signup <a href="/cassy_rss.php"><img border='0' src="/images/rss.png" /></a></th><th>Moderator</th><th>Start Date</th></tr>
<?php
if($data = $cache->get('games-signup-list', 'front')) {
	$list = unserialize($data);
}
else {
	$sql = "Select id, DATE_FORMAT(start_date, '%b-%d-%y') as start from Games where status='Sign-up' and ( ((datediff(start_date, now()) <=500) and (datediff(now(), start_date) <=3)) or automod_id is not null) order by start_date asc";
	$result = mysql_query($sql);
	$list = array();
	while ( $game = mysql_fetch_array($result) ) {
		$row = array($game['id'], $game['start']);
		$row = serialize($row);
		array_push($list, $row);
	}	
	$cache->save(serialize($list), 'games-signup-list', 'front');
}	

foreach($list as $row) {
	$row = unserialize($row);
	$id = $row[0];
	$start = $row[1];
	if($data = $cache->get($_SESSION['uid'],'front-signup-' . $id)) {
		echo $data;
	}
	else {
		$data = "<tr><td>" . get_game($id,"complex, in, title, full") .  "</td>";
		$data .= "<td>" . get_game($id, "mod_np") .  "</td>\n";
		$data .= "<td>" . $start . "</td></tr>";
		echo $data;
		$cache->save($data, $_SESSION['uid'],'front-signup-' . $id);
	}
}
?>
</table>
<span><b>Complexity Ratings: <img src='images/Newbie_large.png'><img src='images/Low_large.png'><img src='images/Medium_large.png'><img src='images/High_large.png'><img src='images/Extreme_large.png'></span><br />
<a href='<?=$site;?>/create_a_game.php'>Add a Game in Signup</a><br />
<a href='<?=$site;?>/automod/new.php'>Add an Auto-Mod Game</a>
<!--End Signup Table-->
</td>
</tr>

<tr>
</table>

<br />

<!-- <img src='games_started_graph.php'> -->

<table class='forum_table' cellpadding='2'>
	<tr><th>Other Abilities</th></tr>
	<tr><td>
<a href='<?=$site;?>/wotw.php'>Wolf of the Week List</a>
	</td></tr>
	<tr><td>
<a href='<?=$site;?>/automod'>Create your own Automod Template</a>
	</td></tr>
	<tr><td>
<a href='<?=$site;?>/bookmarklets.php'>Bookmarklets</a>
	</td></tr>
	<!--
	<tr><td>
		<a href='<?=$site;?>/chat.php'>New Experimental Group Chat</a>
	</td></tr>
	-->
	<tr><td>
		<a href='<?=$site;?>/show_games_missing_info.php'>Games with missing data</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/signup.php'>Get a Password</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/password.php'>Change Password</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/chat_with_us.html'>Need Help?  Have Suggestions?  Want to Chat with an Administator?</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/fun_stats.php'>Fun Statistics</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/ranks.php'>Player and moderator Ranks</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/players_with_profiles.php'>Players with Profiles</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/timezones.php'>Player Timezone Chart</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/show_cassandra_files.php'>Current games in the Cassandra Files System </a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/show_active.php'>Currently active players and moderators</a>
	</td></tr>
	<!--
	<tr><td>
<a href='<?=$site;?>/show_games.php?type=missing_winner'>Finished games with missing winner</a>
	</td></tr>
	-->
	<tr><td>
<a href='<?=$site;?>/wolfy_awards.php'>Wolfy Awards</a>
	</td></tr>
	<tr><td>
<a href='<?=$site;?>/balance'>Game Balance Creator</a>
	</td></tr>
	<tr><td>
		<a href='<?=$site;?>/change_log.html'>Change Log</a> - Last Updated: <?php echo date("l, d-M-Y", filemtime('change_log.html'));?> 
	</td/></tr>
	<tr><td>
		<a href='<?=$site;?>/secrecy_pledge.html'>Our Pledge</a> - Please Read
	</td/></tr>
	<tr><td>
		<a href='<?=$site;?>/admin'>Admin Pages</a>
	</td/></tr>
</table>

<?php

$timer->end_time();
echo number_format($timer->elapsed_time(), 3) . " seconds";
?>
</center>
</body>
</html>


