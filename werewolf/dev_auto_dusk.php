<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";

$mysql = dbConnect();

$game_id = $_REQUEST['game_id'];

if ( ! is_moderator($uid,$game_id) ) {
  error("You are not a moderator of this game.");
}

if ( isset($_POST['submit']) ){
  $sql = sprintf("show columns from Auto_dusk");
  $result = mysqli_query($mysql, $sql);
  while ( $table = mysqli_fetch_array($result) ) {
    if ( $table['Field'] == "game_id" ) { continue; }
	if ( isset($_POST[$table['Field']]) ) {
      $auto_dusk[$table['Field']] = 1;
	} else {
      $auto_dusk[$table['Field']] = 0;
	}
  }
  $sql = sprintf("replace Auto_dusk (game_id, mon, tue, wed, thu, fri, sat, sun) values(%s,%s,%s,%s,%s,%s,%s,%s)",quote_smart($game_id),quote_smart($auto_dusk['mon']),quote_smart($auto_dusk['tue']),quote_smart($auto_dusk['wed']),quote_smart($auto_dusk['thu']),quote_smart($auto_dusk['fri']),quote_smart($auto_dusk['sat']),quote_smart($auto_dusk['sun']));
  $result = mysqli_query($mysql, $sql);
  error("Auto Dusk has been modified.");
}
?>
<html>
<head>
<title>Auto Dusk</title>
</head>
<body>
<form method='POST' action='<?=$_SERVER['PHP_SELF'];?>'>
<table class='forum_table'>
<tr>
<th>Mon</th>
<th>Tue</th>
<th>Wed</th>
<th>Thu</th>
<th>Fri</th>
<th>Sat</th>
<th>Sun</th>
<tr>
<?php
$sql = sprintf("select * from Auto_dusk where game_id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) == 1 ) {
$auto_dusk = mysqli_fetch_array($result);
}

$sql = sprintf("show columns from Auto_dusk");
$result = mysqli_query($mysql, $sql);
while ( $table = mysqli_fetch_array($result) ) {
  if ( ! isset($auto_dusk[$table['Field']]) ){
    if ( $table['Field'] == "game_id" ) {
      $auto_dusk[$table['Field']] = $game_id;
	} else {
      $auto_dusk[$table['Field']] = 0;
	}
  }
  if ( $table['Field'] == "game_id" ) {
    print "<input type='hidden' name='".$table['Field']."' value='".$auto_dusk[$table['Field']]."' />\n";
  } else {
    print "<td><input type='checkbox' name='".$table['Field']."' ";
	if ( $auto_dusk[$table['Field']] == 1 ) {
      print "checked='checked' ";
	}
	print "/></td>\n";
  }
}
?>
</tr>
<tr><td colspan='7' align='center' ><input type='submit' name='submit' value='Submit'/></td></tr>
</table>
<span align='right'><a href='javascript:close("control_space")'>[close]</a></span>
</form>
</body>
</html>
