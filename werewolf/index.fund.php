<?php

session_start();

//Test to see if idenity of visitor can be determined.
#print "Session: ".$_SESSION['uid']."<br />";
#print "Cookie: ".$_COOKIE['cassy_uid']."<br />";
#print "Login: ".$_REQUEST['login']."<br />";

if ( isset($_SESSION['uid']) || isset($_COOKIE['cassy_uid']) ||isset( $_REQUEST['login']) ) {
  include_once("php/accesscontrol.php");
}

require_once('Cache/Lite.php');
include_once("menu.php");
include_once("php/common.php");

require("timer.php");
$timer = new BC_Timer;
$timer->start_time();


$cache = init_cache();

echo "<html> <head> <title>BGG Werewolf Stats</title>";
?>
<link rel='stylesheet' type='text/css' href='bgg.css'>
</head>
<body>
<?php display_menu() ?>
<center>

<div style="padding: 0 20%;">
	<h1>2019 Fundraser</h1>
	To provide high performance and high availablity and continuously backed up web site Cassandra is hosted on Amazon AWS. This cost money and we hope that our community will help contibute to that cost.<br>
	<h3 style="margin: 5px 0">Goal: $60/month</h3><br>
	<h5 style="margin: 5px 0">$2/month from 30 or more people</h3><br>
	<div style="float: left; padding: 0 0 0 28%;">
	<a href="https://www.paypal.me/CassandraWerewolf/24">
	<img alt="donate button" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0">
	</div>
	<div style="float: left; margin: 5px;">
	<h3>OR</h3>
	</div>	
	<div style="float: left">
	<a href="https://www.patreon.com/bePatron?u=10619201" data-patreon-widget-type="become-patron-button">Become a Patron!</a><script async src="https://c6.patreon.com/becomePatronButton.bundle.js"></script>
	</a>
	</div>
	<div style="clear: both;"></div>
</div>

<h1>BGG Werewolf Stats</h1>

<?php
echo "<table class='forum_table' border='0'><tr><th>Game #'s</th><th>Total</th><th>Won by Evil</th><th>Won by Good</th><th>Other type of game</th><th>In Progress</th></tr>";

if($data = $cache->get('game-counts', 'front')) {
	echo $data;
}
else {
	$sql = "select sum(case when status = 'finished' and number !=0 then 1 else 0 end) finished_games, sum(case when status = 'finished' and winner='good' then 1 else 0 end) good_wins,sum(case when status = 'finished' and winner='evil' then 1 else 0 end) evil_wins,sum(case when status = 'finished' and winner='other' then 1 else 0 end) other_wins,sum(case when status = 'in progress' and number != 0 then 1 else 0 end) inprogress_games from Games;";
	$result = mysql_query($sql);
	$value = mysql_result($result,0,0) + mysql_result($result,0,4);
	$data = "<tr><td></td><td><a href='show_games.php?type=all'>$value</a></td>";
	$value = mysql_result($result,0,2);
	$data .= "<td><a href='show_games.php?type=evil'>$value</a></td>";
	$value = mysql_result($result,0,1);
	$data .= "<td><a href='show_games.php?type=good'>$value</a></td>";	
	$value = mysql_result($result,0,3);
	$data .= "<td><a href='show_games.php?type=other'>$value</a></td>";
	$value = mysql_result($result,0,4);
	$data .= "<td>$value</td>";
	echo $data;
	$cache->save($data, 'game-counts', 'front');	
}

echo "</tr></table>";

?>

<br>

<!-- Structer Main Table -->
<table border='0' cellspacing=5>
<tr valign='top'>

<td valign='top'>
<!-- Start Games In Progress : Fast -->
<?php

$inlist = array();
$newmsg = array();
$repls  = array();
if (isset($_SESSION['uid'])) {
    #Get list of games this user is in
	$sql = sprintf("select game_id from Users_game_all join Games on Users_game_all.game_id = Games.id where user_id=%s and Games.status != 'Finished' union (select game_id from Users_game_all join Games on Users_game_all.game_id = Games.id where user_id=%s and Games.status = 'Finished' and number is not null order by Games.end_date desc limit 10)", $_SESSION['uid'], $_SESSION['uid']);
	$result = mysql_query($sql);
	while ($game = mysql_fetch_array($result)) {
		array_push($inlist, $game['game_id']);
	}

	#Get list of games this user has new chat messages
    $sql = sprintf("select Games.id from Games, Chat_users, Chat_rooms, Chat_messages where Chat_users.room_id=Chat_rooms.id and Chat_rooms.id=Chat_messages.room_id and Chat_messages.post_time >= Chat_users.last_view and Chat_messages.post_time > Chat_users.open and Chat_messages.post_time < if(Chat_users.close is null, now(), Chat_users.close) and Games.id=Chat_rooms.game_id and Games.status ='In Progress' and Chat_users.user_id=%s",quote_smart($_SESSION['uid']));
	$result = mysql_query($sql);
	while ($game = mysql_fetch_array($result)) {
		array_push($newmsg, $game['id']);
	}

    #Get list of games that need a replacement
	$sql = "select distinct game_id from Games join Players on Players.game_id = Games.id where need_replace is not null and Games.status != 'Finished'";
	$result = mysql_query($sql);
	while ($game = mysql_fetch_array($result)) {
		array_push($repls, $game['game_id']);
	}	
}

$sql = "Select id, number, title, TIME_FORMAT(day_length, '%H:%i') day_length, TIME_FORMAT(night_length, '%H:%i') night_length, thread_id from Games where status='In Progress' and deadline_speed='Fast' and number is not null order by start_date, number";
$result = mysql_query($sql);
$list = array(); 
$output = "";
while ( $game = mysql_fetch_array($result) ) {

	$data = "<tr><td>" . $game['number'] .  ") </td><td>";
	$data .= in_array($game['id'], $repls) ? "<img src='/images/i_replace.png' border='0'/>" : "";
	$data .= in_array($game['id'], $inlist) ? "<img src='/images/calendar.png' />" : "";
	$data .= in_array($game['id'], $newmsg) ? "<a href='/game/".$game['thread_id']."/chat'><img src='/images/new_message.png' border='0'/></a>" : "";
	$data .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> " . "</td><td>";
	$data .=  $game['day_length'] . "</td><td>";
	$data .=  $game['night_length'] . "</td></tr>\n";
	$output .= $data;

}
if ( $output != "" ) {
?>
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th colspan=2>In Progress (Fast)</th><th>Day</th><th>Night</th></tr>
<?=$output;?>
</table><br />
<?php } ?>
<!-- Start Games In Progress : Standard-->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th colspan=2>In Progress (Standard)</th><th>Dusk</th><th>Dawn</th></tr>
<?php

$sql = "Select id, number, title, TIME_FORMAT(lynch_time, '%l:%i %p') lynch_time, TIME_FORMAT(na_deadline, '%l:%i %p') na_deadline, thread_id from Games where status='In Progress' and deadline_speed='Standard' and number is not null order by start_date, number";
$result = mysql_query($sql);
$list = array(); 
while ( $game = mysql_fetch_array($result) ) {

	$data = "<tr><td>" . $game['number'] . ") </td><td>";

	$data .= in_array($game['id'], $repls) ? "<img src='/images/i_replace.png' border='0'/>" : "";
	$data .= in_array($game['id'], $inlist) ? "<img src='/images/calendar.png' />" : "";
	$data .= in_array($game['id'], $newmsg) ? "<a href='/game/".$game['thread_id']."/chat'><img src='/images/new_message.png' border='0'/></a>" : "";
	$data .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> " . "</td><td>";

	$data .=  $game['lynch_time'] . "</td><td>";
	$data .=  $game['na_deadline'] . "</td></tr>\n";
	echo $data;
}

?>
</table>
<!-- End Games In Progress -->
<br />
<!-- Start Recently Ended Games -->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th colspan=2>10 Most Recently Ended</th></tr>
<?php
$sql = "Select id, title, number, thread_id from Games where status = 'Finished' and number is not null order by end_date desc Limit 0, 10";
$result = mysql_query($sql);
$list = array();
while ( $game = mysql_fetch_array($result) ) {

	$data = "<tr><td>" . $game['number'] . ") </td><td>";

	$data .= in_array($game['id'], $inlist) ? "<img src='/images/calendar.png' />" : "";
	$data .= in_array($game['id'], $newmsg) ? "<a href='/game/".$game['thread_id']."/chat'><img src='/images/new_message.png' border='0'/></a>" : "";
	$data .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> " . "</td></tr>\n";

	echo $data;
}
?>
</table>
<!-- End Recently Ended Games -->
</td>


<td>
<!-- Start Games in Signup Table: Fast-->
<?php
$sql = "Select Games.id, Games.thread_id, Games.complex, Games.title, DATE_FORMAT(start_date, '%b-%d-%y %l:%i %p') as start, swf, TIME_FORMAT(day_length, '%H:%i') day_length, TIME_FORMAT(night_length, '%H:%i') night_length, GROUP_CONCAT(Users.name SEPARATOR ',') mods, (select count(*)from Players where Players.game_id = Games.id) num_players, Games.max_players from Games join Moderators on Games.id = Moderators.game_id join Users on Moderators.user_id = Users.id where status='Sign-up' and deadline_speed='Fast' and ( (swf='No' and (datediff(start_date, now()) <=500) and (datediff(now(), start_date) <=3)) or swf='Yes' or automod_id is not null ) group by Games.id order by swf, start_date asc";
$result = mysql_query($sql);
$output = "";
while ( $game = mysql_fetch_array($result) ) {
	$start = $game['start'];
	if ( $game['swf'] == "Yes" ) { $start = "When Full"; }

	$data = "<tr><td><img src='/images/".$game['complex']."_small.png' /> ";
	$data .= in_array($game['id'], $inlist) ? "<img src='/images/calendar.png' />" : "";
	$data .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> ";

	$full = $game['num_players']."/".$game['max_players'];
	if ( $game['num_players'] == $game['max_players'] ) { $full = "Full/".$game['num_players']; }
	$data .= "($full) ";
	$data .= "</td>";

	$mods = explode(',', $game['mods']);
	foreach ($mods as $key => $mod) {
		$mods[$key] = "<a href='/player/$mod'>$mod</a>";
	}

	$data .= "<td>" . implode(', ', $mods) .  "</td>\n";
	$data .= "<td>" . $start . "</td>";
	$data .= "<td>" . $game['day_length'] . "</td>";
	$data .= "<td>" . $game['night_length'] . "</td></tr>\n";
	$output .= $data;
}
if ( $output != "" ) {
?>
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th>In Signup (Fast)<a href="cassy_rss.php"><img border='0' src="images/rss.png" /></a></th><th>Moderator</th><th>Start Date</th><th>Day</th><th>Night</th></tr>
<?=$output;?>
</table><br />
<?php } ?>
<!-- Start Games in Signup Table-->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th>In Signup (Standard)<a href="cassy_rss.php"><img border='0' src="images/rss.png" /></a></th><th>Moderator</th><th>Start Date</th><th>Dusk</th><th>Dawn</th></tr>
<?php
$sql = "Select Games.id, Games.thread_id, Games.complex, Games.title, DATE_FORMAT(start_date, '%b-%d-%y') as start, swf, TIME_FORMAT(lynch_time, '%l:%i %p') lynch_time, TIME_FORMAT(na_deadline, '%l:%i %p') na_deadline, GROUP_CONCAT(Users.name SEPARATOR ',') mods, (select count(*)from Players where Players.game_id = Games.id) num_players, Games.max_players from Games join Moderators on Games.id = Moderators.game_id join Users on Moderators.user_id = Users.id where status='Sign-up' and deadline_speed='Standard' and ( ((datediff(start_date, now()) <=500) and (datediff(now(), start_date) <=3) and swf='No') or automod_id is not null ) and swf = 'No' group by Games.id order by start_date asc";
$result = mysql_query($sql);
$list = array();
while ( $game = mysql_fetch_array($result) ) {
	$start = $game['start'];

	$data = "<tr><td><img src='/images/".$game['complex']."_small.png' /> ";
	$data .= in_array($game['id'], $inlist) ? "<img src='/images/calendar.png' />" : "";
	$data .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> ";

	$full = $game['num_players']."/".$game['max_players'];
	if ( $game['num_players'] == $game['max_players'] ) { $full = "Full/".$game['num_players']; }
	$data .= "($full) ";
	$data .= "</td>";

	$mods = explode(',', $game['mods']);
	foreach ($mods as $key => $mod) {
		$mods[$key] = "<a href='/player/$mod'>$mod</a>";
	}

	$data .= "<td>" . implode(', ', $mods) .  "</td>\n";
	$data .= "<td>" . $start . "</td>";
	$data .= "<td>" . $game['lynch_time'] . "</td>";
	$data .= "<td>" . $game['na_deadline'] . "</td></tr>\n";

	echo $data;
}
?>
</table>
<br />
<!-- Start Games in Signup Table - SWF-->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th>In Signup (Standard - Starts When Full)</th><th>Moderator</th><th>Needs</th><th>Dusk</th><th>Dawn</th></tr>
<?php
$sql = "Select id,  Games.thread_id, title, complex, (max_players - count(Players.user_id)) as players_needed,  (max_players - count(Players.user_id))=0 as players_needed_bin,  cast(format(((count(Players.user_id)/max_players)*100),0) as unsigned) as percent,  TIME_FORMAT(lynch_time, '%l:%i %p') lynch_time,  TIME_FORMAT(na_deadline, '%l:%i %p') na_deadline, (select GROUP_CONCAT(Users.name SEPARATOR ',') mods from Moderators join Users on Moderators.user_id = Users.id where Moderators.game_id = Games.id) mods, (select count(*)from Players where Players.game_id = Games.id) num_players, max_players from Games  LEFT JOIN Players on Games.id=Players.game_id  where `status`='Sign-up' and deadline_speed='Standard' and swf='Yes'  group by Games.id  order by players_needed_bin asc, players_needed asc, percent desc";
$result = mysql_query($sql);
while ( $game = mysql_fetch_array($result) ) {

	$data = "<tr><td><img src='/images/".$game['complex']."_small.png' /> ";
	$data .= in_array($game['id'], $inlist) ? "<img src='/images/calendar.png' />" : "";
	$data .= "<a href='/game/".$game['thread_id']."'>".$game['title']."</a> ";

	$full = $game['num_players']."/".$game['max_players'];
	if ( $game['num_players'] == $game['max_players'] ) { $full = "Full/".$game['num_players']; }
	$data .= "($full) ";
	$data .= "</td>";

	$mods = explode(',', $game['mods']);
	foreach ($mods as $key => $mod) {
		$mods[$key] = "<a href='/player/$mod'>$mod</a>";
	}

	$data .= "<td>" . implode(', ', $mods) .  "</td>\n";

	$data .= "<td align='center'>" . $game['players_needed'] . "</td>";
	$data .= "<td>" . $game['lynch_time'] . "</td>";
	$data .= "<td>" . $game['na_deadline'] . "</td></tr>\n";
	echo $data;
}
?>
</table>
<br />
<span><b>Complexity Ratings: <img src='images/Newbie_large.png'><img src='images/Low_large.png'><img src='images/Medium_large.png'><img src='images/High_large.png'><img src='images/Extreme_large.png'></span><br />
<a href='create_a_game.php'>Add a Game in Signup</a><br />
<a href='automod/new.php'>Add an Auto-Mod Game</a>
<!--End Signup Table-->
</td>
</tr>

<tr>
</table>

<br />

<!-- <img src='games_started_graph.php'> -->

<table class='forum_table' cellpadding='2'>
	<tr><th colspan='2'>Other Abilities</th></tr>
	<tr><td>
		<a href='signup.php'>Get a Password</a>
	</td>
	<td>
		<a href='password.php'>Change Password</a>
	</td></tr>
	<tr><td>
<a href='wotw.php'>Wolf of the Week List</a>
	</td>
	<td>
<a href='social'>Find WW players Elsewhere</a>
	</td></tr>
	<tr><td>
<a href='automod'>Create your own Automod Template</a>
	</td>
	<td>
		<a href='aes.html'>AES Encryption App</a>
	</td></tr>
	<tr><td>
		<a href='rsa.html'>RSA Encryption App</a>
	</td>
	<td>
		<a href='shamir.html'>Shamir Secret Sharing App</a>
	</td></tr>
	<tr><td>
<a href='bookmarklets.php'>Bookmarklets</a>
	</td>
	<td>
     <a href='https://discord.gg/66Zn2PX' target="_blank">Discord Chat</a>
    </td>
	</tr>
	<tr><td>
		<a href='show_games_missing_info.php'>Games with missing data</a>
	</td>
	<td>
		<a href='fun_stats.php'>Fun Statistics</a>
	</td></tr>
	<tr><td>
		<a href='ranks.php'>Player and moderator Ranks</a>
	</td>
	<td>
		<a href='http://boardgamegeek.com/thread/225928'>Player Picture Thread</a> (<a href='game/225928'>By Player</a>)
	</td></tr>
	<tr><td>
		<a href='timezones.php'>Player Timezone Chart</a>
	</td>
	<td>
		<a href='show_cassandra_files.php'>Current games in the Cassandra Files System </a>
	</td></tr>
	<tr><td>
		<a href='show_active.php'>Currently active players and moderators</a>
	</td>
	<td>
<a href='wolfy_awards.php'>Wolfy Awards</a>
	</td></tr>
	<tr><td>
<a href='balance'>Game Balance Creator</a>
	</td>
	<td>
		<a href='change_log.html'>Change Log</a> - Last Updated: <?php echo date("l, d-M-Y", filemtime('change_log.html'));?> 
	</td/></tr>
	<tr><td>
		<a href='secrecy_pledge.html'>Our Pledge</a> - Please Read
	</td/>
	<td>
		<a href='admin'>Admin Pages</a>
	</td/></tr>
</table>

<?php

$timer->end_time();
echo number_format($timer->elapsed_time(), 3) . " seconds";
?>
</center>
</body>
</html>


