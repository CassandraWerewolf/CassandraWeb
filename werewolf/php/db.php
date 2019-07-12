<?php //db.php library

$dbhost = getenv('MYSQL_HOST');
$dbuser = getenv('MYSQL_USER');
$dbpass = getenv('MYSQL_PASSWORD');

function dbConnect($db="werewolf") {
  global $dbhost, $dbuser, $dbpass;

  $dbcnx = mysql_connect($dbhost, $dbuser, $dbpass)
	  or die("The site database appears to be down.");

  if (!@mysql_select_db($db)) die ("The site database is unavailable.");

  mysql_set_charset("utf8");

  return $dbcnx;
}

function dbGetResult($sql) {
	$res = mysql_query($sql);
	if (!$res) {
		die('Could not query:' . mysql_error());
	}

	return $res;
}

function dbGetResultRowCount($res) {
	$row_count = mysql_num_rows($res);
	return $row_count;
}

function quote_smart($value) {
  if ( get_magic_quotes_gpc()) {
    $value = stripslashes($value);
  }
  if ( ! is_numeric($value)) {
    $value = "'".mysql_real_escape_string($value)."'";
  }
  return $value;
}
?>
