<?php

include_once "../php/accesscontrol.php";
checkLevel($level,2);

include_once "../php/db.php";
dbConnect();

include_once "../php/common.php";
include_once "../menu.php";

if ( isset($_POST['submit']) ) {
  $sql = sprintf("select id from Users where name=%s",quote_smart($_POST['player_name_wotw']));
  $result = mysql_query($sql);
  $player_id = mysql_result($result,0,0);
  $sql = sprintf("insert into Wotw (id, user_id, num, start_date, thread_id) values (null, %s, %s, %s, %s)",quote_smart($player_id),quote_smart($_POST['num']),quote_smart($_POST['start_date']),quote_smart($_POST['thread_id']));
  $result = mysql_query($sql);

?>
<html>
<head>
<script language='javascript'>
<!--
window.location.href='/wotw.php'
//-->
</script>
</head>
<body>
If page does not re-direct <a href='/wotw.php'>click here</a>.
</body>
</html>
<?php
exit;
}

print page_header("Add Wolf of the Week");
?>
<h1>Add Wolf of the Week</h1>
<form method='post' action='<?=$_SERVER['PHP_SELF']?>'>
<table class='forum_table'>
<tr><td>User</td><td><?php print player_autocomplete_form("wotw"); print player_autocomplete_js("wotw")?></td></tr>
<tr><td>Num</td><td><input type='text' size='3' name='num' /></td></tr>
<tr><td>Start Date</td><td><input type='text' name='start_date' /></td></tr>
<tr><td>Thread ID</td><td><input type='text' name='thread_id' /></td></tr>
<tr><td colspan='2'><input type='submit' value='submit' name='submit' /></td></tr>
</table>
</form>
<?php
print page_footer();
?>
