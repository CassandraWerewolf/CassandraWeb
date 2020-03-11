<?php // bgg.php - Functions used to communicate with BGG

function send_geekmail($to, $subject, $message, $from="Cassandra Project", $password="" ) {
  $password = $password ?: getenv('BGG_PASSWORD');
  system("/var/www/html/bgg/send_geekmail.pl \"$from\" \"$password\" \"$to\" \"$subject\" \"$message\" > /dev/null &", $retval);
}

function reply_thread($thread_id, $body, $player="Cassandra Project", $password="" ) {

  $password = $password ?: getenv('BGG_PASSWORD');
  $article_id = system ("/var/www/html/php/post_thread.pl \"$player\" \"$password\" \"reply\" \"$thread_id\" \"$body\"", $retval);

return $article_id;
}

function reply_thread_quick($thread_id, $body, $player="Cassandra Project", $password="" ) {

  $password = $password ?: getenv('BGG_PASSWORD');
  system ("/var/www/html/php/post_thread.pl \"$player\" \"$password\" \"reply\" \"$thread_id\" \"$body\" > /dev/null &", $retval);

}

function edit_post($article_id, $body, $player="Cassandra Project", $password="" ) {

  $password = $password ?: getenv('BGG_PASSWORD');
  system ("/var/www/html/php/post_thread.pl \"$player\" \"$password\" \"edit\" \"$article_id\" \"$body\" >/dev/null &", $retval);

}

function create_thread($title,$message,$forum_id='76', $player="Cassandra Project", $password="" ) {

  $password = $password ?: getenv('BGG_PASSWORD');

  $article_id = system ("/var/www/html/php/post_thread.pl \"$player\" \"$password\" \"new\" \"$forum_id\" \"$message\" \"$title\"" , $retval);
  $thread_id = system ("/var/www/html/php/get_thread_id.pl \"$player\" \"$password\" \"$article_id\"", $retval);

return $thread_id;
}

function is_bgg_user($username) {

  $result = system ("/var/www/html/php/check_bgg_user.pl \"$username\"", $retval);
  return $result;
}

function edit_playerlist_post($game_id) {
  $sql = sprintf("select status, thread_id, player_list_id from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $status = mysql_result($result,0,0);
  $thread_id = mysql_result($result,0,1);
  $player_list_id = mysql_result($result,0,2);
  if ( $status == "Sign-up" ) {
    $sql = sprintf("select name from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
    $result = mysql_query($sql);
	$count = dbGetResultRowCount($result);
    $body = "Player List According to Cassandra:\n";
    while ( $row = mysql_fetch_array($result) ) {
      $body .= $row['name']."\n";
    }
    $body .= "\n$count players are signed up.\n";
    $body .= "\n To sign up for this game go to \n";
    $body .= "http://cassandrawerewolf.com/game/$thread_id\n";

    edit_post($player_list_id, $body);
  }
}

function notify_moderator($game_id,$action="",$username="") {
  global $uname;
  $sql = sprintf("select title, thread_id from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $title = mysql_result($result,0,0);
  $thread_id = mysql_result($result,0,1);

  $sql = sprintf("select name from Users, Moderators where Users.id=Moderators.user_id and game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);

  $count = 0;
  $to = "";
  while ( $row = mysql_fetch_array($result) ) {
    if ( $count > 0 ) { $to .= ", "; }
	if ( $row['name'] == "Cassandra Project" ) { continue; }
    $to .= $row['name'];
	$count++;
  }
  $subject = "Player List for $title has been updated";
  $message = "The current player list according to Cassandra is\n";


  $sql = sprintf("select name from Users, Players where Users.id=Players.user_id and game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  $count = dbGetResultRowCount($result);
  while ( $row = mysql_fetch_array($result) ) {
    $message .= $row['name'] . "\n";
  }

  $message .= "\n$count players are signed up.\n";

  if ( $action != "" && $username != "" ) {
    $message .= "\n$username was $action.\n";
  }

  $message .= "\nGo to Game: http://www.boardgamegeek.com/thread/$thread_id\n";

  send_geekmail($to, $subject, $message) ;
}

function geekmail_form($to="",$subject="",$message="") {
  global $username;
  $bggpwd = $_COOKIE['bgg_password'];

  $output = "<script language='javascript'>\n";
  $output .= "<!--\n";
  $output .= "function bgg_pwd_note() {\n";
  $output .= "alert('This will be stored as a cookie on your computer not in the Cassandra database.')\n";
  $output .= "}\n";
  $output .= "//-->>\n";
  $output .= "</script>";

  $output .= "<h2>Compose GeekMail</h2>\n";
  $output .= "<form name='send_message' method='post' action='/send_geekmail.php'>\n";
  $output .= "<table class='forum_table'>\n";
  $output .= "<tr><td>BGG Password:</td><td><input type='password' name='bggpwd' value='$bggpwd' /></td></tr>\n";
  $output .= "<tr><td></td><td><input type='checkbox' name='remember' />Remember BGG password. <a href='javascript:bgg_pwd_note()'>Read First</a></td></tr>\n";
  $output .= "<tr><td>To:</td><td><input type='text' name='to' value='$to' size='59'/></td></tr>\n";
  $output .= "<tr><td>Subject:</td><td><input type='text' name='subject' value='$subject' size='59' /></td></tr>\n";
  $output .= "<tr><td>Message:</td><td><textarea name='message' rows='10' cols='50'>";
  if ( $to != "" ) { $output .= "cc:$to\n";}
  if ( $message != "" ) { $output .= "$message\n";}
  $output .= "</textarea></td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit' value='submit' /></td></tr>\n";
  $output .= "</table></form>\n";

  return $output;
}

