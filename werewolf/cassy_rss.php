<?php

include_once "php/db.php";

$mysql = dbConnect();

print "<?xml version='1.0'?>\n";
?>
<rss version="2.0">
	<channel>
		<title>BGG Werewolf Sign-up Games</title>
		<link>http://cassandrawerewolf.com</link>
		<description>A list of BGG Werewolf games that have been set to Sign-up mode on the Cassandra System</description>
		<language>en-us</language>

<?php

$sql = "select * from Games where status='Sign-up' order by id desc";
$result = mysqli_query($mysql, $sql);

while ( $game = mysqli_fetch_array($result) ) {
  print "<item>\n";
  print "<title> ".$game['title']."</title>\n";
  print "<link>http://cassandrawerewolf.com/game/".$game['thread_id']."</link>\n";
  print "<guid>http://cassandrawerewolf.com/game/".$game['thread_id']."</guid>\n";
  print "<description>";
  # Get Mod list 
  $sql2 = sprintf("select name from Users, Moderators where Users.id=Moderators.user_id and game_id=%s",quote_smart($game['id']));
  $result2 = mysqli_query($mysql, $sql2);
  $mod_num = mysqli_num_rows($result2);
  $count = 0;
  $modlist = "";
  while ( $mod = mysqli_fetch_array($result2) ) {
    if ( $count != 0  ) $modlist .= ", ";
    $modlist .= $mod['name'];
    $count++;
  }
  print "Moderator: $modlist&lt;br&gt;\n"; 
  print "Complexity: ".$game['complex']."&lt;br&gt;\n";
  print "Max Players: ".$game['max_players']."&lt;br&gt;&lt;br&gt;\n";
  print "Start Date: ".$game['start_date']."&lt;br&gt;&lt;br&gt;\n\n";
  print $game['description'];
  print "</description>\n";
  print "</item>\n\n";
} 

?>
	</channel>
</rss>
