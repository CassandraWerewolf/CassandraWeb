<?php 

  // server side function call
  function calc($x, $y) {
    return $x+$y;
  }

  include_once("agent.php");
  $agent->init(); 
  
?>

<script>

  function runcalc() {
    x = document.getElementById('x').value;
    y = document.getElementById('y').value;
    agent.call('','calc','callback',x,y);
  }
  
  function callback(str) {
    document.getElementById('z').value = str;
  }

</script>

<style>
  p { font-size: 12px; font-family: Verdana, Arial; }; 
</style>

<p><b>Demo: Calc</b></p>

<p>
  <form>
    This is a simple calculator which calls 'calc' operation 
    from the server.<br><br> 
    <input name="x" id="x" size="4" type="text"> +  
    <input name="y" id="y" size="4" type="text"> 
    <input name="OK" value="=" onclick="runcalc();return false;" type="submit">
    <input name="z" id="z" size="4" type="text">
  </form>
</p>


 