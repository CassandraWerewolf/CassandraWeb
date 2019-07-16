<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/bgg.php";
include_once "../php/common.php";
include_once "../menu.php";

$cache = init_cache();

dbConnect();

#checkLevel($level,1);

?>
<html>
<head>
<title>Werewolf and Social Networks</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu(); ?>
<div style='padding:10px;'>
<h1>Where else you can find us</h1>
<table class='forum_table'>
<tr><th>Type</th><th>Site</th><th>Number of WW's there</th><th>My Info</th></tr>
<?php
$sql = sprintf("select * from Social_sites order by category, site_name");
$result = mysql_query($sql);
while ( $site = mysql_fetch_array($result) ) {
  $sql_count = sprintf("select count(*) from Social_users where site_id=%s",$site['id']);
  $result_count = mysql_query($sql_count);
  $count = mysql_fetch_row($result_count);
  $site_count = $count[0];
  $sql_user = sprintf("select * from Social_users where site_id=%s and user_id=%s",$site['id'],$uid);
  $user_result = mysql_query($sql_user);
  $user = mysql_fetch_array($user_result);
  print "<tr>";
  print "<td>".$site['category']."</td>";
  $a_start = "";
  $a_end = "";
  if ( $site['url'] != "" ) {
    $a_start = "<a href='".$site['url']."'>";
    $a_end = "</a>";
  }
  print "<td>$a_start".$site['site_name']."$a_end</td>";
  print "<td align='center'><a href='/social/site/".$site['site_name']."'>".$site_count."</a></td>";
  if ( isset($user['user_id']) ) {
    if ( $site['link'] != "" ) {
      $link = preg_replace("/<userinfo>/",$user['user_info'],$site['link']); 
      $userinfo = "<a href='$link'>".$user['user_info']."</a>";
    } else {
      $userinfo = $user['user_info'];
    }
    print "<td><table width='100%'><tr><td>$userinfo</td>";
    print "<td align='right'><a href='/social/new_user.php?site_name=".$site['site_name']."&mode=edit'><img border='0' src='/images/edit.png' /></a>";
    print " <a href='/social/new_user.php?site_name=".$site['site_name']."&mode=delete'><img border='0' width='15px' src='/images/delete.png' /></a></td></tr></table>";
    print "</td>";
  } else {
    print "<td><a href='/social/new_user.php?site_name=".$site['site_name']."&mode=add'><img border='0' src='/images/add.png' /></a></td>";
  }
 print "</tr>";
}
?>
</table>
<a href='/social/new_social.php'>Add a new site</a>
<br />
<h5>Please <a mailto='cassandrap.project@gmail.com'>let us know</a> if any of the links don't work properly.</h5>
</div>
</body>
</html>
