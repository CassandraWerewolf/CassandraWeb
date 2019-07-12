var xmlHttp
var element

if ( myURL == "/cc_game/"+thread_id ) {
  var dir = "../"
} else {
  var dir = ""
}


function show_challenge() {
element="cc_space"
document.getElementById(element).style.visibility='visible'
var url=dir+"cc_challenge.php?game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}

