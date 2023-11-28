<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";

$mysql = dbConnect();

$old_template_id=$_REQUEST['template_id'];

# Copy AM_template table
$sql = sprintf("select * from AM_template where id=%s",quote_smart($old_template_id));
$result = mysqli_query($mysql, $sql);
$template=mysqli_fetch_array($result);

$sql = sprintf("insert into AM_template (id, owner_id, name, description, num_players, num_player_sets, role_reveal, random_n0, priest_type, random_tinker, random_whitehat, mode) VALUES (null, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'Edit')",quote_smart($uid),quote_smart($template['name']." (copy)"),quote_smart($template['description']),quote_smart($template['num_players']),quote_smart($template['num_player_sets']),quote_smart($template['role_reveal']),quote_smart($template['random_n0']),quote_smart($template['priest_type']),quote_smart($template['random_tinker']),quote_smart($template['random_whitehat']));
$result = mysqli_query($mysql, $sql);
$new_template_id = mysqli_insert_id();
if ( $new_template_id < 10 ) { $new_template_id = "0".$new_template_id; }

# Copy AM_roles table
$sql = sprintf("select * from AM_roles where template_id=%s",quote_smart($old_template_id));
$result = mysqli_query($mysql, $sql);

while ( $roles = mysqli_fetch_array($result) ) {
  $sql2 = sprintf("insert into AM_roles (id, template_id, role_id, side, game_action, action_desc, group_name, n0_knows, n0_view, view_result, reveal_as, attribute, a_hidden, parity, promotion, promotion_parity, require_role) VALUES ( null, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )",quote_smart($new_template_id),quote_smart($roles['role_id']),quote_smart($roles['side']),quote_smart($roles['game_action']),quote_smart($roles['action_desc']),quote_smart($roles['group_name']),quote_smart($roles['n0_knows']),quote_smart($roles['n0_view']),quote_smart($roles['view_result']),quote_smart($roles['reveal_as']),quote_smart($roles['attribute']),quote_smart($roles['a_hidden']),quote_smart($roles['parity']),quote_smart($roles['promotion']),quote_smart($roles['promotion_parity']),quote_smart($roles['require_role']));
  $result2 = mysqli_query($mysql, $sql2);
}

# Copy ruleset
$old_file = "rulesets/${old_template_id}_ruleset.txt";
$new_file = "rulesets/${new_template_id}_ruleset.txt";
if ( file_exists($old_file) ) {
  copy($old_file,$new_file);
}

?>

<html>
<head>
<script language='javascript'>
<!--
location.href='/automod/template/<?=$new_template_id;?>'
//-->
</script>
</head>
<body>
<a href="/automod/template/<?=$new_template_id;?>">Go To Template Page</a>
</body>
</html>


?>
