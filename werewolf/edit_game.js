//alert ("Laoding edit_game.js")

function clear_edit() {
show_busy()
element = "edit_space"
var url="/edit_game.php"
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function edit_mod() {
show_busy()
element = 'edit_space';
var url = "/edit_game.php?q=e_moderator&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
}

function submit_Moderators() {
show_busy()
  element = "mod_td"
  str = ""
  mySelect = document.change_mod.elements[0]
  count=0
  for ( i=0; i<mySelect.options.length; i++ ) {
    if ( mySelect.options[i].selected) {
      if (count == 0 ) {
        str = mySelect.options[i].value
      } else {
        str = str+","+mySelect.options[i].value
      }
      count++
    }
  }
  var url="/edit_game.php?&q=s_moderator&modlist="+str+"&game_id="+game_id
  xmlHttp=GetXmlHttpObject(stateChanged)
  xmlHttp.open("GET", url , false)
  xmlHttp.send(null)
hide_busy()
clear_edit()
}

function edit_dates() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_date&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_dates() {
show_busy()
element = "date_td"
s_date = document.edit_date.start.value
if ( ! isDate(s_date, "yyyy-MM-dd") ) {
alert ("Start date is not a valid sql date.\nyyyy-mm-dd")
hide_busy()
return false
}
stime = document.edit_date.start_time.value
e_date = document.edit_date.end.value
if ( e_date != "" && e_date != "0000-00-00" && !isDate(e_date, "yyyy-MM-dd") ) {
alert ("End date is not a valid sql date.\nyyyy-mm-dd")
hide_busy()
return false
}
swf = document.edit_date.swf.value
if ( document.edit_date.swf.checked ) {
  swf = "Yes"
}
var url="/edit_game.php?q=s_date&sdate="+s_date+"&stime="+stime+"&edate="+e_date+"&swf="+swf+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_desc() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_description&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_desc() {
show_busy()
element = "desc_td"
descrip = document.new_descrip.desc.value
var url="/edit_game.php?q=s_description&desc="+descrip+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_status() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_status&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_status() {
show_busy()
element = "status_td"
I = document.new_status.status.selectedIndex
s = document.new_status.status.options[I].value
p = document.new_status.phase.value
d = document.new_status.day.value
isgood =  true
if ( s == "In Progress" && currentStatus != "In Progress" ) {
  isgood = confirm("Have you changed the BGG Thread id from the sign-up thread to the Game thread?  You must do this first before changing the status for the cassandra files to update correctly.")
}
if ( isgood ) {
var url="/edit_game.php?q=s_status&status="+s+"&phase="+p+"&day="+d+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}
hide_busy()
clear_edit()
if ( currentStatus != s ) {
location.href=myURL
}
} 

function edit_speed() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_speed&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
}

function submit_speed(){
show_busy()
element = "speed_td"
speed = document.new_speed.speed.value
var url="/edit_game.php?q=s_speed&speed="+speed+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
element = "deadline_td"
var url="/edit_game.php?q=s_deadline&speed="+speed+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
element = "date_td"
var url="/edit_game.php?q=s_date&speed="+speed+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
}

function edit_deadline() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_deadline&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_deadline() {
show_busy()
element = "deadline_td"
lynch = document.new_deadline.lynch.value
night = document.new_deadline.night.value
day_length = document.new_deadline.day_length.value
night_length = document.new_deadline.night_length.value
var url="/edit_game.php?q=s_deadline&lynch="+lynch+"&night="+night+"&day_length="+day_length+"&night_length="+night_length+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_winner() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_winner&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_winner() {
show_busy()
element = "win_td"
I = document.new_winner.winner.selectedIndex
w = document.new_winner.winner.options[I].value
var url="/edit_game.php?q=s_winner&winner="+w+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_subt() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_subthread&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function delete_subt(t_id) {
show_busy()
if ( confirm("Are you sure you want to delete this sub-thread?\nSaying yes will delete the game information, and all posts from the database.") ) {
element = "subt_td"
var url="/edit_game.php?q=d_subthread&thread_id="+t_id+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
}
hide_busy()
clear_edit()
} 

function add_subt() {
show_busy()
element = "subt_td"
t_id = document.new_subt.tid.value
if ( ! isNumber(t_id) ) {
alert ("This nees to be a BGG thread_id (numbers only)")
hide_busy()
return 
}
var url="/edit_game.php?q=a_subthread&thread_id="+t_id+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
alert("Please edit the specifics of the sub-thread on it's own page")
hide_busy()
clear_edit()
} 

function edit_name() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_name&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_name() {
show_busy()
element = "name_span"
t = document.new_title.title.value
var url="/edit_game.php?q=s_name&title="+t+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_thread() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_thread&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_thread() {
show_busy()
element = "thread_td"
th = document.new_thread.thread.value
if ( ! isNumber(th) ) {
alert ("This nees to be a BGG thread_id (numbers only)")
hide_busy()
return false
}
var url="/edit_game.php?q=s_thread&thread_id="+th+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)

myLink = document.getElementById('game_link')
myLink.href = "http://www.boardgamegeek.com/thread/"+th

alert("Since you changed the thread_id the page you are on is no longer a valid page, so hiting refresh will not work.")
} 

function edit_player(uid,row) {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_player&uid="+uid+"&row="+row+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function add_player() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=a_player&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
}

function edit_alias() {
show_busy()
element="edit_space"
var url="/edit_game.php?q=e_alias&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("Get",url,false)
xmlHttp.send(null)
hide_busy()
}

function edit_rolename() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_rolename&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function edit_roletype() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_roletype&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function edit_teams() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_team&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function edit_comments() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_comments&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function delete_replacement(replace_id) {
show_busy()
r = document.editPlayer.row_id.value
c = 0
element = "r"+r+"_c"+c
user_id = document.editPlayer.user_id.value
var url="/edit_game.php?q=d_replace&user_id="+user_id+"&replace_id="+replace_id+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function submit_player() {
show_busy()
element = "player_table"
uid = document.editPlayer.user_id.value
rep_id = document.editPlayer.new_rep.options[document.editPlayer.new_rep.selectedIndex].value
rep_p = document.editPlayer.rep_period.options[document.editPlayer.rep_period.selectedIndex].value
rep_n = document.editPlayer.rep_number.value
if ( rep_id != "0" ) {
  if ( rep_n == "" ) {
    alert("You need to specify which "+rep_p+" the player was replaced.")
	hide_busy()
	return false
  }
  if ( ! isNumber(rep_n) ) {
    alert("You need to enter a number for which "+rep_p+" the player was replaced.")
	hide_busy()
	return false
  }
}
player_alias = document.editPlayer.player_alias.value
alias_color = encodeURIComponent(document.editPlayer.alias_color.value, "UTF-8");
r_name = document.editPlayer.role_name.value
r_id = document.editPlayer.role_type.options[document.editPlayer.role_type.selectedIndex].value
s = document.editPlayer.side.options[document.editPlayer.side.selectedIndex].value
death_p = document.editPlayer.d_phase.value
death_d = document.editPlayer.d_day.value
note = document.editPlayer.comment.value
var url="/edit_game.php?q=s_player&uid="+uid+"&rep_id="+rep_id+"&rep_p="+rep_p+"&rep_n="+rep_n+"&player_alias="+player_alias+"&alias_color="+alias_color+"&r_name="+r_name+"&r_id="+r_id+"&side="+s+"&d_phase="+death_p+"&d_day="+death_d+"&comment="+note+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function submit_new_player() {
show_busy()
element = "player_table"
uid = document.getElementById('player_id_new_p').value
s = "old"
if ( uid == "" ) {
if ( confirm("This is a new player correct?") ) {
uid = document.getElementById('player_name_new_p').value
s = "new"
} else {
hide_busy()
return false;
}
}
var url="/edit_game.php?q=an_player&user_id="+uid+"&s="+s+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function delete_player() {
show_busy()
element = "player_table"
uid = document.editPlayer.user_id.value
var url="/edit_game.php?q=d_player&user_id="+uid+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function submit_alias() {
show_busy()
element = "player_table"
num = document.change_aliases.elements.length-1
aliases = ""
colors = ""
for ( i=0; i<num; i++ ) {
  aliases += document.change_aliases.elements[i].value
  i++;
  colors += encodeURIComponent(document.change_aliases.elements[i].value, "UTF-8");
  if ( i != (num-1) ) { 
    aliases += "," 
    colors += ","
  }
}
var url="/edit_game.php?q=s_alias&aliases="+aliases+"&colors="+colors+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function submit_rolename() {
show_busy()
element = "player_table"
num = document.change_rolenames.elements.length-1
rnames = ""
for ( i=0; i<num; i++ ) {
  rnames += document.change_rolenames.elements[i].value
  if ( i != (num-1) ) { rnames += "," }
}
var url="/edit_game.php?q=s_rolename&rnames="+rnames+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function submit_roletype() {
show_busy()
element = "player_table"
num = document.change_roletypes.elements.length-1
rtypes = ""
for ( i=0; i<num; i++ ) {
  rtypes += document.change_roletypes.elements[i].options[document.change_roletypes.elements[i].selectedIndex].value
  if ( i != (num-1) ) { rtypes += "," }
}
var url="/edit_game.php?q=s_roletype&rtypes="+rtypes+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function submit_team() {
show_busy()
element = "player_table"
num = document.change_teams.elements.length-1
teams = ""
for ( i=0; i<num; i++ ) {
  teams += document.change_teams.elements[i].options[document.change_teams.elements[i].selectedIndex].value
  if ( i != (num-1) ) { teams += "," }
}
var url="/edit_game.php?q=s_team&teams="+teams+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_maxplayers() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_maxplayers&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_maxplayers() {
show_busy()
element = "td_maxplayers"
mp = document.change_maxp.max_players.value
if ( ! isNumber(mp) ) {
  alert("This needs to be a number")
  hide_busy()
  return false
}
var url="/edit_game.php?q=s_maxplayers&max_players="+mp+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_deaths() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_deaths&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_deaths() {
show_busy()
element = "player_table"
num = document.change_deaths.elements.length-1
phases = ""
days = ""
for ( i=0; i<num; i++ ) {
  phases += document.change_deaths.elements[i].value
  i++
  days += document.change_deaths.elements[i].value
  if ( i != (num-1) ) { 
    phases += "," 
	days += ","
  }
}
var url="/edit_game.php?q=s_deaths&phases="+phases+"&days="+days+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

function edit_complex() {
show_busy()
element = "edit_space"
var url="/edit_game.php?q=e_complex&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
} 

function submit_complex() {
show_busy()
element = "td_complex"
comp = document.comp_form.complex.value
var url="/edit_game.php?q=s_complex&complex="+comp+"&game_id="+game_id
xmlHttp=GetXmlHttpObject(stateChanged)
xmlHttp.open("GET", url , false)
xmlHttp.send(null)
hide_busy()
clear_edit()
} 

