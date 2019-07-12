<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";
dbConnect();

$game_id = $_REQUEST['game_id'];

$sql = sprintf("select id, name from Users_game_all, Users where Users_game_all.user_id = Users.id and game_id=%s order by name",quote_smart($game_id));
$result = mysql_query($sql);
while ( $player = mysql_fetch_array($result) ) {
  $players[$player['id']] = $player['name'];
}

if ( isset($_POST['submit']) ) {
  $to = "";
  foreach ( $players as $id => $name ) {
    if ( in_array($id,$_POST) || isset($_POST['all']) ){
      if ( $to != "" ) { $to .= ", "; }
      $to .= $name;
    }
  }
?>
<html>
<head>
<title>PM Players</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php print geekmail_form($to); ?>
</body>
</html>
<?php
exit;
}
?>
<form name='pm_player' action='<?=$_SERVER['PHP_SELF'];?>' method='POST'>
<table border='0' class='forum_table' >
<input type='hidden' name='game_id' value='<?=$game_id;?>' />
<?php
foreach ( $players as $id => $name ) {
  print "<tr>";
  print "<td><input type='checkbox' name='".$name."' value='".$id."' /></td>";
  print "<td>".$name."</td>";
  print "</tr>\n";
}
?>
<tr><td><input type='checkbox' name='all' /></td><td><b>ALL</b></td></tr>
<tr><td colspan='2'><input type='submit' name='submit' value='PM Selected Players' /></td></tr>
</table>
</form>
<span align='right'><a href='javascript:close("PM_div")'>[close]</a></span>
