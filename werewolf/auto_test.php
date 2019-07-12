<?php

include_once "autocomplete_functions.php";
include_once "php/agent.php";
$agent->init();

$id = 1;
?>
<html>
<head>
</head>
<body>
<form name='myform<?=$id;?>'>
<table border='0' style='float:left'>
<tr><td><input type='text' name='player_name_<?=$id;?>' id='player_name_<?=$id;?>' onkeyup='getPlayer_<?=$id;?>();return false;' autocomplete='off' /></td></tr>
<tr><td><select id='player_matches_<?=$id;?>' style='position:absolute; visibility: hidden; z-index:1' onchange="player_matchSelected_<?=$id;?>(this);"></select></td></tr></table>
</form>
<script language='javascript'>
<!--
var player_matchList_<?=$id;?> = document.getElementById("player_matches_<?=$id;?>")

function getPlayer_<?=$id;?>() {
  var playerName_<?=$id;?> = document.getElementById('player_name_<?=$id;?>').value
  if ( playerName_<?=$id;?> == "" ) {
    player_matchList_<?=$id;?>.style.visibility = "hidden"
  } else {
    agent.call('','getPlayer','getPlayer_Callback_<?=$id;?>',playerName_<?=$id;?>)
  }
}
function getPlayer_Callback_<?=$id;?>(obj) {
  player_matchList_<?=$id;?>.style.visibility = "visible"
  player_matchList_<?=$id;?>.options.length = 0
  player_matchList_<?=$id;?>.size = 5

  for ( i=0; i<obj.length; i++ ) {
    player_matchList_<?=$id;?>.options[player_matchList_<?=$id;?>.options.length] = new Option(obj[i])
  }
    player_matchList_<?=$id;?>.options[player_matchList_<?=$id;?>.options.length] = new Option("")
    if ( obj.length == 0 ) player_matchList_<?=$id;?>.style.visibility = "hidden"
}
function player_matchSelected_<?=$id;?>(player_matches) {
  var playerName_<?=$id;?> = document.getElementById("player_name_<?=$id;?>")
  playerName_<?=$id;?>.value = player_matches_<?=$id;?>.options[player_matches_<?=$id;?>.selectedIndex].text
  player_matchList_<?=$id;?>.size = 0
  player_matchList_<?=$id;?>.style.visibility = "hidden"
}
//-->
</script>
</body>
</html>
