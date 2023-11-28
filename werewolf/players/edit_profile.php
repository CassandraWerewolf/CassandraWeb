<?php
include_once "../setup.php";

include_once ROOT_PATH . "/php/accesscontrol.php";
include_once "edit_profile_functions.php";
include_once ROOT_PATH . "/php/bgg.php";

if ( ! isset($_REQUEST['q']) ) {
clear_editSpace($username);
exit;
}

$id = $_REQUEST['user_id'];
$field = $_REQUEST['field'];
$sql = "show full columns from Bio";
$result = mysqli_query($mysql, $sql);
while ( $bio = mysqli_fetch_array($result) ) {
  $comment[$bio['Field']] = $bio['Comment'];
}


if ( $_REQUEST['q'] == "edit" ) {
  if ( $field == "avatar" ) {
    print "In Production.  In the future there will be a button here for you to push to update your avatar from BGG.<br />";
	exit;
  }
  print "Please enter your ".$comment[$field].".<br />\n";
  print "<form name='edit_form'>\n";
  print "<input type='hidden' name='field' value='$field' />\n";
  $sql_field = sprintf("select %s from Bio where user_id=%s",$field,quote_smart($id));
  $result_field = mysqli_query($mysql, $sql_field);
  $value = mysqli_result($result_field,0,0);
  switch ( $field ) {
  case max_messages:
    print "<input type='text' size='6' name='$field' value='$value' />\n";
  break;
  case rl_name:
  case location:
  case Nemesis:
  case mbti:
  case email_addr:
    print "<input type='text' size='36' name='$field' value='$value' />\n";
  break;
  case chat_color:
    print "<input type='text' size='8' name='$field' value='$value' /><a href='#' onClick='cp.select(document.edit_form.$field,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a>\n";
  break;
  case name_origin:
  case free_hours:
  case job:
  case family:
  case religion:
  case comments:
    print "<textarea cols='50' rows='6' name='$field' >$value</textarea>\n";
  break;
  case b_date:
    if ( $value == "" ) { $value = "yyyy-mm-dd"; }
    print "<input type='text' size='10' name='$field' value='$value' />\n";
  break;
  case gender:
	$select_m = "";
	$select_f = "";
	if ( $value == "M" ) { $select_m = "selected"; } 
	if ( $value == "F" ) { $select_f = "selected"; } 
    print "<select name='$field'><option value='' /><option $select_m value='M' />M<option $select_f value='F' />F</select>\n";
  break;
  case time_zone:
    print "<select name='$field'><option value=''/>\n";
    $sql_tz = "select zone, concat('(GMT',if(GMT>0,' +',''),if(GMT=0,'',concat(if(GMT<0,' ',''),GMT)),') ',description) as text from Timezones order by zone DESC";
    $result_tz = mysqli_query($mysql, $sql_tz);
    while ( $tz = mysqli_fetch_array($result_tz) ) {
	  $selected = "";
	  if ( $tz['zone'] == $value ) { $selected = "selected"; }
      print "<option $selected value='".$tz['zone']."' />".$tz['text']."\n";
    }
    print "</select>\n";
  break;
  case twitter_name:
    print "<input type='text' size='50' name='$field' value='$value' />\n";
  break;
  }
  print "<br /><input type='button' name='submit' value='submit' onClick='submit_field()' />\n";
  print "</form>\n";
} else if ( $_REQUEST['q'] == "submit" ) {
  $_REQUEST['value'] = safe_html($_REQUEST['value'],"<img><object><param><embed><a>");
  $sql = sprintf("update Bio set %s = %s where user_id=%s",$field,quote_smart($_REQUEST['value']),quote_smart($id));
  $result = mysqli_query($mysql, $sql);
  print show_field($id,$field);

	if($field == 'twitter_name') {
		$command = "twitter/tw.pl follow " . quote_smart($_REQUEST['value']);
		system($command);
	}
}
?>
