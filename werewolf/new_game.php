<?php
include "php/accesscontrol.php";
include_once "php/db.php";

dbConnect();

checkLevel($level,1);

if ( isset($_POST['confirm'] ) ) {
$start_date = $_POST['start_date'];
$title = $_POST['title'];
$thread_id = $_POST['thread_id'];
$moderator = $_POST['moderator'];
$old_players = $_POST['old_players'];
$new_players = $_POST['new_players'];

# Get the game Number
$result = mysql_query("select max(number)+1 from Games");
$numbers = mysql_fetch_array($result);
$number = $numbers[0];

# Insert the game into the database
$result = mysql_query("insert into Games (id, number, start_date, end_date, title, status, winner, thread_id) values ( NULL, $number, '$start_date', NULL, '$title', 'In Progress', '', '$thread_id')");
$game_id = mysql_insert_id();

# Insert the moderator into the database 
foreach ( $moderator as $mod_id ) {
$result = mysql_query("insert into Moderators ( user_id, game_id ) values ( '$mod_id', '$game_id' )");
}

# Insert the players into the database 
foreach ( $old_players as $player_id ) {
  $result = mysql_query("insert into Players ( user_id, game_id ) values ( '$player_id', '$game_id')");
}

# Insert new players into the database and add them to the game.
if ( $new_players != "" ) {
  $new_players_list = split ( ", ", $new_players );
  foreach ( $new_players_list as $name ) {
    $result = mysql_query("insert into Users ( id, name ) values ( NULL , '$name' )");
    $player_id = mysql_insert_id();
    $result = mysql_query("insert into Players ( user_id, game_id) values ( '$player_id', '$game_id' )");
  }
}
?>
<html>
<head>
<title>New Game Entered Database</title>
</head>
<body>
<center>
<h1>Sucessfully Entered Game id:<?=$game_id;?></h1>
<table border='1'>
<tr>
	<td>Game #</td>
	<td>Start Date</td>
	<td>Moderator</td>
	<td>Title</td>
	<td>Players</td>
</tr>
<tr>
<?php
$result = mysql_query("select number, start_date, title from Games where id=$game_id");
$game_data = mysql_fetch_array($result);
print "<td>".$game_data['number']."</td>\n";
print "<td>".$game_data['start_date']."</td>\n";
print "<td>";
$result = mysql_query("select name from Moderators, Users where Moderators.user_id = Users.id and Moderators.game_id = $game_id");
while ( $mod_name = mysql_fetch_array($result) ) {
print $mod_name[0]."<br />";
}
print "</td>\n";
print "<td>".$game_data['title']."</td>\n";
print "<td>";
$result = mysql_query("select name from Players, Users where Players.user_id = Users.id and Players.game_id = $game_id order by Users.name");
while ( $player = mysql_fetch_array($result) ) {
print $player[0]."<br />";
}
$num_players = mysql_num_rows($result);
print "($num_players Players)";
print "</td>\n";
?>
</tr>
</table>
</center>
</body>
</html>
<?php
} elseif ( isset($_POST['newgame']) ) {
?>
<html>
<head>
<title>New Game Confirm</title>
</head>
<body>
<center>
<form name='add_game' action='<?=$_SERVER['PHP_SELF'];?>' method="post">
<!--
<form name='add_game' action='../controler/info.php' method="post">
-->
<h1>New Werewolf Game Confimation Page</h1>
<table border='1'>
<tr>
<td rowspan='3'>Game Data:</td>
  <td>Start Date:</td>
    <td><?=$_POST['start_date'];?></td>
</tr>
<tr>
  <td>Title:</td>
    <td><?=$_POST['title'];?></td>
</tr>
<tr>
  <td>Thread id:</td>
    <td><?=$_POST['thread_id'];?></td>
</tr>
<tr>
<td>Moderator:</td>
  <td> </td>
  <td><?php  
foreach ( $_POST['moderator'] as $id ) {
$result = mysql_query("select name from Users where id=$id");
$name = mysql_fetch_array($result);
print "$name[0]<br />";
mysql_free_result($result);
}
?></td>
</tr>
<tr>
<td rowspan='2'>Players:</td>
  <td>Please select from the list:</td>
  <td><?php
  $count=0;
foreach ( $_POST['old_players'] as $id ) {
$result = mysql_query("select name from Users where id=$id");
$name = mysql_fetch_array($result);
print "$name[0]<br />";
$count++;
mysql_free_result($result);
}
print "($count Players)";
?></td>
</tr>
<tr>
  <td>New Players:</td>
  <td><?=$_POST['new_players'];?></td>
</tr>
</table>
<br />
<input type='hidden' name='start_date' value='<?=$_POST['start_date'];?>' />
<input type='hidden' name='title' value='<?=$_POST['title'];?>' />
<input type='hidden' name='thread_id' value='<?=$_POST['thread_id'];?>' />
<?php
$count = 0;
foreach ( $_POST['moderator'] as $id ) {
print " <input type='hidden' name='moderator[$count]' value='$id' />\n";
$count++;
}
$count = 0;
foreach ( $_POST['old_players'] as $id ) {
print " <input type='hidden' name='old_players[$count]' value='$id' />\n";
$count++;
}
?>
<input type='hidden' name='new_players' value='<?=$_POST['new_players'];?>' />
<input type='submit' name='confirm' value='Confirm New Game' />
</form>
</center>
</body>
</html>
<?php
} else {
?>
<html>
<head>
<title>New Werewolf Game</title>
</head>
<body>
<center>
<form name="add_game" action="<?=$_SERVER['PHP_SELF'];?>" method="post">
<!--
<form name="add_game" action="../controler/info.php" method="post">
-->
<h1>New Werewolf Game Page</h1>
<p>This page will be used to allow me to enter new games into the database.  Eventually the code here can be modified to allow Moderators to add their own games to the database.</p>
<table border='1'>
<tr>
<td rowspan='3'>Game Data:</td>
  <td>Start Date:</td>
    <td><input type='text' name='start_date' value='yyyy-mm-dd'></td>
</tr>
<tr>
  <td>Title:</td>
    <td><input type='text' name='title' value=''></td>
</tr>
<tr>
  <td>Thread id:</td>
    <td><input type='text' name='thread_id' value=''></td>
</tr>
<tr>
<td>Moderator:</td>
  <td>Please select from the list:</td>
  <td><select name='moderator[]' size='4' multiple>
<?php
$result = mysql_query("select * from Users order by name");
while ( $Users = mysql_fetch_array($result) ) { 
  print "<option value='".$Users['id']."' />".$Users['name']."\n";
}
mysql_free_result($result);
?>
  </select></td>
</tr>
<tr>
<td rowspan='2'>Players:</td>
  <td>Please select from the list:</td>
  <td><select name='old_players[]' size='12' multiple>
<?php
$result = mysql_query("select * from Users order by name");
while ( $Users = mysql_fetch_array($result) ) {
  print "<option value='".$Users['id']."' />".$Users['name']."\n";
}
mysql_free_result($result);
?>
  </select></td>
</tr>
<tr>
  <td>New Players:<br />seperate by a ,</td>
  <td><input type='text' name='new_players' value='' rows='2'></td>
</tr>
</table>
<br />
<input type='submit' name='newgame' value='Submit New Game' />
</form>
</center>
</body>
</html>
<?php
}
?>
