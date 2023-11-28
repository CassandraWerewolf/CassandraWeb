<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/bgg.php";
include_once "../php/common.php";
include_once "edit_functions.php";
include_once "../menu.php";

$cache = init_cache();

$mysql = dbConnect();

#checkLevel($level,1);

$template_id = $_REQUEST['template_id'];

if ( $template_id == "" ) {
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

# Check to see if Edits are being made.
if ( isset($_POST['submit']) ) {
  if ( isset($_POST['name']) ) {
    update_name($_POST['name'],$template_id);
  }
  if ( isset($_POST['info'])) {
    update_info($_POST,$template_id); 
  }
  if ( isset($_POST['my_rules']) ) {
    update_rules(stripslashes($_POST['my_rules']),$template_id);
  }
  if ( isset($_POST['count'] ) ) {
    update_roles($_POST['count'],$_POST,$template_id);
  }
  error("Template has been changed");
}



# Get Template Information
$sql = sprintf("select * from AM_template where id=%s",quote_smart($template_id));
$result = mysqli_query($mysql, $sql);
$template = mysqli_fetch_array($result);

$owner = false;
$edit = false;
if ( $template['owner_id'] == $uid || $level == 1 ) { $owner = true; }
if ( $owner && $template['mode'] == "Edit" ) { $edit = true; }

?>
<html>
<head>
<title>AutoMod Template: <?=$template['name'];?></title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<script language='javascript' src='/automod/edit_script.js'></script>
</head>
<body>
<?php display_menu(); ?>
<a href='/automod'>Return to Automod Home</a><br />
<div style='padding:10px;'>
<form method='post' action='<?=$_SERVER['PHP_SELF'];?>'>
<input type='hidden' name='template_id' value='<?=$template_id;?>' />
<table border='0'>
<tr><td valign='top'>
<?php
if ( $edit ) {
  print "<a href='javascript:setup_edit(\"create_title\",\"title_td\",\"$template_id\")'>";
  print "<img src='/images/edit.png' border='0' /></a>";
}
print "</td><td id='title_td'>\n";
print create_title($template_id);
?>
</td></tr>
<tr><td>
</td><td>
<table class='forum_table' width='75%'>
<tr>
<td><b>Owner:</b></td><td><?=get_player_page($template['owner_id']);?></td>
<td><b>Mode:</b></td><td>
<?php
if ( $owner ) {
  print "<table width='100%'><tr><td>";
  print mode_select($template_id);
  print " <img src='/images/moreinfo.gif' onMouseOver='document.getElementById(\"mode_info\").style.visibility=\"visible\";' onMouseOut='document.getElementById(\"mode_info\").style.visibility=\"hidden\";' />";
  print "<div id='mode_info' style='visibility:hidden; position:absolute; background-color:white; border:solid black 1px;'>Edit: Must be in edit mode in order to edit the template.<br />Testing: When in testing only you can start a game using this ruleset.<br />Active: Allow anybody to start a game with this ruleset.</div>";
  print "</td><td align='right'>";
  if ( $template['mode'] == "Edit" ) {
    print "<input type='button' name='delete' value='Delete' onClick='delete_me(\"$template_id\")' />";
  }
  print "</td></tr></table>";
  print "</td></tr>";
} else {
  print $template['mode'];
}
?>
</td>
</tr>
</table>
<br />
</td></tr>
<tr><td valign='top'>
<?php
if ( $edit ) {
  print "<a href='javascript:setup_edit(\"create_info_table\",\"info_td\",\"$template_id\")'>";
  print "<img src='/images/edit.png' border='0' /></a>";
}
print "</td><td id='info_td'>";
print create_info_table($template_id); 
?>
<br />
</td></tr>
<tr><td valign='top'>
<?php
if ( $edit ) {
  print "<a href='javascript:setup_edit(\"create_role_table\",\"role_td\",\"$template_id\")'>";
  print "<img src='/images/edit.png' border='0'/></a>";
}
print "</td><td id='role_td'>";
print create_role_table($template_id); 
?>
</td></tr>
<tr><td valign='top'>
<?php
if ( $edit ) {
  print "<a href='javascript:setup_edit(\"get_ruleset\",\"ruleset\",\"$template_id\")'>";
  print "<img src='/images/edit.png' border='0' /></a>";
}
?>
</td><td>
<table class='forum_table'>
<tr><th>Ruleset</th></tr>
<tr><td>
<pre id='ruleset' width='100'>
<?php print get_ruleset($template_id); ?>
</pre>
</td></tr>
</table>
<br />
</td></tr>
</table>
</form>
<h2>Games Played Statistcs</h2>
<span>Note: Rules may have changes between playings.  This just shows all the games with this automod template id.</span>
<table class='forum_table'>
<tr><th>Game</th><th>Status</th><th>Winner</th></tr>
<?php
$sql = sprintf("select id, status, winner from Games where automod_id=%s",quote_smart($template_id));
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) == 0 ) {
  print "<tr><td colspan='3'>No Games played with this template.</td></tr>";
} else {
while ( $game = mysqli_fetch_array($result) ) {
  print "<tr>";
  print "<td>".get_game($game['id'])."</td>";
  print "<td>".$game['status']."</td>";
  print "<td>".$game['winner']."</td>";
  print "</tr>\n";
}
}
?>
</table>
</div>
</body>
</html>
