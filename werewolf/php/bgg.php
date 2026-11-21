<?php 
// This class is used to communicate with BGG
class BGG 
{
    const SCRIPT_PATH = "/opt/werewolf/bgg/";

    protected $username;
    private $password;
    private $geekauth;

    public function __construct() {
    }

    // Build a shell command for one of the bin/bgg scripts. Every argument is
    // passed through escapeshellarg(), so text like $(...) or `...` in a message,
    // title or form field is handed to the script literally and never run by
    // the shell. (The old code used "double quotes", which allowed command injection.)
    private static function cmd($script, ...$args) {
        return self::SCRIPT_PATH . $script . ' ' . implode(' ', array_map(
            function ($a) { return escapeshellarg((string) $a); }, $args));
    }

    public static function auth($username, $password) {
        $instance = new self();
        $instance->username = $username;
        $instance->password = $password;
        exec(self::cmd("authenticate.pl", $username, $password), $output);
        $instance->geekauth = $output[0] ?? '';
        return $instance;
    }

    public static function authAsCassy() {
        $username = getenv('BGG_USERNAME');
        $password = getenv('BGG_PASSWORD');
        return self::auth($username, $password);
    }

    // Auth required functions

    public function send_geekmail($to, $subject, $message) {
        system(self::cmd("send_geekmail.pl", $this->username, $this->password, $to, $subject, $message) . " > /dev/null &", $retval);
    }

    public function reply_thread($thread_id, $body) {
        $article_id = self::get_article_id_by_thread_id($thread_id);
        exec(self::cmd("reply_thread.pl", $this->geekauth, $thread_id, $article_id, $body), $response);
        $article = json_decode($response[0] ?? '', true);
        return $article['id'] ?? null;
    }

    public function reply_thread_quick($thread_id, $body) {
        self::reply_thread($thread_id, $body);
    }

    public function edit_post($article_id, $body) {
        system(self::cmd("edit_article.pl", $this->geekauth, $article_id, $body) . " >/dev/null &", $retval);
    }

    public function create_thread($title,$message,$forum_id='8') {
        $article_id = system(self::cmd("create_thread.pl", $this->username, $this->password, $forum_id, $message, $title), $retval);
        return self::get_thread_id_by_article_id($article_id);
    }
    
    // No oauth required functions

    public function is_bgg_user($username) {
        exec(self::cmd("check_bgg_user.pl", $username), $output);
        return $output[0] ?? null;
    }

    public function get_thread_id_by_article_id($article_id) {
        exec(self::cmd("retrieve_thread.pl", $article_id), $response);
        $thread = json_decode($response[0] ?? '', true);
        return $thread['source']['id'] ?? null;
    }

    public function get_article_id_by_thread_id($thread_id) {
        exec(self::cmd("retrieve_articles.pl", $thread_id), $response);
        $thread = json_decode($response[0] ?? '', true);
        return $thread['articles'][0]['id'] ?? null;
    }
}

// -----------------------------------------------------------------------------
// Other related BGG methods
// -----------------------------------------------------------------------------

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

    BGG::authAsCassy()->edit_post($player_list_id, $body);
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

  $message .= "\nGo to Game: https://www.boardgamegeek.com/thread/$thread_id\n";

  BGG::authAsCassy()->send_geekmail($to, $subject, $message) ;
}

// "Remember BGG password": the password is kept in an HttpOnly cookie in the
// user's own browser (not in the database). It is never printed into a page:
// forms show a "(saved)" placeholder, and a blank field means "use the saved one".

// Returns the BGG password for this POST: what was typed, or the saved cookie if
// the field was left blank. Saves it when "remember" is ticked. Only a POSTed form
// can save it (a GET link can't). Call before any output, because it may set a cookie.
function bgg_password_from_post() {
  if ( ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ) {
    return '';
  }
  $typed = (string) ($_POST['bggpwd'] ?? '');
  $pwd = ( $typed !== '' ) ? $typed : (string) ($_COOKIE['bgg_password'] ?? '');
  if ( ($_POST['remember'] ?? '') == 'on' && $pwd !== '' ) {
    setcookie('bgg_password', $pwd, time()+60*60*24*365, '/', '', true, true);
  }
  return $pwd;
}

// Extra attribute for the BGG password <input>: a "(saved)" placeholder when one is remembered.
function bgg_password_placeholder() {
  return ( (string) ($_COOKIE['bgg_password'] ?? '') !== '' ) ? " placeholder='(saved)'" : "";
}

// Escapes text for HTML (attributes and element content). Existing entities such
// as &amp; are left alone, so text that was already escaped displays the same.
function bgg_html($text) {
  return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8', false);
}

function geekmail_form($to="",$subject="",$message="") {
  global $username;

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
  $output .= "<tr><td>BGG Password:</td><td><input type='password' name='bggpwd' value=''" . bgg_password_placeholder() . " /></td></tr>\n";
  $output .= "<tr><td></td><td><input type='checkbox' name='remember' />Remember BGG password. <a href='javascript:bgg_pwd_note()'>Read First</a></td></tr>\n";
  $output .= "<tr><td>To:</td><td><input type='text' name='to' value='" . bgg_html($to) . "' size='59'/></td></tr>\n";
  $output .= "<tr><td>Subject:</td><td><input type='text' name='subject' value='" . bgg_html($subject) . "' size='59' /></td></tr>\n";
  $output .= "<tr><td>Message:</td><td><textarea name='message' rows='10' cols='50'>";
  if ( $to != "" ) { $output .= "cc:" . bgg_html($to) . "\n";}
  if ( $message != "" ) { $output .= bgg_html($message) . "\n";}
  $output .= "</textarea></td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit' value='submit' /></td></tr>\n";
  $output .= "</table></form>\n";

  return $output;
}