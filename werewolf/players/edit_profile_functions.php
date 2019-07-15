<?php

include_once "../setup.php";
include_once ROOT_PATH . "/php/db.php";

dbConnect();

$here = "/";
$player = "${here}player/";
$game_page = "${here}game/";
$posts = "";

function setPostsPath($game_id) {
global $here, $posts;
$sql = sprintf("select thread_id from Games where id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$thread_id = mysql_result($result,0,0);
$posts = "${here}game/$thread_id/";
}

function clear_editSpace($player) {
  global $here;
  print "Welcome $player, please click on something you wish to edit.  Everything is optional and only those that you have filled in will be shown to other werewolf players.  The edit dialoge will appear here.";
}

function show_field($id,$field,$text="none") {
  $sql = sprintf("show full columns from Bio where Field=%s",quote_smart($field));
  $result = mysql_query($sql);
  $comment = mysql_result($result,0,8);
  $hint_comment = $comment;
  if ( $field == "mbti" ) { $hint_comment = "MBTI"; }
  if ( $text == "none" ) {
    $s = $field;
	if ( $field == 'b_date' ) { $s = "TIMESTAMPDIFF(YEAR, b_date, CURDATE()) as b_date"; }
    $sql = sprintf("select %s from Bio where user_id=%s",$s,quote_smart($id));
	$result = mysql_query($sql);
	$text = mysql_result($result,0,0);
	if ( $field == 'time_zone' ) { 
	  $sql = sprintf("select concat('(GMT',if(GMT>0,' +',''),if(GMT=0,'',concat(if(GMT<0,' ',''),GMT)),') ',description) as text from Timezones where zone=%s",quote_smart($text));
	  $result = mysql_query($sql);
	  $text = mysql_result($result,0,0);
	}
  }
  if ( $field == 'avatar' ) { 
    if ( $text != "" ) {
      $text = "<img height='64px' width='64px' src='/avatars/$text' />";
	}
  }
  $output = "<div onMouseOver='show_hint(\"Click to Edit $hint_comment\")' onMouseOut='hide_hint()' onClick='edit_field(\"$field\")'>";
  $output .= $text;
  $output .= "</div>";

  return $output;
}
