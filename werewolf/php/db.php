<?php //db.php library

require_once __DIR__ . "/mysql_compat.php";

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

// Runs a parameterised query: put ? in $sql for each value in $params.
// The values are sent separately from the SQL, so they can't change the query.
// Returns a result for SELECT, true for other statements, or false on error,
// so the result works with the mysql_* functions in mysql_compat.php.
function db_query($sql, array $params = []) {
  $link = __mysql_compat_link();
  if ( ! $link ) {
    return false;
  }
  return mysqli_execute_query($link, $sql, $params);
}

function quote_smart($value) {
  // Magic quotes no longer exist (removed in PHP 5.4/8.0), so no stripslashes() here.
  if ( ! is_numeric($value)) {
    $value = "'".mysql_real_escape_string($value)."'";
  }
  return $value;
}
?>
