<?php

include_once "php/db.php";
$mysql = dbConnect();

$state =  $_REQUEST['state'];
if ( $state == 'open' ) {
  $modlist = split ( ", ", $_REQUEST['q'] );

  $output = "<form name='change_mod'>\n";
  $output .= "<select name='moderator[]' size='4' multiple>\n";

  foreach ( $modlist as $name ) {
    $result = mysqli_query("select id from Users where name='$name' ");
    $id[] = mysqli_result($result,0,0);
  }

  $sql="Select id, name from Users order by name";
  $result = mysqli_query($mysql, $sql);

  $i = 0;
  while ( $row = mysqli_fetch_array($result) ) {
    $selected = "";
    if ( $row['id'] == $id[$i] ) {
      $selected = "selected";
	  $i++;
    }
    $output .= "<option $selected value='".$row['id']."' />".$row['name']."\n";
  }

  $output .= "</select>\n";
  $output .= "<input type=button value='submit' name='submit' onClick='submitModerators()' />\n";
  $output .= "</form>\n";

  print $output;
} else {

  $newidlist = split( ",", $_REQUEST['q']);
  sort($newidlist);
  $game_id = $_REQUEST['gameid'];

  $sql = "select user_id from Games, Moderators where Games.id = Moderators.game_id and Games.id = $game_id";
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $oldidlist[] = $row['user_id'];
  }

# Find Id's that need to be added.
  foreach ( $newidlist as $newid ) {
    $found = false;
    foreach ( $oldidlist as $oldid ) {
      if ( $newid == $oldid ) $found = true;
    }
    if ( ! $found ) $addlist[] = $newid;
  }

  if ( $addlist[0] != "" ) {
  foreach ( $addlist as $id ) {
    $sql = "insert into Moderators ( user_id, game_id ) values ( '$id', '$game_id' )";
    $result = mysqli_query($mysql, $sql);
  }
  }

# Find id's that need to be deleted.
  foreach ( $oldidlist as $oldid ) {
    $found = false;
    foreach ( $newidlist as $newid ) {
      if ( $newid == $oldid ) $found = true;
    }
    if ( ! $found ) $dellist[] = $oldid;
  }

  if ( $dellist[0] != "" ) {
  foreach ( $dellist as $id ) {
    $sql = "delete from Moderators where user_id='$id' and game_id='$game_id'";
    $result = mysqli_query($mysql, $sql);
  }
  }

# Return to original output
  $sql = "Select id, name from Users, Moderators where Users.id=Moderators.user_id and Moderators.game_id='$game_id' order by name";
  $result = mysqli_query($mysql, $sql);
  $count = 0;
  $modlist = "";
  while ( $mod = mysqli_fetch_array($result) ) {
    ( $count == 0 ) ? $modlist = $mod['name'] : $modlist .= ", ".$mod['name'];
    $count++;
  }

  print "<div onClick='changeModerators(\"$modlist\")';>$modlist</div>";
}
?>
