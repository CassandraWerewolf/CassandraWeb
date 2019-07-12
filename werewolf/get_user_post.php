<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";

dbConnect();

$thread_id = $_REQUEST['thread_id'];
$player = $_REQUEST['player'];

if ( $thread_id == "" || $player == "" ) {
?>
<html>
<head>
<script language='javascript'>
<!--
window.history.back();
//-->
</script>
</head>
<body>
Please hit your browsers back button.
</body>
</html>
<?php
exit;
}

$sql = "Select id, title from Games where thread_id='$thread_id'";
$result = mysql_query($sql);
if (mysql_num_rows($result) == 1 ) { 
  $game_id = mysql_result($result,0,0);
  $game_name = mysql_result($result,0,1);
} else {
  $game_id = 0;
  $game_name = "Invalid Game";
}

$sql = "Select last_dumped from Post_collect_slots where game_id='$game_id'";
$result = mysql_query($sql);
$num_rows = mysql_num_rows($result);
if($num_rows <= 0)
{
	$last_dumped = "";
}	
else
{
	$dumped_time = mysql_result($result,0,0);
	$last_dumped = "Last dumped on " . $dumped_time;
}

if ( $player != 'all' && $player != 'users' ) {
$sql = "Select id from Users where name='$player'";
$result = mysql_query($sql);
$user_id = mysql_result($result,0,0);

}
?>
<html>
<head>
<title>Cassandra Files for <?=$player;?> in "<?=$game_name;?>" <?=$last_dumped;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu(); ?>
<h2>Cassandra Files for <?=$player;?> in "<?=$game_name;?>" <?=$last_dumped;?></h2>
<p>Return to <a href="/game/<?=$thread_id;?>">Game Page</a></p>
<?php
$where_user = "";
if ( $player == 'all' ) { echo "<p><a href=\"/game/$thread_id/users\">Remove System posts</a></p>"; }
if ( $player == 'users' ) { echo "<p><a href=\"/game/$thread_id/all\">Add System posts</a></p>"; }
if ( $player != 'all' ) { $where_user = " and Posts.user_id='$user_id' "; }
if ( $player == 'users' ) { $where_user = " and Posts.user_id not in (306,749) "; }
$sql = "Select Users.name, article_id, text, date_format(time_stamp, '%a %b %e,%Y %l:%i %p') as post_date from Users, Posts where Users.id=Posts.user_id and Posts.game_id='$game_id' $where_user order by article_id";
$result = mysql_query($sql);
$rownum = mysql_num_rows($result);
if ( $rownum < 1 ) {
  print "<p>$player did not post in this game.</p>\n";
} else {
?>
<?php
while ( $row = mysql_fetch_array($result) ) {
?>
<table cellpadding="1" cellspacing="1" border="0" width="100%" class='forum_table'>
<?php
if ( $player == 'all'  || $player == 'users' ) {
?>
<tr>
<th align='left'><?=$row['name'];?> wrote:</th>
</tr>
<?php
}
?>
<tr>
<td valign='top'>
<?=$row['text'];?>
</td>
</tr>
<tr>
<td align='right'>
<span class='forum_date'>
<a href='http://www.boardgamegeek.com/article/<?=$row['article_id'];?>#<?=$row['article_id'];?>'><img src="//cf.geekdo-static.com/images/icon_minipost.gif" border=0></a>
Posted <?=$row['post_date'];?> 
[<A href="http://www.boardgamegeek.com/article/reply/<?=$row['article_id'];?>">Reply</A>]
[<A href="http://www.boardgamegeek.com/article/quote/<?=$row['article_id'];?>">Quote</A>]
</span>
</td>
</tr>
</table>
<br />
<?php
}
?>
</body>
</html>
<?php
}
?>
