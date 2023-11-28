<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");

include_once "php/db.php";
$mysql = dbConnect();


//Check to see if a message was sent.
if(isset($_POST['message']) && $_POST['message'] != '') {
  $sql = sprintf("insert into `_message` (chat_id, user_id, user_name, message, post_time) values (%s,'1',%s,%s,now())",quote_smart($_GET['chat']),quote_smart($_POST['name']),quote_smart($_POST['message']));
  $result = mysqli_query($mysql, $sql);
}

//Create the XML response.
$xml = '<?xml version="1.0" ?><root>';
//Check to ensure the user is in a chat room.
if(!isset($_GET['chat'])) {
  $xml .='Your are not currently in a chat session.';
  $xml .= '<message id="0" >';
  $xml .= '<user>Admin</user>';
  $xml .= '<text>Your are not currently in a chat session.</text>';
  $xml .= '<time>' . date('h:i') . '</time>';
  $xml .= '</message>';
} else {
  $last = (isset($_GET['last']) && $_GET['last'] != '') ? $_GET['last'] : 0;
  $format = '%h:%i';
  $sql = sprintf("select message_id, user_name, message, date_format(post_time,%s) as post_time from `_message` where chat_id=%s and message_id > %s",quote_smart($format),quote_smart($_GET['chat']),quote_smart($last));
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $xml .= '<message id="'.$row['message_id'].'">';
    $xml .= '<user>'.htmlspecialchars($row['user_name']).'</user>';
    $xml .= '<text>'.htmlspecialchars($row['message']).'</text>';
	$xml .= '<time>'.$row['post_time'].'</time>';
	$xml .= '</message>';
  }
}
$xml .= '</root>';
print $xml;
?>
