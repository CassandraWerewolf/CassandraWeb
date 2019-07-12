<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";

dbConnect();

$thread_id = $_GET['thread_id'];
$sql = sprintf("select id, title, status from Games where threaD_id=%s",quote_smart($thread_id));
$result = mysql_query($sql);
$game_id = mysql_result($result,0,0);
$title = mysql_result($result,0,1);
$status = mysql_result($result,0,2);

?>
<html>
<head>
<title>Communications for <?=$title;?> (Alpha)</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
<style type=text/css media=screen>
.chat_time {
  font-style: italic;
  font-size: 9px;
}
.user_name {
  color:red;
  font-weight:bold;
}

</style>
<script language=JavaScript type=text/javascript>
var sendReq = getXmlHttpRequestObject();
var receiveReq = getXmlHttpRequestObject();
var lastMessage = 0;
var mTimer;
var dir = "/";

//Function for initializating the page.
function startChat() {
  //Set the focus to the Message Box.
  document.getElementById('txt_message').focus();
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
    document.getElementById('p_status').innerHTML = 'Status: Cound not create XmlHttpRequest Object.  Consider upgrading your browser.';
  }
}

//Gets the current messages from the server
function getChatText() {
  if (receiveReq.readyState == 4 || receiveReq.readyState == 0) {
    receiveReq.open("GET", dir+'getChat.php?chat=1&last=' + lastMessage, true);
    receiveReq.onreadystatechange = handleReceiveChat;
    //receiveReq.onreadystatechange = print_state();
    receiveReq.send(null);
  }
}

function print_state() {
    document.write(": Status<br />");
	document.write(receiveReq.readyState)
	document.write("<br />status<br />")
	document.write(receiveReq.status)
	document.write("<br />text<br />")
	document.write(receiveReq.statusText)
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
      var time_node = message_nodes[i].getElementsByTagName("time");
      chat_div.innerHTML += '&lt;<font class="user_name">'+user_node[0].firstChild.nodeValue + '</font>&nbsp;';
      chat_div.innerHTML += '<font class="chat_time">' + time_node[0].firstChild.nodeValue + '</font>&gt; ';
      chat_div.innerHTML += text_node[0].firstChild.nodeValue + '<br />';
      chat_div.scrollTop = chat_div.scrollHeight;
	  lastMessage = (message_nodes[i].getAttribute('id'));
	}
	mTimer = setTimeout('getChatText();',2000); //Refresh our chat in 2 seconds
  }
}

//Add a message to the chat server.
function sendChatText() {
  if(document.getElementById('txt_message').value == '') {
    alert("You have not entered a message");
	return;
  }
  if (sendReq.readyState == 4 || sendReq.readyState == 0) {
    sendReq.open("POST", dir+'getChat.php?chat=1&last=' + lastMessage, true);
    sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    sendReq.onreadystatechange = handleSendChat;
    var param = 'message=' + document.getElementById('txt_message').value;
    param += '&name=<?=$username;?>';
    param += '&chat=1';
    sendReq.send(param);
    document.getElementById('txt_message').value = '';
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
sendChatText();
return false;
}

</script>
</head>
<body onload="javascript:startChat();">
<!--<body>-->
<?php print display_menu(); ?>
<h1>Communications for <?=$title;?> (Alpha)</h1>
Current Chat
<div id="div_chat" style="height: 300px; width: 500px; overflow: auto; background-color: #CCCCCC; border: 1px solid #555555;">
</div>
<form id=frmmain name=frmmain onsubmit="return blockSubmit()">
<input id=btn_get_chat type=button value="Refresh Chat" name=btn_get_chat onclick=javascript:getChatText(); /><br />
<input id=txt_message style="WIDTH: 447px" name=txt_message>
<input id=btn_send_chat type=button value=Send name=btn_send_chat onclick=javascript:sendChatText(); />
</form>
<!--
<script language='javascript'>
startChat();
</script>
-->
</body>
</html>
