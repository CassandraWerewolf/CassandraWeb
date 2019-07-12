<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "game_page_functions.php";
include_once "timezone_functions.php";
include_once "menu.php";

dbConnect();

# Process any Submitted forms
if ( isset($_POST['submit_goa']) ) {
  goa_submit($_POST);
}
if ( isset($_POST['submit_assign_roles']) ) {
  assign_roles_submit($_POST);
}

$thread_id = $_REQUEST['thread_id'];
if ($thread_id == "" ) { error("No Thread id Given."); }

# Get Game info
$game = get_game_info($thread_id,"thread");
list ($status, $subthread) = get_game_status($game['status'],$game['parent_game_id']);
$num_chats = get_game_chat_status($game['id']);

# Find out if the person viewing is the moderator.
$moderator = is_moderator($uid,$game['id']);

# Find out if the person viewing is a player.
$isplayer = is_player($uid,$game['id']);

# Find out if person viewing should have edit abilities.
$edit = false;
if ( $moderator || ($level <= 2 && $status == 'Finished') ) {
  $edit = true;
}

if ( $game['id'] == "" || $game['id'] == 0 )  {$game['title'] = "Invalid Game";}

$css = "<link rel='stylesheet' type='text/css' href='$domain/hint.css'>\n";
$javascript .= "<script language='javascript'>\n";
$javascript .= "<!--\n";
$javascript .= "var domain = '$domain'\n";
$javascript .= "var game_id = '".$game['id']."'\n";
$javascript .= "var thread_id = '".$game['thread_id']."'\n";
$javascript .= "var currentStatus = '".$game['status']."'\n";
$javascript .= "//-->\n";
$javascript .= "</script>\n";
$javascript .= "<script src='$domain/hint.js'></script>\n";
$javascript .= "<script src='$domain/game_page.js'></script>\n";
if ( $edit )  {
# $javascript .= "<script src='$domain/edit_game.js'></script>\n";
# $javascript .= "<script src='$domain/mod_control.js'></script>\n";
  $javascript .= "<script src='$domain/validation.js'></script>\n";
}

$extra = $css.$javascript;
print page_header($game['title']."- New",$extra);

?>
<div id='divDescription' class='clDescriptionCont'>
<!--Empty Div used for hint popup-->
</div>
<?php

print "<h1>";
$content = show_name($game['id']);
print create_edit_div($edit,'name_div',"Click to Change Name",'get_edit_form("name_form")',$content);
print "</h1>";

?>
<table border='0'> <!--  This table is purely for layout-->
<tr><td valign='top' width='50%'>
<!-- Table with Main Game Information -->
<?php print create_game_info_table($edit,$status,$subthread,$game['id']); ?>
</td>
<td valign='top'>
<!-- Table for Mod Controls -->
<?php
if ( $edit ) {
  if ( $moderator && ($status != "Finished") ) {
    print create_mod_controls($game['id'],$status,$num_chats);
  }
  print create_edit_area();
}
?>
</td>
</tr>
<table>

<?php
$sql = sprintf("select max(article_id) as a_id from Posts where game_id=%s",$game['id']);
$result = mysql_query($sql);
if ( $result ) { $article_id = mysql_result($result,0,0); }
print "<br /><a id='game_link' href='http://www.boardgamegeek.com/thread/$thread_id'>Go to Game Thread</a>\n";
if ( $status != "Sign-up" ) {
  print ": <a href='http://www.boardgamegeek.com/article/$article_id#$article_id'>Last retrieved post</a>\n";
}
print "<br />\n";
if ( $game['auto_vt'] != "No" ) {
  print "<a href='$domain/game/$thread_id/tally'>Vote Tally</a> : <a href='$domain/game/$thread_id/tally_inverted'>Inverted Tally</a> : <a href='$domain/game/$thread_id/votes'>Vote Log</a><br />\n";
}
if ( $chats > 0 ) {
  print "<a href='${posts}chat'>Cassandra Communication System</a><br />";
}
?>
<a href='javascript:pm_players()'>GeekMail Players</a><br />
<div style='position:absolute; visibility:hidden; background-color:white; border:1px solid black;' id='PM_div'></div>
<table>
<tr><td>
<div id='player_table'>
<?php
print createPlayer_table($edit,$game['id']);

print "</div>\n";
print "</td>\n";
print "<td valign='top'>\n";
# Show any Wolfy awards the game has won.
$sql = sprintf("select * from Wolfy_games, Wolfy_awards where Wolfy_games.award_
id=Wolfy_awards.id and game_id=%s order by id, year",$game['id']);
$result = mysql_query($sql);
if ( $result ) {
  $num_awards = mysql_num_rows($result);
} else {
  $num_award = 0;
}
if ( $num_awards > 0 ) {
  print "<table class='forum_table'><tr><th>Wolfy Awards</th></tr>\n";
while ( $award = mysql_fetch_array($result) ) {
  print "<tr><td><a href='http://www.boardgamegeek.com/article/".$award['award_post']."#".$award['award_post']."'>".$award['award']." (".$award['year'].")</a></td></tr>\n";
}
  print "</table>\n";
}
print "</td></tr></table>\n";

$sql = "select distinct Posts.user_id, name from Posts, Users where Posts.user_id=Users.id and Posts.game_id='".$game['id']."' and Posts.user_id not in ( select user_id from Players where game_id='".$game['id']."') and Posts.user_id not in ( select user_id from Moderators where game_id='".$game['id']."') and Posts.user_id not in ( select replace_id from Replacements where game_id='".$game['id']."') order by name";
$result = mysql_query($sql);
$num_row = mysql_num_rows($result);
if ( $num_row > 0 ) {
print "<br />Non-Players who posted<br />\n";
print "<table class='forum_table'>\n";
while ( $row = mysql_fetch_array($result) ) {
  $sql2 = "select count(*) from Posts where game_id='".$game['id']."' and user_id='".$row['user_id']."'";
  $result2 = mysql_query($sql2);
  $num_post = mysql_result($result2,0,0);
  print "<tr><td>".get_player_page($row['name'])."<a href='$posts".$row['name']."'>($num_post posts)</a></td></tr>\n";
}
print "</table>\n";
}
?>
<br />
<table>
<tr><td><h3>Player Time Zone Chart</h3></td>
<td><?php print timezone_changer(); ?></td></tr>
</table>
<div id='tz_div'>
<?php print timezone_chart("",$game['id']); ?>
</div>
<?php
timezone_js();
?>
<script language='javascript'>
setHint()
</script>
<?php

print page_footer();
?>
