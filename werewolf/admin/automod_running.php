<?php

include_once "../php/accesscontrol.php";
checkLevel($level,1);

include_once "../php/db.php";
$mysql = dbConnect();

include_once "../menu.php";

$sql = "select id, thread_id, title, status, automod_running from Games where automod_id is not null and status != 'Finished' order by status";
$result = mysqli_query($mysql, $sql);
?>
<html>
<head>
<title>Cassandra Running Automod Games</title>
<link rel='stylesheet' type='text/css' href='../assets/css/application.css'>
<script language='javascript'>
<!--
function reset_game(game_id) {
  agent.call('','reset_game','reload_page',game_id)
}

function reload_page() {
  location.href='<?=$SERVER['PHP_SELF'];?>'
}
//!-->
</script>
</head>
<body>
<?php display_menu();?>
<h1>Cassandra Running Automod Games</h1>
<p>
<?php
$sql_now = "select now()";
$result_now = mysqli_query($mysql, $sql_now);
$now = mysqli_result($result_now,0,0);
print "Now: $now";
?>
</p>
<table class='forum_table'>
<form>
<tr><th>Game ID</th><th>Title</th><th>Status</th><th>Running</th><th>Reset</th></tr>
<?php
while ( $game = mysqli_fetch_array($result) ) {
  print "<tr><td>".$game['id']."</td>";
  print "<td><a href='/game/".$game['thread_id']."'>".$game['title']."</a></td>\n";
  print "<td>".$game['status']."</td>";
  print "<td>".$game['automod_running']."</td>";
  print "<td><input type='button' name='reset' value='Reset' onClick='reset_game(\"".$game['id']."\")'></td>";
  print "</tr>\n";
}
?>
</form>
</table>
</body>
</html>

<?php

function reset_game($game_id) {
  $sql = sprintf("update Games set automod_running=null where id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  
  return;
}
?>
