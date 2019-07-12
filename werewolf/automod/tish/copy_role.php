<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";

dbConnect();

$old_role_id=$_REQUEST['role_id'];

$sql = sprintf("select * from AM_roles where id=%s",quote_smart($old_role_id));
$result = mysql_query($sql);
$roles = mysql_fetch_array($result); 

$sql = sprintf("insert into AM_roles (id, template_id, role_id, side, game_action, action_desc, group_name, n0_knows, n0_view, view_result, reveal_as, attribute, a_hidden, parity, require_role) VALUES ( null, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )",quote_smart($roles['template_id']),quote_smart($roles['role_id']),quote_smart($roles['side']),quote_smart($roles['game_action']),quote_smart($roles['action_desc']),quote_smart($roles['group_name']),quote_smart($roles['n0_knows']),quote_smart($roles['n0_view']),quote_smart($roles['view_result']),quote_smart($roles['reveal_as']),quote_smart($roles['attribute']),quote_smart($roles['a_hidden']),quote_smart($roles['parity']),quote_smart($roles['require_role']));
print $sql;
$result = mysql_query($sql);

?>

<html>
<head>
<script language='javascript'>
<!--
location.href='/automod/template/<?=$roles['template_id'];?>'
//-->
</script>
</head>
<body>
<a href="/automod/template/<?=$roles['template_id'];?>">Go To Template Page</a>
</body>
</html>

