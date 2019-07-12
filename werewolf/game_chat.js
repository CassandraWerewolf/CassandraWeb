var sendReq = getXmlHttpRequestObject();
var receiveReq = getXmlHttpRequestObject();
var lastMessage = 0;
var mTimer;
var userlist = new Array();
var view_full = false
var dir = "/";
//var dir = "/dev_";

//Function for initializing the page.
function startChat() {
  //Set the focus to the Message Box.
  document.getElementById('text').focus();
  //Start Recieving Messages.
  getChatText();
}

//Gets the browser specific XmlHttpRequest Object
function getXmlHttpRequestObject() {
  if (window.XMLHttpRequest) {
    return new XMLHttpRequest();
  } else if(window.ActiveXObject) {
    return new ActiveXObject("Microsoft.XMLHTTP");
  } else {
    alert('Status: Cound not create XmlHttpRequest Object.  Consider upgrading your browser.')
  }
}

//Gets the current messages from the server
function getChatText() {
  if (receiveReq.readyState == 4 || receiveReq.readyState == 0) {
    room_id = document.getElementById('room_id').value
	if ( room_id != 0 ) {
      receiveReq.open("GET", dir+'get_game_chat.php?room_id='+room_id+'&last='+lastMessage, true);
      receiveReq.onreadystatechange = handleReceiveChat;
      receiveReq.send(null);
	}
  }
}

//Function for handling the return of chat text
function handleReceiveChat() {
  if (receiveReq.readyState == 4) {
    var chat_div = document.getElementById('div_chat');
    var xmldoc = receiveReq.responseXML;
    //var xmldoc = receiveReq.responseText;
    var message_nodes = xmldoc.getElementsByTagName("message");
    var n_messages = message_nodes.length
    for (i = 0; i < n_messages; i++) {
      var user_node = message_nodes[i].getElementsByTagName("user");
      var text_node = message_nodes[i].getElementsByTagName("text");
      var color_node = message_nodes[i].getElementsByTagName("color");
      var bgcolor_node = message_nodes[i].getElementsByTagName("bgcolor");
      var time_node = message_nodes[i].getElementsByTagName("time");

      chat_div.innerHTML += '<span style="font-weight:bold; color: '+color_node[0].firstChild.nodeValue+'; background-color:'+bgcolor_node[0].firstChild.nodeValue+';">['+user_node[0].firstChild.nodeValue + '&nbsp;' + time_node[0].firstChild.nodeValue + ']</span> ';
      chat_div.innerHTML += text_node[0].firstChild.nodeValue + '<br />';
      chat_div.scrollTop = chat_div.scrollHeight;
      lastMessage = (message_nodes[i].getAttribute('id'));
    }
    update_room_stats();
    mTimer = setTimeout('getChatText();',1000); //Refresh our chat in 2 seconds
  }
}

//Add a message to the chat server.
function sendChatText() {
  room_id = document.getElementById('room_id').value
  if ( document.getElementById('read_only').value ) {
    alert("This Room has been locked.  You can not post.")
    document.getElementById('text').value = ''
    return false;
  }
  if(document.getElementById('text').value == '') {
    alert("You have not entered a message");
    return;
  }
  if (sendReq.readyState == 4 || sendReq.readyState == 0) {
    sendReq.open("POST", dir+'get_game_chat.php?room_id='+room_id+'&last='+lastMessage, true);
    sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    sendReq.onreadystatechange = handleSendChat;
    var param = 'message=' + escape(document.getElementById('text').value);
    sendReq.send(param);
    document.getElementById('text').value = '';
  }
}

//When our message has been sent, update our page.
function handleSendChat() {
  //Clear out the existing timer so we don't have
  //multiple timer instances running.
  clearInterval(mTimer);
  getChatText();
}

function blockSubmit() {
  if ( view_full ) {
    view_full = false;
    return true;
  } else {
    sendChatText();
    return false;
  }
}

function enter_submit(evt) {
   evt = ( evt ) ? evt : event;
   var charCode = (evt.charCode) ? evt.charCode : (( evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
   if ( charCode == 13 ) {
     sendChatText()
     return false;
   }
   return true;
}

function switch_tab(tab_num) {
  agent.call('','show_tab','change_tab',tab_num,user_id,game_id)
  agent.call('','display_tabs','change_tabs',tab_num,is_mod)
  current_tab = tab_num
}

function change_tab(obj) {
  document.getElementById('tab_window').innerHTML = obj
}

function change_tabs(obj) {
  document.getElementById('tab_navigation').innerHTML = obj
}

function show_room_list(game_id,user_id) {
  agent.call('','room_list','display_room_list',game_id,user_id)
}

function show_broadcast(){
  agent.call('','broadcast_form','display_mod_div',game_id)
}

function show_room_edit() {
  room_id = document.getElementById('room_id').value
  agent.call('','room_edit_form','display_mod_div',game_id,room_id)
}

function show_room_add() {
  room_id = document.getElementById('room_id').value
  agent.call('','display_add_dialog','display_mod_div',game_id,room_id)
}

function show_goa() {
  agent.call('','display_goa','display_mod_div',game_id) 
}

function display_mod_div(obj) {
  document.getElementById('mod_control_div').innerHTML = obj
}

function display_room_list(obj){
  document.getElementById('room_nav').innerHTML = obj
  document.getElementById('room_nav').style.visibility='visible'
}

function close_div(name) {
  document.getElementById(name).style.visibility='hidden'
}

function select_room_change() {
  myroom_id = document.getElementById('change_room').value
  change_room(myroom_id)
}
function change_room(myroom_id) {
  if ( document.getElementById('change_room').value != myroom_id ) {
    document.getElementById('change_room').value = myroom_id
  }
  agent.call('','display_chat_room','change_chat_room',myroom_id,user_id)
  if ( myroom_id == 0 ) {
    document.getElementById('player_list').style.visibility='hidden'
  } else {
    document.getElementById('player_list').style.visibility='visible'
  }
  switch_tab(current_tab)
}

function change_chat_room(obj) {
  document.getElementById('chat_window').innerHTML = obj
  lastMessage = 0;
  clearInterval(mTimer);
  document.getElementById('text').focus();
  getChatText();
}

function update_room_stats() {
  room_id = document.getElementById('room_id').value
  //alert(room_id)
  agent.call('','list_players','update_players',room_id, 0, user_id)
}

function update_players(obj) {
  document.getElementById('player_list').innerHTML = obj
}

//  Format Functions

function format_around(open,close) {
  txtarea = document.getElementById('text')
  a = (txtarea.value).substring(0,txtarea.selectionStart)
  b = (txtarea.value).substring(txtarea.selectionStart,txtarea.selectionEnd)
  c = (txtarea.value).substring(txtarea.selectionEnd,txtarea.value.length)
  document.getElementById('text').value = a+open+b+close+c
}

function geekMail() {
  room_id = document.getElementById('room_id').value
    location.href=dir+'geekmail_chat.php?room_id='+room_id
	}

function entireChat() {
  view_full = true
}

function mark_as_read() {
  room_id = document.getElementById('room_id').value
  //game_id = document.getElementById('game_id').value
  action = "read_all"
  if (sendReq.readyState == 4 || sendReq.readyState == 0) {
    receiveReq.open("GET", dir+'get_game_chat.php?game_id='+game_id+'&room_id='+room_id+'&last='+lastMessage+"&action="+action, true);
    receiveReq.onreadystatechange = handleReceiveChat;
    receiveReq.send(null);
  }
  switch_tab(1)
  return false;
}

//Add a message to the chat server.
function sendBroadcastText() {
  if(document.getElementById('broad_text').value == '') {
    alert("You have not entered a message");
    return false;
  }
  if ( document.getElementById('post_to_all').checked ) {
    if (sendReq.readyState == 4 || sendReq.readyState == 0) {
	  sendReq.open("POST", dir+'get_game_chat.php?game_id='+game_id+'&room_id='+room_id+'&last='+lastMessage, true);
	  sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	  sendReq.onreadystatechange = handleSendChat;
	  var param = 'message=' + escape(document.getElementById('broad_text').value);
	  sendReq.send(param);
	  document.getElementById('broad_text').value = '';
	}
	return false;
  }
  room_id = ""
  for(i=0;i<room_list.length;i++) {
	if ( document.getElementById('post_to_'+room_list[i]).checked ) {
	  if ( room_id == "" ) { 
	    room_id = room_list[i]
	  } else {
	    room_id += ","+room_list[i]
	  }
	}
  }
  if (sendReq.readyState == 4 || sendReq.readyState == 0) {
    sendReq.open("POST", dir+'get_game_chat.php?room_id='+room_id+'&last='+lastMessage, true);
    sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    sendReq.onreadystatechange = handleSendChat;
    var param = 'message=' + escape(document.getElementById('broad_text').value);
    sendReq.send(param);
    document.getElementById('broad_text').value = '';
  }
  return false;
}


function enter_broadcast(evt) {
   evt = ( evt ) ? evt : event;
   var charCode = (evt.charCode) ? evt.charCode : (( evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
   if ( charCode == 13 ) {
     sendBroadcastText()
     return false;
   }
   return true;
}

function lock_ga() {
  sure = confirm("Are you sure you want to lock your game action?  While locked, you will not be able to change it.  The Order form will be locked until the next Dawn is posted and read by cassy, or if you unlock it.")
  if ( sure ) {
    location.href="/game_action.php?user_id="+user_id+"&game_id="+game_id+"&action=lock"
  }
}

function unlock_ga() {
  sure = confirm("Are you sure you want to unlock your game action?  You will be able to change your actions, but dawn will not be able to be processed until either the regularly scheduled time or until you relock it.")
  if ( sure ) {
    location.href="/game_action.php?user_id="+user_id+"&game_id="+game_id+"&action=unlock"
  }
}


