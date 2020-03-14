<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";
include_once "php/common.php";

dbConnect();

$game_id = $_REQUEST['game_id'];
$user_id = $_REQUEST['user_id'];
$action = $_REQUEST['action'];

$cache = init_cache();
$cache->remove('game-'.$game_id,'front');

$mod = is_moderator($uid,$game_id);

$bgg_cassy = BGG::authAsCassy();

$rep = false;
$sql = sprintf("select * from Replacements where user_id=%s and game_id=%s and replace_id=%s",quote_smart($user_id),quote_smart($game_id),quote_smart($uid));
$result = mysql_query($sql);
if ( mysql_num_rows($result) == 1 ) { $rep = true; }

$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$game = mysql_fetch_array($result);

$sql = sprintf("select name from Users where id=%s",quote_smart($user_id));
$result = mysql_query($sql);
$user_name = mysql_result($result,0,0);

$sql = sprintf("select name from Users, Moderators where Users.id=Moderators.user_id and game_id=%s",quote_smart($game_id));
$result = mysql_query($sql);
$mod_num = mysql_num_rows($result);
$count = 0;
$to = "";
while ( $moderator = mysql_fetch_array($result) ) {
  if ( $moderator['name'] == "Cassandra Project" ) { continue; }
  if ( $count != 0  ) $to .= ", ";
  $to .= $moderator['name'];
  $count++;
}

if ( $action == "replace_me" ) {
# Check to see if player submitting request is the mod or the player being replaced.
  if ( !$mod && $uid != $user_id  && !$rep) { 
    error("You can't put in a replacement request for this player"); 
  }
  $sql = sprintf("update Players set need_replace='1' where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  if ( !$mod ) {
	$subject = "Player needs to be replaced";
    $message = "$user_name has requested to be replaced in ".$game['title'];
	if( $to != "" ) {
      $bgg_cassy->send_geekmail($to, $subject, $message);
	}
  }
  $message = "r{ We are looking for a player to replace $user_name.  Please go to http://cassandrawerewolf.com/game/".$game['thread_id']." to replace the player. }r";
  $bgg_cassy->reply_thread($game['thread_id'], $message);
  error("You have sucessfully requested a replacement");
} else if ( $action == "I_replace" ) {
  if ( $uid == $user_id || $mod) {
    if ( ! $mod ) {
	  # Make sure another player hasn't already replace them.
	  $sql = sprintf("select need_replace from Players where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
	  $result = mysql_query($sql);
	  if ( mysql_result($result,0,0) == "" ) {
        error("I'm sorry this player no longer needs to be replaced.");
	  }
	}
    $sql = sprintf("update Players set need_replace = null where user_id=%s and game_id=%s", quote_smart($user_id),quote_smart($game_id));
	$result = mysql_query($sql);
	if ( !$mod ) {
  	  $subject = "Player no longer needs to be replaced";
      $message = "$user_name has removed the requested to be replaced in ".$game['title'];
	  if ( $to != "" ) {
        $bgg_cassy->send_geekmail($to, $subject, $message);
	  }
  	}
    $message = "r{ $user_name no longer needs to be replaced.}r";
    $bgg_cassy->reply_thread($game['thread_id'], $message);
	error("You are no longer requesting a replacement");
  } 
  # Check to make sure player is not already in the game
  $sql_check = sprintf("select * from Players_all where user_id=%s and game_id=%s",quote_smart($uid),quote_smart($game_id));
  print "Sql check: $sql_check <br />";
  $result_check = mysql_query($sql_check);
  if ( mysql_num_rows($result_check) > 0 ) {
    error ("You are already in this game and can not be automatically put in as a replacement.  If the moderator allows you to replace this player then he must replace you manually");
  }
  # Make sure another player hasn't already replace them.
  $sql_test = sprintf("select need_replace from Players where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
  print "Sql test: $sql_test <br />";
  $result_test = mysql_query($sql_test);
  if ( mysql_result($result_test,0,0) == "" ) {
     error("I'm sorry this player no longer needs to be replaced.");
  }
  $sql = sprintf("insert into Replacements (user_id, game_id, replace_id, period, number) values ( %s, %s, %s, %s, %s)",quote_smart($user_id),quote_smart($game_id),quote_smart($uid),quote_smart($game['phase']),quote_smart($game['day']));
  print "Replace: $sql <br />";
  $result = mysql_query($sql);
  $sql = sprintf("update Players set need_replace = null where user_id=%s and game_id=%s", quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $subject = "Player no longer needs to be replaced";
  $message = "$username has replaced $user_name in ".$game['title'];
  if ( $to != "" ) {
    $bgg_cassy->send_geekmail($to, $subject, $message);
  }
  $message = "r{ $username has replaced $user_name.  If you have (un)voted $user_name since the last Cassandra vote tally, please [b][unvote all][/b] and revote your current choice just to make sure Cassy counts it correctly.}r";
  $bgg_cassy->reply_thread($game['thread_id'], $message);
  error ("You have now replaced the player");
  #exit;
} else {
error("You didn't specifiy an action");
}
?>
