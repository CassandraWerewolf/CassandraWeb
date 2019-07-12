<?php

include_once "autocomplete_functions.php";
include_once "php/agent.php";
include_once "php/htaccess.php";
$agent->init();

function player_autocomplete_form($id) {
$output = "<form name='myform$id'>\n";
$output .= "<table border='0'>\n";
$output .= "<tr><td><input type='text' name='player_name_$id' id='player_name_$id' onkeyup='getPlayer_$id();return false;' autocomplete='off' /></td></tr>\n";
$output .= "<tr><td><select id='player_matches_$id' style='position:absolute; visibility: hidden; z-index:1' onchange='player_matchSelected_$id(this);'></select></td></tr></table>\n";
$output .= "<input type='hidden' id='player_id_$id' value='' />\n";
$output .= "</form>\n";
return $output;
}

function player_autocomplete_js($id) {
$output = "";
$output .= "<script language='javascript'>\n";
$output .= "<!--\n";
$output .= "var player_matchList_$id = document.getElementById('player_matches_$id')\n";
$output .= "\n";
$output .= "function getPlayer_$id() {\n";
$output .= "player_matchList_$id = document.getElementById('player_matches_$id')\n";
$output .= "var playerName_$id = document.getElementById('player_name_$id').value\n";
$output .= "if ( playerName_$id == '' ) {\n";
$output .= "player_matchList_$id.style.visibility = 'hidden'\n";
$output .= "} else {\n";
$output .= "agent.call('','getPlayer','getPlayer_Callback_$id',playerName_$id)\n";
$output .= "}\n";
$output .= "}\n";
$output .= "function getPlayer_Callback_$id(obj) {\n";
$output .= "player_matchList_$id = document.getElementById('player_matches_$id')\n";
$output .= "player_matchList_$id.style.visibility = 'visible'\n";
$output .= "player_matchList_$id.options.length = 0\n";
$output .= "player_matchList_$id.size = 5\n";
$output .= "\n";
$output .= "for ( i=0; i<obj.length; i=i+2 ) {\n";
$output .= "player_matchList_$id.options[player_matchList_$id.options.length] = new Option(obj[i],obj[i+1])\n";
$output .= "}\n";
$output .= "player_matchList_$id.options[player_matchList_$id.options.length] = new Option('')\n";
$output .= "if ( obj.length == 0 ) player_matchList_$id.style.visibility = 'hidden'\n";
$output .= "}\n";
$output .= "function player_matchSelected_$id(player_matches_$id) {\n";
$output .= "player_matchList_$id = document.getElementById('player_matches_$id')\n";
$output .= "var playerName_$id = document.getElementById('player_name_$id')\n";
$output .= "  var playerID_$id = document.getElementById('player_id_$id')\n";
$output .= "playerName_$id.value = player_matches_$id.options[player_matches_$id.selectedIndex].text\n";
$output .= "  playerID_$id.value = player_matches_$id.options[player_matches_$id.selectedIndex].value\n";
$output .= "player_matchList_$id.size = 0\n";
$output .= "player_matchList_$id.style.visibility = 'hidden'\n";
$output .= "}\n";
$output .= "//-->\n";
$output .= "</script>\n";

return $output;
}

function game_autocomplete_form($id) {
$output = "<form name='myform$id'>\n";
$output .= "<table border='0' style='float:left'>\n";
$output .= "<tr><td><input type='text' size='50'  name='game_name_$id' id='game_name_$id' onkeyup='getGame_$id();return false;' autocomplete='off' /></td>\n";
$output .= "<tr>\n";
$output .= "<td><select id='game_matches_$id' style='position:absolute; visibility:hidden; z-index:1' onchange='game_matchSelected_$id(this);'></select></td>\n";
$output .= "</tr></table>\n";
$output .= "<input type='hidden' id='game_id_$id' value='' />\n";
$output .= "</form>\n";

return $output;
}

function game_autocomplete_js($id) {
$output = "<script language='javascript'>\n";
$output .= "<!--\n";
$output .= "var game_matchList_$id = document.getElementById('game_matches_$id')\n";
$output .= "\n";
$output .= "function getGame_$id() {\n";
$output .= "  game_matchList_$id = document.getElementById('game_matches_$id')\n";
$output .= "  var gameName_$id = document.getElementById('game_name_$id').value\n";
$output .= "  if ( gameName_$id == '' ) {\n";
$output .= "    game_matchList_$id.style.visibility = 'hidden'\n";
$output .= "  } else {\n";
$output .= "    agent.call('','getGame','getGame_Callback_$id',gameName_$id)\n";
$output .= "  }\n";
$output .= "}\n";
$output .= "function getGame_Callback_$id(obj) {\n";
$output .= "  game_matchList_$id = document.getElementById('game_matches_$id')\n";
$output .= "  game_matchList_$id.style.visibility = 'visible'\n";
$output .= "  game_matchList_$id.options.length = 0\n";
$output .= "  game_matchList_$id.size = 5\n";
$output .= "\n";
$output .= "  for ( i=0; i<obj.length; i=i+2 ) {\n";
$output .= "    game_matchList_$id.options[game_matchList_$id.options.length] = new Option(obj[i],obj[i+1])\n";
$output .= "  }\n";
$output .= "    game_matchList_$id.options[game_matchList_$id.options.length] = new Option('')\n";
$output .= "	if ( obj.length == 0 ) game_matchList_$id.style.visibility = 'hidden'\n";
$output .= "}\n";
$output .= "function game_matchSelected_$id(game_matches_$id) {\n";
$output .= "  game_matchList_$id = document.getElementById('game_matches_$id')\n";
$output .= "  var gameName_$id = document.getElementById('game_name_$id')\n";
$output .= "  var gameID_$id = document.getElementById('game_id_$id')\n";
$output .= "  gameName_$id.value = game_matches_$id.options[game_matches_$id.selectedIndex].text\n";
$output .= "  gameID_$id.value = game_matches_$id.options[game_matches_$id.selectedIndex].value\n";
$output .= "  game_matchList_$id.size = 0\n";
$output .= "  game_matchList_$id.style.visibility = 'hidden'\n";
$output .= "}\n";
$output .= "//-->\n";
$output .= "</script>\n";

return $output;
}
?>
