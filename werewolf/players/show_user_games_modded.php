<?php
	include_once "../setup.php";

	include ROOT_PATH . "/php/accesscontrol.php";
	include_once ROOT_PATH . "/php/db.php";
	include_once ROOT_PATH . "/menu.php";
	require_once 'HTML/Table.php';

	$game = "/game/";

	$player = $_REQUEST['player']; 
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
	}
?>
<html>
<head>
<title>Moderator Stats for <?=$player;?></title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
<?php

$sql = sprintf("select id from Users where name=%s",quote_smart($player));
$result = mysql_query($sql);
$user_id = 0;
if ( mysql_num_rows($result) == 1 ) {
  $user_id = mysql_result($result,0,0);
}

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

function edit_comment(game_id) {
  myComment = document.getElementById('comment_'+game_id);
  myForm = document.getElementById('form_'+game_id);
  myDiv = myForm
  agent.call('','edit_dialog','update_div',user_id,game_id);
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

function submit_comment(game_id) {
  comment = document.getElementById('new_comment_'+game_id).value;
  myComment = document.getElementById('comment_'+game_id);
  myForm = document.getElementById('form_'+game_id);
  myDiv = myComment
  agent.call('','update_comment','update_div',user_id,game_id,comment);
}

function clear_edit(game_id) {
  comment = document.getElementById('new_comment_'+game_id).value;
  myComment = document.getElementById('comment_'+game_id);
  myForm = document.getElementById('form_'+game_id);
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
<h1>Moderator Stats for <?=$player;?></h1>
<?php
	dbConnect();

	$sql_games_modded = "SELECT CONCAT(Games.number, ') ', Games.title) AS game, thread_id, Games.id as game_id, Moderators.comment FROM Games,Moderators,Users WHERE Users.name = '$player' AND Users.id=Moderators.user_id AND Games.id=Moderators.game_id and Games.status != 'Sub-Thread' and Games.status != 'Sign-up' and Games.number != 0 order by Games.number ";

	#
	# get games modded data
	#
	$res_games_modded = dbGetResult($sql_games_modded);
	$games_modded_total = dbGetResultRowCount($res_games_modded);
	$games_modded_names[] = "header placeholder";
    $games_modded_comment[] = "Comment";
	while($row = mysql_fetch_array($res_games_modded)){
	    $sql = "select count(*) from Posts, Users where Posts.user_id=Users.id and game_id='".$row['game_id']."' and name='$player'";
		$result = mysql_query($sql);
		$num_post = mysql_result($result,0,0);
		$games_modded_names[] = "<a href='$game".$row['thread_id']."'>".$row['game']."</a> <a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		$games_modded_comment[] = "<div id='comment_".$row['game_id']."' onMouseOver='show_hint(\"Click to Edit your comment\")' onMouseOut='hide_hint()' onClick='edit_comment(\"".$row['game_id']."\")' style='visibility:visible; position:static;' >".$row['comment']."&nbsp;</div>\n<div id='form_".$row['game_id']."' style='visibility:hidden; position:absolute;'></div>";
	}

	$attrs = array(
		'border' => '0',
		'cellpadding' => '4',
		'class' => 'forum_table'
	);

	$table =& new HTML_Table($attrs);

	$table->addCol($games_modded_names);
    $table->addCol($games_modded_comment);
	$table->setHeaderContents(0,0,"Games Modded ($games_modded_total)");
    $table->setHeaderContents(0,1,"Comment");
	echo $table->toHTML();

?>
</div>
<script language='javascript'>
<!--
setHint()
//-->
</script>
</body>
</html>
<?php
function edit_dialog($user_id,$game_id) {
  $output = "<form>\n";
  $sql = sprintf("select comment from Moderators where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $comment = mysql_result($result,0,0);
  $output .= "<textarea id='new_comment_${game_id}' name='new_comment_${game_id}' style='width:100%; height:80px;'>$comment</textarea><br />";
  $output .= "<input type='button' name='submit' value='submit' onclick='submit_comment(\"$game_id\")' /> ";
  $output .= "<input type='button' name='cancel' value='cancel' onclick='clear_edit(\"$game_id\")' />";
  $output .= "</form>\n";

  return $output;
}

function update_comment($user_id,$game_id,$comment){
  $comment = stripslashes($comment);
  $comment = safe_html($comment);
  $sql = sprintf("update Moderators set comment=%s where user_id=%s and game_id=%s",quote_smart($comment),quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);

  return $comment;
}

?>

