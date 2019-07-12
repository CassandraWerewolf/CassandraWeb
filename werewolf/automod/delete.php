<?php

include_once ("../php/accesscontrol.php");
include_once ("../php/db.php");

dbConnect();

$template_id = $_REQUEST['template_id'];

# Check to make sure player deleting is the owner of the template.

$sql = sprintf("select owner_id from AM_template where id=%s",quote_smart($template_id));
$result = mysql_query($sql);
$owner_id = mysql_result($result,0,0);

if ( $uid == $owner_id || $level == 1 ) {
  $sql = sprintf("delete from AM_template where id=%s",quote_smart($template_id));
  $result = mysql_query($sql);
  $file = "rulesets/${template_id}_ruleset.txt";
  if ( file_exists($file) ) {
    unlink($file);
  }
?>
<html>
<head>
<script language='javascript'>
<!--
location.href='/automod';
//-->
</script>
</head>
<body>
<a href='/automod'>Go to Automod home page</a>
</body>
</html>
<?php
} else {
  error ("You can't delete someone elses template");
}
?>
