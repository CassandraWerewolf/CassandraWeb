<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../menu.php";

$mysql = dbConnect();

$site_name = urldecode($_REQUEST['site_name']);
$sql = sprintf("select * from Social_sites where site_name=%s",quote_smart($site_name));
$result = mysqli_query($mysql, $sql);
$site = mysqli_fetch_array($result);

?>
<html>
<head>
<title>WW on <?=$site['site_name']?></title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php print display_menu(); ?>
<div style='padding:10px;'>
<h2>Werewolf Players on
<?php
$a_start = "";
$a_end = "";
if ( $site['url'] != "" ) {
  $a_start = "<a href='".$site['url']."'>"; 
  $a_end = "</a>";
}
print $a_start.$site['site_name'].$a_end;
?>
</h2>
<?php
$sql_users = sprintf("select * from Social_users, Users where Social_users.user_id = Users.id and site_id=%s order by name",quote_smart($site['id']));
$result_users = mysqli_query($mysql, $sql_users);
if ( mysqli_num_rows($result_users) == 0 ) {
  print "There are no users that use this service.";
} else {
  print "<table class='forum_table'>";
  print "<tr><th>Werewolf Player</th><th>".$site['site_name']." Information</t></tr>";
  while ( $user = mysqli_fetch_array($result_users) ) {
    print "<tr>";
    print "<td>".get_player_page($user['id'])."</td>";
    if ( $site['link'] != "" ) {
      $link = preg_replace("/<userinfo>/",$user['user_info'],$site['link']);
      $userinfo = "<a href='$link'>".$user['user_info']."</a>";
    } else {
      $userinfo = $user['user_info'];
    }
    print "<td>$userinfo</td>";
    print "</tr>";
  }
  print "</table>";
}
?>
</div>
</body>
</html>

