<?php
header("Content-type: image/png");

include_once "../../setup.php";
include_once(ROOT_PATH . "php/db.php");

// Set the enviroment variable for GD
putenv('GDFONTPATH=' . realpath('.'));

dbConnect();

$fontsize = 10;
$font = 'Vera';
#$username = $_REQUEST['username'];
$text = "test 123";

#$sql = "SELECT CONCAT(Games.number, ') ', Games.title) as game FROM Games, Players, Users WHERE Users.name='$username' AND Players.user_id = Users.id AND Players.game_id = Games.id AND Games.status ='Finished' ORDER BY Games.number;"

#$res = dbGetResult($sql);
#$total = dbGetResultRowCount($res);

#$text = "Games($total)\n"; 
#while($row=mysql_fetch_array($res))
#{
#	$text .= $text . $row['game'] . "\n";
#)


// Create the image
$size = imagettfbbox($fontsize, 0, $font, $text);
$width = $size[2] + $size[0] + 8;
$height = abs($size[1]) + abs($size[7]);

$im = imagecreate($width, $height); 

$colourBlack = imagecolorallocate($im, 255, 255, 255);
imagecolortransparent($im, $colourBlack);

// Create some colors
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);

// Add the text
magettftext($im, $fontsize, 0, 0, abs($size[5]), $black, $font, $text);

// Using imagepng() results in clearer text compared with 
imagepng($im);
imagedestroy($im);
?>
