<?php
include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "menu.php";

$mysql = dbConnect();

?>
<html>
<head>
<title>Wolf of the Week</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php print display_menu(); 
if ($level == 1 || $level == 2)
{
  if ( isset($_POST['submit']) ) {
	$sql = sprintf("select id from Users where name=%s",quote_smart($_POST['player_name_wotw']));
	$result = mysqli_query($mysql, $sql);
	$player_id = mysqli_result($result,0,0);
	$sql = sprintf("insert into Wotw (id, user_id, num, start_date, thread_id) values (null, %s, %s, %s, %s)",quote_smart($player_id),quote_smart($_POST['num']),quote_smart($_POST['start_date']),quote_smart($_POST['thread_id']));
	$result = mysqli_query($mysql, $sql);
  }
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
}
?>

<div style='padding-left:10px;'>
<h1>Wolf of the Week</h1>
<table class='forum_table'>
<tr>
<th>Week #</th>
<th>Start Date</th>
<th>Wolf</th>
</tr>
<?php
$sql = "select user_id, num, date_format(start_date,'%b %e, %Y') as start_date, thread_id from Wotw order by num desc";
$result = mysqli_query($mysql, $sql);
while ( $row = mysqli_fetch_array($result) ) {
  print "<tr><td align='center'><a href='http://www.boardgamegeek.com/thread/".$row['thread_id']."'>".$row['num']."</a></td>\n";
  print "<td align='center'><a href='http://www.boardgamegeek.com/thread/".$row['thread_id']."'>".$row['start_date']."</a></td>\n";
  print "<td>";
  print get_player_page($row['user_id']);
  print "</td>\n</tr>\n";
}
?>
</table>
</div>
</body>
</html>

