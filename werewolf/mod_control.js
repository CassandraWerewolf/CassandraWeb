// These functions are used for the Moderator Control Panel on the Game pages.

var xmlHttp
var element

if ( myURL == "/game/"+thread_id || myURL == "/dev_game/"+thread_id || myURL == "/cc_game/"+thread_id ) {
  var dir = "../"
} else {
  var dir = ""
}
if ( myURL == "/dev_game/"+thread_id ) {
  dir=dir+"dev_"
}

function rand_assign() {
element="control_space"
document.getElementById(element).style.visibility='visible'
var url=dir+"assign_roles.php?game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}

function activate_al() {
element="control_space"
document.getElementById(element).style.visibility='visible'
var url=dir+"alias.php?game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}

function activate_goa() {
element="control_space"
document.getElementById(element).style.visibility='visible'
var url=dir+"game_order_assistant.php?game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}

function activate_ad() {
element="control_space"
document.getElementById(element).style.visibility='visible'
var url=dir+"auto_dusk.php?game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}

function activate_vt() {
  myelement = document.getElementById("control_space")
  myelement.style.visibility='visible'
  myelement.innerHTML = "<form name='tiebreaker'>Choose a Tie Breaker:<br/><select name='tieb'><option value='lhlv' />Longest Held Last Vote<option value='lhv' />Longest Held Vote</select><br />Allow Nightfall votes? <input type='checkbox' name='allow_nightfall' id='allow_nightfall' checked=1 /><br />Allow No Kill votes? <input type='checkbox' name='allow_nolynch' id='allow_nolynch' checked=1 /><br /><input type='button' value='submit' onClick='javascript:submit_activate_vt()'></form>"
}

function submit_activate_vt() {
  myelement = document.getElementById("control_space")
  myelement.style.visibility='hidden'
  tb = document.tiebreaker.tieb.value
  nf = document.tiebreaker.allow_nightfall.checked
  nl = document.tiebreaker.allow_nolynch.checked
  //alert("You have chosen "+tb+ " as your tiebreaker")
  location.href=dir+"vote_tally.php?action=activate&tieb="+tb+"&nf="+nf+"&nl="+nl+"&game_id="+game_id+"&from="+myURL
}

function retrieve_vt() {
  alert("It may take up to a minute for the vote tally to be posted.")
  location.href=dir+"vote_tally.php?action=retrieve&game_id="+game_id+"&from="+myURL
}

function activate_aliases() {
  activate = confirm("Are you sure, this can not be undone.")
  if ( activate ) {
    alert("You must now assign all players different Aliases")
    location.href=dir+"activate_aliases.php?game_id="+game_id
  }
}

function delete_game() {
  location.href = dir+"delete_game.php?game_id="+game_id
}

function activate_mpw() {
  myelement = document.getElementById("control_space")
  myelement.style.visibility='visible'
  myelement.innerHTML = "<form name='missing'>Warn if a player hasn't posted in<input type='text' size='2' name='hr' /> hours.<br /><input type='button' value='submit' onClick='javascript:submit_activate_mpw()'><span align='right'><a href='javascript:close(\"control_space\")'>[close]</a></span></form>"
}

function submit_activate_mpw() {
  myelement = document.getElementById("control_space")
  myelement.style.visibility='hidden'
  hr = document.missing.hr.value
  //alert("You have chosen "+hr+ " as your warning hr")
  location.href=dir+"missing_warning.php?action=activate&hr="+hr+"&game_id="+game_id+"&from="+myURL
}


function reset_cassy() {
  location.href=dir+"reset_cassy.php?game_id="+game_id+"&from="+myURL
}
