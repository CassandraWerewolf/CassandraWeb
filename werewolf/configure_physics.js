var template = false

function switch_tab(tab_num) {
  agent.call('','show_physics_tab','change_tab',tab_num,user_id,game_id)
  agent.call('','display_physics_tabs','change_tabs',tab_num)
  current_tab = tab_num
}

function change_tab(obj) {
  document.getElementById('tab_window').innerHTML = obj
}

function change_tabs(obj) {
  document.getElementById('tab_navigation').innerHTML = obj
}

function add_item_dialog() {
  agent.call('','display_add_items','show_dialog',game_id);
}
function edit_item_dialog(id) {
  agent.call('','display_edit_items','show_dialog',game_id,id);
}

function add_temp_dialog() {
  agent.call('','display_add_item_temps','show_dialog',game_id);
}
function edit_temp_dialog(id) {
  agent.call('','display_edit_item_temps','show_dialog',game_id,id);
}

function add_exit_dialog() {
  agent.call('','display_add_exits','show_dialog',game_id);
}
function edit_exit_dialog(id) {
  agent.call('','display_edit_exits','show_dialog',game_id,id);
}

function add_loc_dialog() {
  agent.call('','display_add_locs','show_dialog',game_id);
}
function edit_loc_dialog(id) {
  agent.call('','display_edit_locs','show_dialog',game_id,id);
}

function add_player_dialog() {
  agent.call('','display_add_players','show_dialog',game_id);
}
function edit_player_dialog(id) {
  agent.call('','display_edit_players','show_dialog',game_id,id);
}


function edit_eye_dialog(room_id,user_id) {
  agent.call('','display_eye_dialog','show_dialog',game_id,room_id,user_id);
}

function show_dialog(str) {
  document.getElementById('dialog_div').innerHTML = str
}

function dawn_chat_reset() {
  agent.call('','change_dawn_reset','',game_id)
}

function lock_room(room_id) {
  lock_image =  document.getElementById('lock_img_'+room_id)
  agent.call('','lock_room','change_lock',room_id);
}

function change_lock(obj) {
  if ( obj == "On" ) {
    lock_image.src = "/images/lock_green.gif"
  } else if ( obj == "Secure" ) {
    lock_image.src = "/images/lock_red.gif"
  } else {
    lock_image.src = "/images/unlock.gif"
  }
}

function lock_player(room_id,user_id) {
  lock_image =  document.getElementById('lock_img_'+room_id+"_"+user_id)
  agent.call('','lock_player','change_lock',room_id,user_id);
}

function eye_player(room_id,user_id) {
  eye_image =  document.getElementById('eye_img_'+room_id+"_"+user_id)
  agent.call('','eye_player','',room_id,user_id);
  agent.call('','list_chat_rooms','update_room_list',game_id)
  show_room_edit()
}


function reset_room(room_id) {
  agent.call('','reset_room','',room_id)
  agent.call('','list_chat_rooms','update_room_list',game_id)
  show_room_edit()
}

function reset_player(room_id,user_id) {
  agent.call('','reset_user','',room_id,user_id)
  agent.call('','list_chat_rooms','update_room_list',game_id)
  show_room_edit()
}

function delete_selected_exits() {
  if ( confirm("This will delete the exit.  Are you sure you want to do this?") ) {
    myForm = document.getElementById('all_exits')
    for ( i=0; i<myForm.elements.length-1; i++ ) {
      myarray = myForm.elements[i].name.split('_')
      id = myarray[1]
	  delete_this = myForm.elements[i].checked
	  if ( delete_this ) {
	    agent.call('','delete_exit','',id)
	  }
    }
  }
  agent.call('','list_exits','update_list',game_id)
}

function delete_selected_items() {
  if ( confirm("This will delete the item.  Are you sure you want to do this?") ) {
    myForm = document.getElementById('all_items')
    for ( i=0; i<myForm.elements.length-1; i++ ) {
      myarray = myForm.elements[i].name.split('_')
      id = myarray[1]
      delete_this = myForm.elements[i].checked
      if ( delete_this ) {
        agent.call('','delete_item','',id)
      }
    }
  }
  agent.call('','list_items','update_list',game_id)
}

function delete_selected_temps() {
  if ( confirm("This will delete the item template, and all items using this template.  Are you sure you want to do this?") ) {
    myForm = document.getElementById('all_temps')
    for ( i=0; i<myForm.elements.length-1; i++ ) {
      myarray = myForm.elements[i].name.split('_')
      id = myarray[1]
      delete_this = myForm.elements[i].checked
      if ( delete_this ) {
        agent.call('','delete_item_temp','',id)
      }
    }
  }
  agent.call('','list_item_temps','update_list',game_id)
}

function delete_selected_locs() {
  if ( confirm("This will delete the location. All items in this location will be unowned, and players will be considered nowhere. Are you sure you want to do this?") ) {
    myForm = document.getElementById('all_locs')
    for ( i=0; i<myForm.elements.length-1; i++ ) {
      myarray = myForm.elements[i].name.split('_')
      id = myarray[1]
      delete_this = myForm.elements[i].checked
      if ( delete_this ) {
        agent.call('','delete_loc','',id)
      }
    }
  }
  agent.call('','list_locs','update_list',game_id)
}


function update_list(str) {
  document.getElementById('list_div').innerHTML = str
}


function warn_delete(myCheckbox,id,ovalue) {
  if ( ! myCheckbox.checked ) {
  uncheck = confirm("Unchecking this player will delete them and all their post from the chat.  Are you sure you want to do this?")
    if ( uncheck ) {
    myCheckbox.value = 'off'
	myCheckbox.checked = false
	document.getElementById('player_open_'+id).value = ""
	} else {
    myCheckbox.value = 'on'
	myCheckbox.checked = true
	document.getElementById('player_open_'+id).value = ovalue
	}
  } else {
    myCheckbox.value = 'on'
	myCheckbox.checked = true
	document.getElementById('player_open_'+id).value = ovalue
  }
}

function warn_delete_submit() {
  if ( confirm("This will delete the room and all messages in it.  Are you sure you want to do this?") ) {
    myform.submit()
    return true
  }
  return false
}

function warn_delete_phys_submit() {
  if ( confirm("This will delete the physics object and all related objects.  Are you sure you want to do this?") ) {
    myform.submit()
    return true
  }
  return false
}

function check_this_box(id) {
  color = document.getElementById('color_'+id).value
  if ( color != "" ) {
    document.getElementById('player_'+id).checked = true
    document.getElementById('player_'+id).value = 'on'
  }
}

function template_notice(view_id) {
  if ( view_id != 306 ) {
    template = true
  }
}

function check_template() {
  if ( template ) {
    return confirm ("Have you set the colors above as you wish to have them for all created chats?  Use the first player for the Mod chat rooms and the first two players for the Player to Player chat rooms.")
  } 
  return true
  
}
