var edit_space_id = "edit_space"

function refresh_page() {
  alert("here2")
  location.href=domain+"/game/"+thread_id
}

function change_edit_space(obj) {
  document.getElementById(edit_space_id).innerHTML = obj
  if ( edit_space_id != "edit_space" ) { 
    clear_edit();
   }
}

function clear_edit() {
  edit_space_id = "edit_space"
  agent.call('','clear_editSpace','change_edit_space');
}

function get_edit_form(form_name) {
  edit_space_id = "edit_space"
  agent.call('',form_name,'change_edit_space',game_id);
}

function change_modcontrol_space(obj) {
  document.getElementById('control_space').style.visibility='visible'
  document.getElementById('control_space').innerHTML = obj
}

function close() {
  document.getElementById('control_space').style.visibility='hidden'
}

function get_modcontrol_form(form_name) {
  agent.call('',form_name,'change_modcontrol_space',game_id);
}

function submit_name() {
  t = document.new_title.title.value
  edit_space_id = "name_div"
  agent.call('','name_submit','change_edit_space',game_id,t)
}

function submit_mod() {
  mySelect = document.change_mod.elements[0]
  count=0
  for ( i=0; i<mySelect.options.length; i++ ) {
    if ( mySelect.options[i].selected) {
      if (count == 0 ) {
        modlist = mySelect.options[i].value
      } else {
        modlist = modlist+","+mySelect.options[i].value
      }
      count++
    }
  }
  edit_space_id = "mod_td"
  agent.call('','mod_submit','change_edit_space',game_id,modlist);
}

function submit_dates() {
  s_date = document.edit_date.start.value
  if ( ! isDate(s_date, "yyyy-MM-dd") ) {
    alert ("Start date is not a valid sql date.\nyyyy-mm-dd")
    return false
  }
  e_date = document.edit_date.end.value
  if ( e_date != "" && e_date != "0000-00-00" && !isDate(e_date, "yyyy-MM-dd") ) {
    alert ("End date is not a valid sql date.\nyyyy-mm-dd")
    return false
  }
  edit_space_id = "dates_td"
  agent.call('','dates_submit','change_edit_space',game_id,s_date,e_date)
}

function submit_status() {
  I = document.new_status.status.selectedIndex
  s = document.new_status.status.options[I].value
  p = document.new_status.phase.value
  d = document.new_status.day.value
  isgood =  true
  if ( s == "In Progress" && currentStatus != "In Progress" ) {
    isgood = confirm("Have you changed the BGG Thread id from the sign-up thread to the Game thread?  You must do this first before changing the status for the cassandra files to update correctly.")
  }
  if ( isgood ) {
    currentStatus = s
    edit_space_id = "status_td"
    agent.call('','status_submit','change_edit_space',game_id,s,p,d)
  }
}

function submit_deadline() {
  lynch = document.new_deadline.lynch.value
  night = document.new_deadline.night.value
  edit_space_id = "deadline_td"
  agent.call('','deadline_submit','change_edit_space',game_id,lynch,night)
}

function submit_maxplayers() {
  mp = document.change_maxp.max_players.value
  if ( ! isNumber(mp) ) {
    alert("This needs to be a number")
    return false
  }
  edit_space_id = "maxplayers_td"
  agent.call('','maxplayers_submit','change_edit_space',game_id,mp)
}

function submit_complex() {
  comp = document.comp_form.complex.value
  edit_space_id = "complex_td"
  agent.call('','complexity_submit','change_edit_space',game_id,comp)
}

function submit_winner() {
  I = document.new_winner.winner.selectedIndex
  w = document.new_winner.winner.options[I].value
  edit_space_id = "win_td"
  agent.call('','winner_submit','change_edit_space',game_id,w)
}

function submit_thread() {
  th = document.new_thread.thread.value
  if ( ! isNumber(th) ) {
    alert ("This nees to be a BGG thread_id (numbers only)")
    return false
  }
  thread_id = th
  agent.call('','thread_submit','refresh_page',game_id,th)
}

function submit_subt(thread,action) {
  if (action == "add" ) {
    thread = document.new_subt.tid.value
    if ( ! isNumber(thread) ) {
      alert ("This nees to be a BGG thread_id (numbers only)")
      return
    }
  } else if (action == "delete" ) { 
    if ( !confirm("Are you sure you want to delete this sub-thread?\nSaying yes will delete the game information, and all posts from the database.") ) {
      return false
    }
  } else {
    return false
  }
  edit_space_id = "subt_td"
  agent.call('','subt_submit','change_edit_space',game_id,thread,action)
}

function submit_desc() {
  descrip = document.new_descrip.desc.value
  edit_space_id = "desc_td"
  agent.call('','desc_submit','change_edit_space',game_id,descrip)
}

function submit_vote_tally(action) {
  if ( action == "activate" ) {
    tb = document.tiebreaker.tieb.value
  }
  if (action == "retrieve" ) {
    tb = "x"
    isDusk = confirm("Have you posted [Dusk] in the thread?")
    if ( isDusk ) {
      alert("It may take up to a minute for the final vote tally to be posted.")
    } else {
      return
    }
  }
  agent.call('','vote_tally_submit','close',game_id,action,tb)
}

function submit_mpw() {
  alert("here")
  hr = document.missing.hr.value
  agent.call('','mpw_submit','refresh_page',game_id,hr)
}

function delete_game() {
  location.href = dir+"delete_game.php?game_id="+game_id
}

function pm_players() {
agent.call('','pm_players_form','display_pm',game_id)
}

function display_pm(obj){
  document.getElementById('PM_div').style.visibility='visible'
  document.getElementById('PM_div').innerHTML = obj
}

function close_pm() {
  document.getElementById('PM_div').style.visibility='hidden'
  document.getElementById('PM_div').innerHTML = ""
}
