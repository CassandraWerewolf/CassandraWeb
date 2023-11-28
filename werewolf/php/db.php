<?php //db.php library

$dbhost = getenv('MYSQL_HOST');
$dbuser = getenv('MYSQL_USER');
$dbpass = getenv('MYSQL_PASSWORD');

function dbConnect($db="werewolf") {
  global $dbhost, $dbuser, $dbpass;

  $dbcnx = mysqli_connect($dbhost, $dbuser, $dbpass)
	  or die("The site database appears to be down.");

  if (!@mysqli_select_db($dbcnx, $db)) die ("The site database is unavailable.");

  mysqli_set_charset($dbcnx, "utf8");

  return $dbcnx;
}

// Polyfill mysql_result for mysql.
function mysqli_result($res,$row=0,$col=0){ 
  $numrows = mysqli_num_rows($res); 
  if ($numrows && $row <= ($numrows-1) && $row >=0){
      mysqli_data_seek($res,$row);
      $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
      if (isset($resrow[$col])){
          return $resrow[$col];
      }
  }
  return false;
}

function dbGetResult($sql) {
  $mysql = dbConnect();
	$res = mysqli_query($mysql, $sql);
	if (!$res) {
		die('Could not query:' . mysqli_error());
	}

	return $res;
}

function dbGetResultRowCount($res) {
	$row_count = mysqli_num_rows($res);
	return $row_count;
}

function quote_smart($value) {
  $value = stripslashes($value);
  if ( ! is_numeric($value)) {
    $value = "'".mysqli_real_escape_string(dbConnect(), $value)."'";
  }
  return $value;
}
?>
