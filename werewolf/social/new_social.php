<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../menu.php";

dbConnect();

if ( isset($_POST['submit']) ) {
  if ( $_POST['site_name'] == "" ) { error("You must have a site name."); }
  $sql = sprintf("insert into Social_sites (id, site_name, url, category, link)values ( null, %s, %s, %s, %s)",quote_smart($_POST['site_name']),quote_smart($_POST['url']),quote_smart($_POST['category']),quote_smart($_POST['link']));
  $result = mysql_query($sql);
?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/social/'
//-->
</script>
</head>
<body>
<a href="/social">Go To Social Page</a>
</body>
</html>
<?php
} else {
?>
<html>
<head>
<title>Add a New Social Site Connection</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php print display_menu(); ?>
<div style='padding:10px;'>
<h1>Add a New Social Site Connection</h1>
<form method='POST' action='<?=$_SERVER['PHP_SELF'];?>'>
<table class='forum_table'>
<tr><td><b>Site Name</b></td><td><input type='text' name='site_name'></input></td></tr>
<tr><td><b>Type of Connection</b></td><td>
<?php
 $list = get_enum_array('category','Social_sites');
 print create_dropdown('category','Other',$list)
?>
</td></tr>
<tr><td><b>URL</b></td><td><input type='text' name='url'></input>Make sure to include the http://</td></tr>
<tr><td><b>Personal Link</b> </td><td><input type='text' name='link'></input> Please put &lt;userinfo&gt; where the personal user info would go in the url.  </td></tr>
<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>
</table>
</form>
</div>
</body>
</html>
<?php
exit;
}
?>
