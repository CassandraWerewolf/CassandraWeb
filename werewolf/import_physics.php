<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "configure_physics_functions.php";
//include_once "menu.php";

$mysql = dbConnect();
#upgrading();

$game_id = $_REQUEST['import_game_id'];

//Make sure the person viewing this page is a moderator of the game.
$sql = sprintf("select * from Moderators where game_id=%s and user_id=%s",quote_smart($game_id),$uid);
$result = mysqli_query($mysql, $sql);
if ( mysqli_num_rows($result) != 1 ) {
	error("You must be the moderator of the game in order to import the Physics System.");
}

$sql = sprintf("select * from Games where id=%s",quote_smart($game_id));
$result = mysqli_query($mysql, $sql);
$game = mysqli_fetch_array($result);

$extension = end(explode(".", $_FILES["file"]["name"]));
if (($_FILES["file"]["type"] == "text/xml")
&& ($_FILES["file"]["size"]/1024 < 1024)
&& $extension =="xml")
  {
  if ($_FILES["file"]["error"] > 0)
    {
    error( "Error: " . $_FILES["file"]["error"] );
    }
  else
    {
	  $data = simplexml_load_file($_FILES["file"]["tmp_name"]);
	  if ($data) { xml_import_physics($game_id, $data); }
	  else { foreach(libxml_get_errors() as $error) {
        echo "\t", $error->message;
    } error ("Could not parse XML."); }	  
    }
  }
else
  {
   error("Invalid file");
  }

?>
