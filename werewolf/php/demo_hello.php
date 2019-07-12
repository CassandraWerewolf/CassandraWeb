<?php 
  // server side function call
  function hello() {
    return "Hello World from server! The server time is ".date("H:i:s");
  }

  function delayed_hello() {
    sleep(5);
    return "Delayed Hello World from server! The server time is ".date("H:i:s");
  }

  include_once("agent.php");
  $agent->init(); 
?>

<script>

  function call_hello() {
    agent.call('','hello','callback_hello');
  }
  
  function call_hello_external_url() {
    agent.call('demo_external.php','hello','callback_hello');
  }

  var obj; 
  function call_delayed_hello() {
    obj = agent.call('','delayed_hello','callback_hello');
  }
   
  function call_hello_sync() {
    str = agent.call('','hello','');
    alert(str);
  }
  
  function callback_hello(str) {
    alert(str);
  }

</script>

<style>
  p { font-size: 12px; font-family: Verdana, Arial; }
</style>

<p><b>Demo: Hello</b></p>

<p>
  This is a simple demo which calls 'hello' function from the server.  
  Click <a href="#" onclick="call_hello()">here</a> to test.
</p>

<p>
  This one calls 'hello' function from the server but from an external URL. 
  Click <a href="#" onclick="call_hello_external_url()">here</a> to test.
</p>

<p>
  This one calls 'hello' function from the server synchronously.  
  Click <a href="#" onclick="call_hello_sync()">here</a> to test.
</p>

<p>
  This one calls 'delayed hello' function from the server which takes 5 seconds 
  to respond back. Click <a href="#" onclick="call_delayed_hello()">here</a> to 
  test. While waiting for the response, you may chose to
  <a href="#" onclick="obj.abort();">abort</a> the last request to check the 
  'abort' functionality.
</p>

 