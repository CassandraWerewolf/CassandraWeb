<?php

include_once "php/db.php";
dbConnect();

function getPlayer( $text ) {
  $sql = sprintf("select id, name from Users where name LIKE %s and level != '0' order by name",quote_smart($text."%"));
  $result = mysql_query($sql);

  $listArray = array();
  $i=0;
  while ( $row = mysql_fetch_array($result) ) {
    $listArray[$i] = $row['name'];
	$i++;
    $listArray[$i] = $row['id'];
	$i++;
  }

  return $listArray;
}

function getGame( $text ) {
  #$sql = sprintf("select concat(if(number,number,'*'),') ',title) as name, thread_id from Games where title LIKE %s order by title",quote_smart("%".$text."%"));
  #$sql = sprintf("select concat(if(a.number,a.number,'*'),') ',b.title) as name, a.thread_id from Games a, Games_titles b where MATCH (b.title) AGAINST ('%s*' IN BOOLEAN MODE) order by b.title",quote_smart($text));
	$sql = '';
  $result = mysql_query($sql);

  $listArray = array();
  $i=0;
  while ( $row = mysql_fetch_array($result) ) {
    $listArray[$i] = $row['name'];
	$i++;
    $listArray[$i] = $row['thread_id'];
	$i++;
  }

  return $listArray;
}
?>
