<?php
// This page will be used for players to input personal data - ie stuff that was in the "get to know your fellow ww player thread

include_once "../setup.php";

include ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/edit_profile_functions.php";
include_once ROOT_PATH . "/menu.php";


$here = "/";
$player = $_GET['player'];
$sql = sprintf("select id from Users where name=%s",quote_smart($player));
$result = mysql_query($sql);
$id = mysql_result($result,0,0);
$edit = false;
if ( $uid == $id ) { $edit = true; }
$sql_bio = sprintf ("select * from Bio where user_id=%s",$id);
$result_bio = mysql_query($sql_bio);

if ( $player == "" ) {
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

#If the player has edit abilities, check to see if a profile has already been created.  If not send the user to the add_profile.php page.
if ( $edit && mysql_num_rows($result_bio) == 0 ) {
?>
<html>
<head>
<title></title>
<script language='javascript'>
<!--
location.href='<?=$here;?>players/add_profile.php'
//-->
</script>
</head>
<body>
<p>Please go <a href='<?=$here;?>players/add_profile.php'>add your profile</a>.</p>
<?php
exit;
}

?>
<html>
<head>
<title>Profile for <?=$player;?></title>
<link rel='stylesheet' type='text/css' href='<?=$here;?>bgg.css'>
<link rel='stylesheet' type='text/css' href='<?=$here;?>hint.css'>
<script language='javascript'>
<!--
var player = '<?=$player;?>'
var player_id = '<?=$id;?>'
var myURL = '<?=$_SERVER['REQUEST_URI'];?>'
//-->
</script>
<?php
if ( $edit ) {
?>
<script src='<?=$here;?>color_picker.js'></script>
<script src='<?=$here;?>edit_profile.js'></script>
<script src='<?=$here;?>ajax.js'></script>
<script src='<?=$here;?>hint.js'></script>
<script src='<?=$here;?>validation.js'></script>
<?php
}
?>
</head>
<body>
<?php display_menu();?>
<h1>Profile for <?=$player;?></h1>
<?php
if ( mysql_num_rows($result_bio) != 1 ) {
?>
<p>This player has not created a profile page.</p>
<?php
} else {
$bio = mysql_fetch_array($result_bio);
?>
<div id='divDescription' class='clDescriptionCont'>
<!--Empty Div used for hint popup-->
</div>
<a href='<?=$here;?>player/<?=$player;?>'>Cassandra Stats</a><br />
<a href='http://boardgamegeek.com/user/<?=$player;?>'>BGG Profile</a><br />
<a href='<?=${here};?>social/user/<?=$player;?>'>Social Sites</a></br />

<?php
$wotw_sql = sprintf("select thread_id from Wotw where user_id=%s",quote_smart($id));
$wotw_result = mysql_query($wotw_sql);
if ( mysql_num_rows($wotw_result) == 1 ) {
  $wotw_thread = mysql_result($wotw_result,0,0);
  print "<a href='http://boardgamegeek.com/thread/".$wotw_thread."'>Wolf of the Week Thread</a><br />"; 
}
?>
<a href='<?=$here;?>send_geekmail.php?to=<?=$player;?>'>Send GeekMail</a>
<table width='100%'>
<tr><td valign='top' width='50%'>
<table class='forum_table' cellpadding='5'>
<?php
$sql_col = "show full columns from Bio";
$result_col = mysql_query($sql_col);
while ( $col = mysql_fetch_array($result_col)) {
  $field = $col['Field'];
  $comment = $col['Comment'];
  $hint_comment = $comment;
  switch($field) {
    case user_id:
	  continue 2;
	break;
    case mbti:
      $hint_comment = "MBTI"; 
	break;
	case b_date:
	  $comment = "Age";
	  $sql = sprintf("select TIMESTAMPDIFF(YEAR, b_date, CURDATE()) from Bio where user_id=%s",quote_smart($id));
      $result = mysql_query($sql);
	  $bio['b_date'] = mysql_result($result,0,0);
	break;
	case time_zone:
	  if ( $bio['time_zone'] != "" ) {
        $sql = sprintf("select concat('(GMT',if(GMT>0,' +',''),if(GMT=0,'',concat(if(GMT<0,' ',''),GMT)),') ',description) as text from Timezones where zone=%s",quote_smart($bio['time_zone']));
	    $result = mysql_query($sql);
	    $bio['time_zone'] = mysql_result($result,0,0);
	  }
	break;
  }
  if ( $edit || $bio[$field] != "" ) {
    print "<tr><th align='left'><div onMouseOver='show_hint(\"Click to Edit $hint_comment\")' onMouseOut='hide_hint()' onClick='edit_field(\"$field\")'>$comment</div></th><td id='${field}_td'>";
    print show_field($id,$field,$bio[$field]);
    print "</td></tr>\n"; 
  }
}
?>
</table>
</td>
<td valign='top'>
<?php
if ( $edit ) {
?>
<table class='forum_table'>
<tr><th>Edit</td><tr>
<tr><td><div id='edit_space' style='text-align:center'><?php clear_editSpace($player);?></div>
<img id='busy' style='visibility:hidden' src='<?=$here;?>images/ajax_busy.gif' />
</td></tr>
</table>
<?php
}
?>
</td>
</tr>
</table>
<?php
}
?>
<script language='javascript'>
setHint()
</script>
<table><td width='300' valign='top'>
<div style='border:solid 1px #b0b0b0; padding:5px;'>
<script language="javascript" src="http://www.boardgamegeek.com/jswidget.php?username=<?=$player;?>&numitems=5&header=1&text=title&images=small-fixed&show=recentplays&imagesonly=1&imagepos=left"></script></div></table>
</body>
</html>
