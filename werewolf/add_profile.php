<?php
//This page will allow a player to add a profile for the first time.

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";

dbConnect();

$sql = sprintf("select * from Bio where user_id = %s",quote_smart($uid));
$result = mysql_query($sql);
$num = mysql_num_rows($result);
if ( $num != 0 ) {
?>
<html>
<head>
<script language='javascript'>
<!--
location.href = '/profile/<?=$username;?>'
//-->
</script>
</head>
<body>
Please return to <a href='/profile/<?=$username;?>'> Your Profile Page</a>
</body>
</html>
<?php
exit;
}

$sql_col = "show full columns from Bio";
$result_col = mysql_query($sql_col);

if ( isset($_POST['submit']) ) {
  $sql = sprintf("insert into Bio (user_id) values (%s)",quote_smart($_POST['user_id']));
  $result = mysql_query($sql);
  while ( $col = mysql_fetch_array($result_col) ) {
    if ( $col['Field'] == "user_id" ) { continue; }
	if ( $col['Field'] == "b_date" && $_POST['b_date'] == "yyyy-mm-dd" ) { continue; }
	if ( $_POST[$col['Field']] == "" ) { continue; }
    $sql = sprintf("update Bio set %s=%s where user_id=%s",$col['Field'],quote_smart($_POST[$col['Field']]),quote_smart($_POST['user_id']));
	$result = mysql_query($sql);
  }
?>
<html>
<head>
<script language='javascript'>
<!--
location.href = '/profile/<?=$username;?>'
//-->
</script>
</head>
<body>
Please return to <a href='/profile/<?=$username;?>'> Your Profile Page</a>
</body>
</html>
<?php
exit;
}
?>
<html>
<head>
<title>Add a Profile for <?=$username;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu();?>
<center>
<h1>Add a profile for <?=$username;?></h1>
<p>All fields are optional.  Only those fields that you add data for will be shown<br />to other werewolf players. The ones with *'s are ones that we would like to<br />be filled out for various other Cassandra utilities we would like to add.</p>
<form method='post' action='<?=$_SERVER['PHP_SELF'];?>'>
<table class='forum_table'>
<input type='hidden' name='user_id' value='<?=$uid;?>' />
<?php
while ( $col = mysql_fetch_array($result_col) ) {
  if ( $col['Comment'] == "" ) { continue; }
  print "<tr><td>".$col['Comment'];
  $star = "";
  switch ( $col['Field'] ) {
    case rl_name:
	case location:
	case mbti:
	case email_addr:
      print "</td><td><input type='text' size='36' name='".$col['Field']."' value='' /></td></tr>\n";
    break;
	case name_origin:
	case free_hours:
	case job:
	case family:
	case religion:
	case comments:
	  print "</td><td><textarea cols='30' name='".$col['Field']."' value=''></textarea></td></tr>\n";
	break;
    case b_date:
      print "(will be shown as age)</td><td><input type='text' size='10' name='".$col['Field']."' value='yyyy-mm-dd' /></td></tr>\n";
    break;
	case gender:
	  print "*";
	  print "</td><td><select name='".$col['Field']."'><option /><option />M<option />F</select></td></tr>";
	break;
	case time_zone:
	  print "*";
	  print "</td><td><select name='".$col['Field']."'><option value=''/>\n";
      $sql_tz = "select zone, concat('(GMT',if(GMT>0,' +',''),if(GMT=0,'',concat(if(GMT<0,' ',''),GMT)),') ',description) as text from Timezones order by zone DESC";
      $result_tz = mysql_query($sql_tz);
	  while ( $tz = mysql_fetch_array($result_tz) ) {
        print "<option value='".$tz['zone']."' />".$tz['text']."\n";
	  }
	  print "</select></td></tr>\n";
	break;
  }
}
?>
<tr><td align='center' colspan='2'><input type='submit' name='submit' value='submit' /></td></tr>
</table>
</form>
</center>
</body>
</html>
