<?php 
  // server side function call
  function hello() {
    return "Hello World from server, external URL! The server time is ".date("H:i:s");
  }

  include_once("agent.php");
?>
