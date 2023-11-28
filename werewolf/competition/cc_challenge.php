<?php
include_once "../setup.php";

include_once ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/php/db.php";
include_once ROOT_PATH . "/php/bgg.php";

$mysql = dbConnect();

$game_id= $_REQUEST['game_id'];

if ( isset($_POST['submit']) ) {
  $sql = sprintf("select team, if(now()>=date_add(claim_time, interval 4 day),true,false) as extend_time from CC_info, CC_players where CC_info.user_id=CC_players.user_id and game_id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $old_team = mysqli_result($result,0,0);
  $sql = sprintf("select team from CC_players where user_id=%s",quote_smart($_POST['user_id']));
  $result = mysqli_query($mysql, $sql);
  $new_team = mysqli_result($result,0,0);
  if ( $old_team == $new_team ) {
    if ( $extend_time == "false" ) {
      error("You cannot challenge a game from your own team until it has been challengable for 24hrs.");
	}
  }
  $sql = sprintf("update CC_info set challenger_id=%s, type_error=%s, desc_error=%s where game_id=%s",quote_smart($_POST['user_id']),quote_smart($_POST['toe']),quote_smart($_POST['doe']),quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $sql = sprintf("select number, title, thread_id, name from Games, Users, CC_info where Games.id=CC_info.game_id and Users.id=CC_info.user_id and CC_info.game_id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $info = mysqli_fetch_array($result);
  $message = "$username has Challenged ".$info['name']."'s data for ".$info['number'].") ".$info['title'].".\n\n";
  $message .= "Reason: ".$_POST['doe'];
  $message .= "\n\nhttp://cassandrawerewolf.com/cc_game/".$info['thread_id'];
  BGG::authAsCassy()->reply_thread_quick('163595',$message);
  error("Your challenge has been recorded");
}

$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
$game = mysqli_fetch_array($result);
?>
<html>
<head>
<title>Challenge a completed game</title>
</head>
<body>
<h1>Challenge a Completed game</h1>
<form method='post' action='<?=$_SERVER['PHP_SELF'];?>'>
<table class='forum_table' border='0'>
<tr><td>Game:</td><td><?=$game['title'];?></td></tr>
<input type='hidden' name='game_id' value='<?=$game_id;?>' />
<tr><td>Challenger:</td><td><?=$username;?></td></tr>
<input type='hidden' name='user_id' value='<?=$uid;?>' />
<tr><td>Type of Error:</td><td>
<select name='toe'>
<option value='game'>Game Details</option>
<option value='player'>Player Details</option>
</select>
</td></tr>
<tr><td>Description and <br />Evidence of Error:</td><td><textarea rows='5' cols='50' name='doe'></textarea></td></tr>
<tr><td colspan='2' align='center'><input type='submit' name='submit' value='Submit'></td></tr>
</table>
</form>
<a href='javascript:close("cc_space")'>[close]</a>
</body>
</html>
