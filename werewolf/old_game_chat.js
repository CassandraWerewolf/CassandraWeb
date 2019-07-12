var sendReq = getXmlHttpRequestObject();
var receiveReq = getXmlHttpRequestObject();
var lastMessage = 0;
var mTimer;
var userlist = new Array();
var view_full = false
var dir = "/";
//var dir = "/old_";

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
    receiveReq.open("GET", dir+'get_game_chat.php?room_id='+room_id+'&last='+lastMessage, true);
    receiveReq.onreadystatechange = handleReceiveChat;
    receiveReq.send(null);
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
  if ( document.getElementById('lock_img_'+room_id) != null ) {
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

function change_rooms(room_id,room_name,room_created,user_view) {
    var chat_div = document.getElementById('div_chat');
	chat_div.innerHTML = "";
	document.getElementById('room_name').innerHTML = room_name;
	document.getElementById('room_created').innerHTML = "Created: "+room_created;
	document.getElementById('user_view').innerHTML = user_view;
	document.getElementById('room_id').value = room_id;
	lastMessage = 0;
    clearInterval(mTimer);
    document.getElementById('text').focus();
	getChatText();
}

function update_room_stats() {
  agent.call('','show_chatRooms','update_rooms',game_id,user_id);
}

function update_rooms(obj) {
  document.getElementById('rooms_td').innerHTML = obj
}

function geekMail() {
  room_id = document.getElementById('room_id').value
  location.href=dir+'geekmail_chat.php?room_id='+room_id
}

function entireChat() {
  view_full = true
}

//Add a message to the chat server.
function sendBroadcastText() {
  room_id = document.getElementById('room_id').value
  game_id = document.getElementById('game_id').value
  if(document.getElementById('broad_text').value == '') {
    alert("You have not entered a message");
    return false;
  }
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

function enter_broadcast(evt) {
   evt = ( evt ) ? evt : event;
   var charCode = (evt.charCode) ? evt.charCode : (( evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
   if ( charCode == 13 ) {
     sendBroadcastText()
	 return false;
   }
   return true;
}

function mark_as_read() {
  room_id = document.getElementById('room_id').value
  game_id = document.getElementById('game_id').value
  action = "read_all"
  if (sendReq.readyState == 4 || sendReq.readyState == 0) {
    receiveReq.open("GET", dir+'get_game_chat.php?game_id='+game_id+'&room_id='+room_id+'&last='+lastMessage+"&action="+action, true);
    receiveReq.onreadystatechange = handleReceiveChat;
    receiveReq.send(null);
  }
  return false;
}
