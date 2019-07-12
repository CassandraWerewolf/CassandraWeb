<?php 

  // server side function call
  function ping($obj) {
    return $obj;
  }
  
  function add($obj) {
    $tempobj = array( "Name" =>"David Filo");
    array_push($obj, $tempobj);
    return $obj;
  }
  
  include_once("agent.php");
  $agent->init(); 
  
?>

<script>

  function call_ping() {
    var myTeam = [
      {
          "Name" : "Steve Hemmady",
          "Email" : "steve@ajaxagent.org",
          "Phone" : "(555) 555-1212"
      },
      {
          "Name" : "Anuta Udyawar",
          "Email" : "anuta@ajaxagent.org",
          "Phone" : "(555) 555-1212"
      }
    ];
    agent.call("","ping","callback_ping",myTeam);
  }

  function callback_ping(obj) {
    var str = "The team members are: "+obj[0]["Name"]+" &amp; "+obj[1]["Name"];
    document.getElementById("divPing").innerHTML = str;
  }

  function call_add() {
    var myTeam = [
      {
          "Name" : "Steve Hemmady",
          "Email" : "steve@ajaxagent.org",
          "Phone" : "(555) 555-1212"
      },
      {
          "Name" : "Anuta Udyawar",
          "Email" : "anuta@ajaxagent.org",
          "Phone" : "(555) 555-1212"
      }
    ];
    agent.call("","add","callback_add",myTeam);
  }

  function callback_add(obj) {
    var str = "The team members are: "+obj[0]["Name"]+", "+obj[1]["Name"]+" &amp; "+obj[2]["Name"];
    document.getElementById("divAdd").innerHTML = str;
  }

</script>
 
<style>
  p { font-size: 12px; font-family: Verdana, Arial; }
  div { font-size: 12px; font-family: Verdana, Arial; color: blue; } 
</style>

<p><b>Demo: Array</b></p>

<p>
Click <a href="#" onclick="call_ping();">here</a> to create an array of 
team members, send it to the server & then display the details 
when the response array is pinged back from the server. <br>
</p>
<div id="divPing"></div>

<p>
Click <a href="#" onclick="call_add();">here</a> to create an array of 
team members, send it to the server, have an element added & then 
display the details when the the server sends back the result. <br>
</p>
<div id="divAdd"></div>
 