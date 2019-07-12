var template = false

function add_room_dialog() {
  agent.call('','display_add_dialog','show_dialog',game_id);
}

function edit_room_dialog(room_id) {
  agent.call('','display_edit_dialog','show_dialog',game_id,room_id);
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

function delete_selected() {
  if ( confirm("This will delete the room and all messages in it.  Are you sure you want to do this?") ) {
    myForm = document.getElementById('all_rooms')
    for ( i=0; i<myForm.elements.length-1; i++ ) {
      myarray = myForm.elements[i].name.split('_')
      room_id = myarray[1]
	  delete_this = myForm.elements[i].checked
	  if ( delete_this ) {
	    agent.call('','delete_chat_room','',room_id)
	  }
    }
  }
  agent.call('','list_chat_rooms','update_room_list',game_id)
}

function update_room_list(str) {
  document.getElementById('room_list_div').innerHTML = str
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
