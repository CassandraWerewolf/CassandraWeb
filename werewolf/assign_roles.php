<?php

include "php/accesscontrol.php";
include_once "php/db.php";

dbConnect();

$game_id = $_REQUEST['game_id'];

if ( isset($_POST['submit']) ) {
  $sql = sprintf("select user_id from Players where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $players = array();
  while ( $row = mysql_fetch_array($result) ) {
    $players[] = $row['user_id'];
  }
  $roles = array();
  for ( $i=0; $i<$_POST['num'];$i++ ) {
    $roles[] = $_POST['role_'.$i];
  }
  shuffle($players);
  shuffle($roles);
  for ( $i=0; $i<$_POST['num'];$i++ ) {
    for ( $j=0; $j<$_POST['num']; $j++ ) {
      if ( $roles[$i] == $_POST['role_'.$j] && $_POST['hide_'.$j] == "on" ) {
	    $hide = true;
		break;
	  } else {
        $hide = false;
	  }
	}
	if ( $hide ) {
      $sql = sprintf("select mod_comment from Players where user_id=%s and game_id=%s",$players[$i],quote_smart($game_id));
	  $result = mysql_query($sql);
	  $mod_comment = mysql_result($result,0,0);
	  $sql = sprintf("update Players set mod_comment=%s where user_id=%s and game_id=%s",quote_smart($mod_comment." ".$roles[$i]),$players[$i],quote_smart($game_id));
	  $result = mysql_query($sql);
	} else {
      $sql = sprintf("update Players set role_name=%s where user_id=%s and game_id=%s",quote_smart($roles[$i]),$players[$i],quote_smart($game_id));
	  $result = mysql_query($sql);
	}
  }
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
$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$game = mysql_fetch_array($result);

$sql = sprintf("select role_name from Players where game_id=%s order by role_name",quote_smart($game_id));
$result = mysql_query($sql);
$num_players = mysql_num_rows($result);
?>
<html>
<head>
<title>Random Role Assignment</title>
</head>
<body>
<form name='roles' method='post' action='<?=$_SERVER['PHP_SELF'];?>'>
<input type='hidden' name='game_id' value='<?=$game_id;?>' />
<input type='hidden' name='num' value='<?=$num_players;?>' />
<table class='forum_table'>
<tr><th>Enter the Names of all Roles</th><th>hide</th></tr>
<?php
$i=0;
while ( $player = mysql_fetch_array($result) ) {
  print "<tr><td align='center'><input type='text' name='role_$i' value='".$player{'role_name'}."' /></td><td><input type='checkbox' name='hide_$i' /></td></tr>\n";
  $i++;
}
?>
<tr><td align='center' colspan='2'><input type='submit' name='submit' value='submit' /></td></tr>
</table>
</form>
<p>If a role is a hidden role<br />click the "hide" checkbox.<br />
<span align='right'><a href='javascript:close("control_space")'>[close]</a></span></p>
</body>
</html>
