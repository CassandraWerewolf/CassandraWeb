//alert ("Laoding edit_profile.js")

var xmlHttp
var element
if ( myURL == "/profile/"+player || myURL == "/dev_profile/"+player ) {
  var dir = "../"
} else {
  var dir = ""
}
dir = "/";

function clear_edit() {
show_busy()
element = "edit_space"
var url=dir+"edit_profile.php"
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 


function edit_field(field) {
show_busy()
element = "edit_space"
var url=dir+"edit_profile.php?q=edit&field="+field+"&user_id="+player_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_field() {
show_busy()
field = document.edit_form.field.value
value = escape(document.edit_form.elements[1].value)
element = field+"_td"
var url=dir+"edit_profile.php?q=submit&field="+field+"&value="+value+"&user_id="+player_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
}
