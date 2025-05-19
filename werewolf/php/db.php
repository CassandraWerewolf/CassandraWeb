<?php //db.php library

$dbhost = getenv('MYSQL_HOST');
$dbuser = getenv('MYSQL_USER');
$dbpass = getenv('MYSQL_PASSWORD');
$dbcnx = null; // Initialize the connection variable

function dbConnect($db="werewolf") {
  global $dbhost, $dbuser, $dbpass, $dbcnx;

  $dbcnx = mysqli_connect($dbhost, $dbuser, $dbpass, $db)
     or die("The site database appears to be down.");

  if (!$dbcnx) die ("The site database is unavailable.");

  mysqli_set_charset($dbcnx, "utf8");

  // Include the compatibility layer for mysql_ functions
  require_once __DIR__ . '/mysqli_compat.php';

  return $dbcnx;
}

function dbGetResult($sql) {
    global $dbcnx;
    $res = mysqli_query($dbcnx, $sql);
    if (!$res) {
        die('Could not query:' . mysqli_error($dbcnx));
    }

    return $res;
}

function dbGetResultRowCount($res) {
    $row_count = mysqli_num_rows($res);
    return $row_count;
}

function quote_smart($value) {
  global $dbcnx;

  // In PHP 8, get_magic_quotes_gpc() is removed
  if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $value = stripslashes($value);
  }

  if (!is_numeric($value)) {
    $value = "'" . mysqli_real_escape_string($dbcnx, $value) . "'";
  }
  return $value;
}
?>
