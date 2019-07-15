<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/bgg.php";
include_once "../php/common.php";
include_once "../menu.php";

$cache = init_cache();

dbConnect();

$sql = sprintf("select name, id from Users where name=%s",quote_smart($_REQUEST['username']));
$result = mysql_query($sql);
$user = mysql_fetch_array($result);

#checkLevel($level,1);

?>
<html>
<head>
<title> <?=$user['name']?> and Social Networks</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu(); ?>
<div style='padding:10px;'>
<h1>Where else you can find <?=$user['name']?></h1>
<table class='forum_table'>
<tr><th>Type</th><th>Site</th><th><?=$user['name']?>'s Info</th></tr>
<?php
$sql = sprintf("select * from Social_sites, Social_users where Social_sites.id=Social_users.site_id and Social_users.user_id=%s order by category, site_name",quote_smart($user['id']));
$result = mysql_query($sql);
while ( $site = mysql_fetch_array($result) ) {
  print "<tr>";
  print "<td>".$site['category']."</td>";
  $a_start = "";
  $a_end = "";
  if ( $site['url'] != "" ) {
    $a_start = "<a href='".$site['url']."'>";
    $a_end = "</a>";
  }
  print "<td>$a_start".$site['site_name']."$a_end</td>";
  if ( $site['link'] != "" ) {
    $link = preg_replace("/<userinfo>/",$site['user_info'],$site['link']); 
    $userinfo = "<a href='$link'>".$site['user_info']."</a>";
  } else {
    $userinfo = $site['user_info'];
  }
  print "<td>$userinfo</td>";
  print "</tr>";
}
?>
</table>
<?php
if ( $user['id'] == $uid ) {
?>
Go to <a href='/social'>the full list</a> to add new information.
<?php
}
?>
<br />
<h5>Please <a mailto='cassandrap.project@gmail.com'>let us know</a> if any of the links don't work properly.</h5>
</div>
</body>
</html>
