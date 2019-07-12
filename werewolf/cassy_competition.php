<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";

dbConnect();

#checkLevel($level,2);

$order = "number";
if ( isset($_REQUEST['order']) ) { $order = $_REQUEST['order']; }

?>
<html>
<head>
<title>Cassandra Competition Main Page</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php display_menu(); ?>
<div style='padding:10px;'>
<h1>Cassandra Competition Main Page</h1>
<table class='forum_table'>
<tr><th>Team</th><th>Score</th></tr>
<?php
$sql = sprintf("select team, count(*) as score from CC_info, CC_players where CC_info.user_id=CC_players.user_id and CC_info.challenger_id is null group by CC_players.team order by score desc");
$result = mysql_query($sql);
while ( $row = mysql_fetch_array($result) ) {
  print "<tr>";
  print "<td>".$row['team']."</td>";
  print "<td align='center'>".$row['score']."</td>";
  print "</tr>\n";
}
?>
</table>
<br />
<table class='forum_table'>
<?php
$here = "/cassy_competition.php";
$format = 'Can\\\'t be challenged until %a. %b %e, %Y at %l:%i%p';
$sql = sprintf("select Games.id, number, title, thread_id, user_id, if(timestampdiff(HOUR,claim_time,now())>=72,if(challenger_id is null,if(timestampdiff(HOUR,claim_time,now())>=96,'Can be challenged by anyone','Can be challenged by other teams'), challenger_id),date_format(date_add(claim_time, interval 3 day),'%s')) as status from Games left join CC_info on Games.id=CC_info.game_id where Games.status = 'Finished' and Games.automod_id is null and end_date < '2007-08-06 12:31:29' order by %s",$format,quote_smart($order));
$result = mysql_query($sql);
$num_games = mysql_num_rows($result);
print "<tr><th><a href='$here?order=number'>Game ($num_games)</a></th><th><a href='$here?order=user_id'>Claimed by</a></th><th><a href='$here?order=status'>Challenge Status</a></tr>\n";
while ( $row = mysql_fetch_array($result)) {
  print "<tr>";
  #print "<td>".$row['number'].") <a href='/cc_game/".$row['thread_id']."'>".$row['title']."</a></td>";
  print "<td>".$row['number'].") <a href='/game/".$row['thread_id']."'>".$row['title']."</a></td>";
 print "<td>".get_player_page($row['user_id'],false)."</td>";
 print "<td>";
 if ( is_numeric($row['status']) ) {
   print "Challenged by ".get_player_page($row['status'],false); 
 } else {
   print $row['status'];
 }
 print "</td>";
 print "</td>";
}
?>
</div>
</body>
</html>
