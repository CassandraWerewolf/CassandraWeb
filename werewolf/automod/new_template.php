<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../menu.php";
include_once "edit_functions.php";

dbConnect();

if ( isset($_POST['submit'])) {
  $random_tinker = 0;
  $random_whitehat = 0;
  if ( isset($_POST['random_tinker']) ) { $random_tinker = 1; }
  if ( isset($_POST['random_whitehat']) ) { $random_whitehat = 1; }
  $sql = sprintf("insert into AM_template (id, owner_id, name, description, num_players, num_player_sets, role_reveal, random_n0, priest_type, random_tinker, random_whitehat, mode ) VALUES ( null, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'Edit')",quote_smart($uid),quote_smart($_POST['name']),quote_smart($_POST['description']),quote_smart($_POST['num_players']),quote_smart($_POST['num_player_sets']),quote_smart($_POST['role_reveal']),quote_smart($_POST['random_n0']),quote_smart($_POST['priest_type']),quote_smart($random_tinker),quote_smart($random_whitehat));
  $result = mysql_query($sql);
  $template_id = mysql_insert_id();
  if ( $template_id < 10 ) { $template_id = "0".$template_id; }
?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/automod/template/<?=$template_id;?>'
//-->
</script>
</head>
<body>
<a href="/automod/template/<?=$template_id;?>">Go To Template Page</a>
</body>
</html>
<?php
} else {
?>
<html>
<head>
<title>Create New Automod Template</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php print display_menu(); ?>
<div style='padding:10px;'>
<h1>Create New Automod Template</h1>
<form method='POST' action='<?=$_SERVER['PHP_SELF'];?>'>
<?php
print "<b>Template Name:</b> ".create_title('0',true,false);
print "<br /><br />";
print create_info_table('0',true);

?>
<p>All other information will be added after you have created the basic frame work.</p>
</form>
</div>
</body>
</html>
<?php
exit;
}
?>
