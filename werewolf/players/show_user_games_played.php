<?php

include_once "../setup.php";

include      ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/php/db.php";
include_once ROOT_PATH . "/php/common.php";
include_once ROOT_PATH . "/menu.php";
include_once "HTML/Table.php";

dbConnect();

$player = $_REQUEST['player'];

$game_page = "/game";
if ( $player == "" ) {
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

$sql = sprintf("select id from Users where name=%s",quote_smart($player));
$result = mysql_query($sql);
$user_id = 0;
if ( mysql_num_rows($result) == 1 ) { 
  $user_id = mysql_result($result,0,0);
}

?>
<html>
<head>
<title>Games Played Stats for <?=$player;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
<?php
if ( $user_id == $uid ) {
?>
<link rel='stylesheet' type='text/css' href='/hint.css'>
<script src='/hint.js'></script>
<script language='javascript'>
<!--
var user_id = "<?=$user_id;?>";
var myDiv = "";
var myComment = "";
var myForm = "";

function edit_comment(game_id,original_id) {
  myComment = document.getElementById('comment_'+game_id+"_"+original_id);
  myForm = document.getElementById('form_'+game_id+"_"+original_id);
  myDiv = myForm
  agent.call('','edit_dialog','update_div',user_id,game_id,original_id);
}

function update_div (str) {
  myComment.style.visibility = "hidden";
  myComment.style.position = "absolute";
  myComment.innerHTML = "";
  myForm.style.visibility = "hidden";
  myForm.style.position = "absolute";
  myForm.innerHTML = "";
  myDiv.style.visibility = "visible";
  myDiv.style.position = "static";
  myDiv.innerHTML = str;
}

function submit_comment(game_id,original_id) {
  comment = document.getElementById('new_comment_'+game_id+"_"+original_id).value;
  myComment = document.getElementById('comment_'+game_id+"_"+original_id);
  myForm = document.getElementById('form_'+game_id+"_"+original_id);
  myDiv = myComment
  agent.call('','update_comment','update_div',user_id,game_id,original_id,comment);
}

function clear_edit(game_id,original_id) {
  comment = document.getElementById('new_comment_'+game_id+"_"+original_id).value;
  myComment = document.getElementById('comment_'+game_id+"_"+original_id);
  myForm = document.getElementById('form_'+game_id+"_"+original_id);
  myDiv = myComment
  if (comment == "" ) { comment = "&nbsp;" }
  update_div(comment)
}

//-->
</script>
<?php
}
?>
</head>
<body>
<?php display_menu(); ?>
<div id='divDescription' class='clDescriptionCont'>
<!--Empty Div used for hint popup-->
</div>
<div style='padding:10px;'>
<h1>Games Played Stats for <?=$player;?></h1>
<?php
$results[] = "";
$all[] = "All";
$good[] = "Good";
$evil[] = "Evil";
$other[] = "Other";
$order = Array();

$sql = sprintf("select * from Users_result_count where user_id=%s order by user_id, result = 'Unknown', result = 'Other', result = 'Lost', result = 'Won'",quote_smart($user_id));
$result = mysql_query($sql);
$total = 0;
$count = 0;

while ( $data = mysql_fetch_array($result) ) {
  if ( $data['result'] == "Total" ) { $total = $data['count']; }
  $percentage = "";
  if ( $data['result'] == "Won" || $data['result'] == "Lost" ) {
    $percentage = "(";
    $perc = $data['count']/$total*100;
    $percentage .= round($perc);
    $percentage .= "%)";
  }
  $results[] = $data['result'];
  $all[] = $data['count']." $percentage";
  $count++;
  $order[$data['result']] = $count;
}

$sql = sprintf("select * from Users_result_side_count where user_id=%s order by user_id, side, result = 'Unknown', result = 'Other', result = 'Lost', result = 'Won'",quote_smart($user_id));
$result = mysql_query($sql);
$sub_total = 0;
while ( $data = mysql_fetch_array($result) ) {
  $i = $order[$data['result']];
  switch ($data['side']) {
  case Evil:
	  $percentage = "";
	  $this_total = $sub_total;
	  if ( $data['result'] == 'Total' ) {
      $sub_total = $data['count'];
		  $this_total = $total;
	  } 
	  if ( $data['result'] != 'Other') {
      $percentage = "(";
	    $perc = $data['count']/$this_total*100;
	    $percentage .= round($perc);
	    $percentage .= "%)";
	  }
	  $evil[$i] = $data['count']." $percentage";;
	  break;
	case Good:
	  $percentage = "";
	  $this_total = $sub_total;
	  if ( $data['result'] == 'Total' ) {
      $sub_total = $data['count'];
		  $this_total = $total;
	  } 
	  if ( $data['result'] != 'Other') {
      $percentage = "(";
	    $perc = $data['count']/$this_total*100;
	    $percentage .= round($perc);
	    $percentage .= "%)";
	  }
	  $good[$i] = $data['count']." $percentage";;
	  break;
	case Other:
	  $percentage = "";
	  $this_total = $sub_total;
	  if ( $data['result'] == 'Total' ) {
      $sub_total = $data['count'];
		  $this_total = $total;
	  } 
	  if ( $data['result'] != 'Other') {
      $percentage = "(";
	    $perc = $data['count']/$this_total*100;
	    $percentage .= round($perc);
	    $percentage .= "%)";
	  }
	  $other[$i] = $data['count']." $percentage";;
	  break;
  }
}
$attrs = array (
	'class' => 'forum_table',
	'cellpadding' => '2'
);

$table =& new HTML_Table($attrs);

$table->addCol($results);
$table->addCol($all);
$table->addCol($good);
$table->addCol($evil);
$table->addCol($other);

$table->setHeaderContents(0,0,$results[0]);
$table->setHeaderContents(0,1,$all[0]);
$table->setHeaderContents(0,2,$good[0]);
$table->setHeaderContents(0,3,$evil[0]);
$table->setHeaderContents(0,4,$other[0]);

echo $table->toHTML();
?>
<br />
<table class='forum_table' cellpadding='2'>
<?php
$sql_data = sprintf("select Games.id as game_id, number, title, `status`, thread_id, original_id, result, role_name, `type` as role_type, side, user_comment, death_phase, death_day from Games, Players, Players_result, Roles where Games.id=Players_result.game_id and Players_result.original_id=Players.user_id and Players_result.game_id=Players.game_id and Players.role_id=Roles.id and Players_result.user_id=%s order by number",quote_smart($user_id));
$result_data = mysql_query($sql_data);
$num_games = mysql_num_rows($result_data);
 print "<tr><th>#</th><th>Games Played ($num_games)</th><th>Role Name</th><th>Role Type</th><th>Team</th><th>Result</th><th>Death</th><th>Comment</th></tr>\n";
$game_count = 1;
while ( $data = mysql_fetch_array($result_data) ) {
  $style = "";
  if ( $data['result'] == "Won" ) {
    //green
    $style="style='background-color:#eeffee;'";
  } else if ( $data['result'] == "Lost" ) {
    //red
    $style="style='background-color:#ffeeee;'";
  } else if ( $data['result'] == "Unknown" && $data['status'] != "In Progress") {
    $style="style='background-color:white;'";
  }
  print "<tr $style>";
  print "<td $style>$game_count</td>\n";
  $game_count++;
  print "<td $style><a href='$game_page/".$data['thread_id']."'>".$data['number'].") ".$data['title']."</a>";
  $sql2 = sprintf("select count(*) from Posts where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($data['game_id']));
  $result2 = mysql_query($sql2);
  $post = mysql_result($result2,0,0);
  print "<a href='$game_page/".$data['thread_id']."/$player'> ($post posts) </a>";
  if ( $data['original_id'] != $user_id ) {
    print "<br />";
    print "Replaced ";
    print get_player_page($data['original_id']);
	$sql = sprintf("select * from Replacements where game_id=%s and replace_id=%s",quote_smart($data['game_id']),quote_smart($user_id));
	$result = mysql_query($sql);
	$rep_info = mysql_fetch_array($result);
    print " on ".$rep_info['period']." ".$rep_info['number'];
	$data['user_comment'] = $rep_info['rep_comment'];
  }
  print find_Replacements($user_id,$data['game_id']);
  print "</td>";
  if ( $data['status'] == "In Progress" ) {
    print "<td $style colspan='6'>Game In Progress</td>";
  } else {
    print "<td $style>".$data['role_name']."</td>";
    print "<td $style>".$data['role_type']."</td>";
    print "<td $style>".$data['side']."</td>";
    print "<td $style>".$data['result']."</td>";
    print "<td $style>".$data['death_phase']." ".$data['death_day']."</td>";
    print "<td $style>";
	print "<div id='comment_".$data['game_id']."_".$data['original_id']."' onMouseOver='show_hint(\"Click to Edit your comment\")' onMouseOut='hide_hint()' onClick='edit_comment(\"".$data['game_id']."\",\"".$data['original_id']."\")' style='visibility:visible; position:static;' >".$data['user_comment']."&nbsp;</div>";
	print "<div id='form_".$data['game_id']."_".$data['original_id']."' style='visibility:hidden; position:absolute;'></div></td>";
  }
  print "</tr>\n";
}
?>
</table>
</div>
<script language='javascript'>
<!--
setHint()
//-->
</script>
</body>
</html>
<?php
function find_Replacements($user_id,$game_id) {
  $sql = sprintf("Select name, replace_id as id, substring(period,1,1) as p, number from Users, Replacements where Users.id=Replacements.replace_id and game_id=%s and user_id=%s order by number, period",quote_smart($game_id),quote_smart($user_id));
  $result = mysql_query($sql);
  $count = 0;
  $replace = "";
  while ( $rep = mysql_fetch_array($result) ) {
    $sql2 = sprintf("select count(*) from Posts where game_id=%s and user_id='".$rep['id']."'",quote_smart($game_id));
    $result2 = mysql_query($sql2);
    $num_post = mysql_result($result2,0,0);
    if ( $count == 0 ) {
      $replace = "<br /> Replaced by ";
      $replace .= get_player_page($rep['name']);
      $replace .= " on ".$rep['p'].$rep['number'];
    } else {
      $replace .= ",<br /> ";
      $replace .= get_player_page($rep['name']);
      $replace .= " on ".$rep['p'].$rep['number'];
    }
    $count++;
  }
                                                                                
  return $replace;
}

function edit_dialog($user_id,$game_id,$original_id) {
  $output = "<form>\n";
  if ( $user_id == $original_id ) {
    $table = "Players";
    $field = "user_comment";
    $id = "user_id";
  } else {
    $table = "Replacements";
    $field = "rep_comment";
    $id = "replace_id";
  }
  $sql = sprintf("select %s from %s where %s=%s and game_id=%s",$field,$table,$id,quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $comment = mysql_result($result,0,0);
  $output .= "<textarea id='new_comment_${game_id}_$original_id' name='new_comment_${game_id}_$original_id' style='width:100%; height:80px;'>$comment</textarea><br />";
  $output .= "<input type='button' name='submit' value='submit' onclick='submit_comment(\"$game_id\",\"$original_id\")' /> ";
  $output .= "<input type='button' name='cancel' value='cancel' onclick='clear_edit(\"$game_id\",\"$original_id\")' />";
  $output .= "</form>\n"; 

  return $output;
}

function update_comment($user_id,$game_id,$original_id,$comment){
  if ( $user_id == $original_id ) {
    $table = "Players";
    $field = "user_comment";
    $id = "user_id";
  } else {
    $table = "Replacements";
    $field = "rep_comment";
    $id = "replace_id";
  }
  $comment = stripslashes($comment);
  $comment = safe_html($comment);
  $sql = sprintf("update %s set %s=%s where %s=%s and game_id=%s",$table,$field,quote_smart($comment),$id,quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  
  return $comment;
}

?>
