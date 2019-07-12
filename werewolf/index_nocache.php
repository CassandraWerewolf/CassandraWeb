<?php
require("timer.php");
$timer = new BC_Timer;
$timer->start_time();

session_start();
//Test to see if idenity of visitor can be determined.
#print "Session: ".$_SESSION['uid']."<br />";
#print "Cookie: ".$_COOKIE['cassy_uid']."<br />";
#print "Login: ".$_REQUEST['login']."<br />";
if ( isset($_SESSION['uid']) || isset($_COOKIE['cassy_uid']) ||isset( $_REQUEST['login']) ) {
  include_once("php/accesscontrol.php");
}

include_once("php/db.php");
include_once("menu.php");
include_once("php/common.php");

dbConnect();

$site = '';

$sql = "Select count(*) from Games where status in ('In Progress', 'Finished') and number != 0;";
$result = mysql_query($sql);
$total = mysql_result($result,0,0);
$sql = "Select count(*) from Games where winner='evil' AND status = 'Finished';";
$result = mysql_query($sql);
$evil = mysql_result($result,0,0);
$sql = "Select count(*) from Games where winner='good' AND status  = 'Finished';";
$result = mysql_query($sql);
$good = mysql_result($result,0,0);
$sql = "Select count(*) from Games where winner='other' AND status  = 'Finished';";
$result = mysql_query($sql);
$other = mysql_result($result,0,0);
$sql = "Select count(*) from Games where status='In Progress' and number != 0";
$result = mysql_query($sql);
$current = mysql_result($result,0,0);

?>
<html>
<head>
<title>BGG Werewolf Game Stats</title>
<link rel='stylesheet' type='text/css' href='<?=$site;?>/bgg.css'>
<script language='javascript'>
<!--
function view_player() {
index = document.view.player.selectedIndex
player = document.view.player.options[index].value
location.href = "player/"+player
}
function view_game() {
index = document.view.game.selectedIndex
game = document.view.game.options[index].value
location.href = "game/"+game
}
//-->
</script>
</head>
<body>
<?php display_menu() ?>
<center>
<h1>BGG Werewolf Game Stats</h1>
<br />
<table class='forum_table' border='0'>
<tr><th>Game #'s</th><th>Total</th><th>Won by Evil</th><th>Won by Good</th><th>Other type of game</th><th>In Progress</th></tr>
<tr><td> </td><td><a href='<?=$site;?>/show_games.php?type=all'><?=$total;?></a></td><td><a href='<?=$site;?>/show_games.php?type=evil'><?=$evil;?></a></td><td><a href='<?=$site;?>/show_games.php?type=good'><?=$good;?></a></td><td><a href='<?=$site;?>/show_games.php?type=other'><?=$other;?></a></td><td><?=$current;?></td></tr>
</table>

<br>

<!-- Structer Main Table -->
<table border='0' cellspacing=5>
<tr valign='top'>

<td valign='top'>
<!-- Start Games In Progress -->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th colspan=2>Games in Progress</th></tr>
<?php
$sql = "Select id from Games where status='In Progress' and number is not null order by start_date, number";
$result = mysql_query($sql);
while ( $game = mysql_fetch_array($result) ) {
print "<tr><td>";
print get_game($game['id'],"num");
print "</td><td>";
print get_game($game['id'],"in, chat, title");
print "</td></tr>\n";
}
?>
</table>
<!-- End Games In Progress -->
<br />
<!-- Start Recently Ended Games -->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th colspan=2>10 Most Recently Ended Games</th></tr>
<?php
$sql = "Select id from Games where status = 'Finished' and number is not null order by end_date desc Limit 0, 10";
$result = mysql_query($sql);
while ( $game = mysql_fetch_array($result) ) {
print "<tr><td>";
print get_game($game['id'],"num");
print "</td><td>";
print get_game($game['id'],"in, title");
print "</td></tr>\n";
}
?>
</table>
<!-- End Recently Ended Games -->


</td>


<td>
<!-- Start Games in Signup Table-->
<table width='100%' class='forum_table' cellpadding='2'>
<tr><th>Games in Signup</th><th>Moderator</th><th>Start Date</th></tr>
<?php
$sql = "Select id, DATE_FORMAT(start_date, '%b-%d-%y') as start from Games where status='Sign-up' order by start_date asc";
$result = mysql_query($sql);
while ( $game = mysql_fetch_array($result) ) {
print "<tr><td>";
print get_game($game['id'],"complex, in, title, full");
#print get_game($game['id'],"in, title");
print "</td><td>";
print get_game($game['id'],"mod_np");
print "</td><td>".$game['start']."</td></tr>";
}
?>
</td></tr>
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


