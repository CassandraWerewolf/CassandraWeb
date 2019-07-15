<?php
require("timer.php");
$timer = new BC_Timer;
$timer->start_time();



session_start();
require_once('Cache/Lite.php');
include_once("menu.php");
include_once("php/common.php");

//Test to see if idenity of visitor can be determined.
#print "Session: ".$_SESSION['uid']."<br />";
#print "Cookie: ".$_COOKIE['cassy_uid']."<br />";
#print "Login: ".$_REQUEST['login']."<br />";
if ( isset($_SESSION['uid']) || isset($_COOKIE['cassy_uid']) ||isset( $_REQUEST['login']) ) {
  include_once("php/accesscontrol.php");
}


$cache = init_cache();

$site = '';

echo "<html> <head> <title>BGG Werewolf Game Stats</title>";
?>
<link rel='stylesheet' type='text/css' href='<?=$site;?>/bgg.css'>
</head>
<body>
<?php display_menu() ?>
<center>
<h1>BGG Werewolf Game Stats</h1>

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
	if($data = $cache->get('game-' . $id, 'front')) {
		echo $data;
	}
	else {
		$data = "<tr><td>" . get_game($id,"num") .  "</td><td>";
		$data .= get_game($id,"in, title") . "</td></tr>\n";
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
	$sql = "Select id, DATE_FORMAT(start_date, '%b-%d-%y') as start from Games where status='Sign-up' order by start_date asc";
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
<a href='<?=$site;?>/create_a_game.php'>Add a Game in Signup</a>
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
		<a href='<?=$site;?>/chat.php'>New Experimental Group Chat</a>
	</td></tr>
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
	<tr><td>
<a href='<?=$site;?>/show_games.php?type=missing_winner'>Finished games with missing winner</a>
	</td></tr>
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
	<tr><td>
		More to come
	</td></tr>
</table>

<?php

$timer->end_time();
echo number_format($timer->elapsed_time(), 3) . " seconds";
?>
</center>
</body>
</html>


