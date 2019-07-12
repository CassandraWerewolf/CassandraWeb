<?php header("Location: demo_index.php"); ?>
<?php 

  // the first line of this document ( header("Location ... ") ) redirects the 
  // browser to a different URL . You may remove that line so that you can 
  // jump start with a simple hello world example 

  // server side function call
  function hello() {
    return "Hello World from server! The server time is ".date("H:i:s");
  }

  // Ajax Agent: including the toolkit 
  require("agent.php");
  
  // Ajax Agent: initializing the server side agent
  $agent->init(); 
?>

<script>

  function call_hello() {
    agent.call('','hello','callback_hello');
  }
  
  function call_hello_external_url() {
    agent.call('demo_external.php','hello','callback_hello');
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
  Click <a href="#" onclick="call_hello()">here</a>.
</p>

<p>
  This one calls 'hello' function from the server but an external URL. 
  Click <a href="#" onclick="call_hello_external_url()">here</a>.
</p>

<p>
  This one calls 'hello' function from the server synchronously.  
  Click <a href="#" onclick="call_hello_sync()">here</a>.
</p>
