<?php 
  // server side function call for showing the source code
  function showcode($src) {
    error_reporting(0);
    if((strpos($src, 'demo_')===0)) {
      $lines = file($src);
      $str .= "<br/><p><b>Source code listing for $src:</b></p>";
      $str .= "<textarea rows=200 style='width:100%;height:5000' readonly>";
      foreach ($lines as $line_num => $line) {
        $line = str_replace("</textarea>", "&lt;/textarea&gt;", $line);
        $str .=  $line;
      }
      $str .= "</textarea>";
      return $str;
    }
  }
  // server side function call for running the code
  function runcode($src) {
    $str .= "<br/>";
    $str .= "<iframe width=100% height=600 frameborder=0 marginwidth=0 src=$src></iframe>";
    return $str;
  }

  // AJAXAGENT: including the toolkit 
  require("agent.php");
  
  // AJAXAGENT: including the toolkit 
  $agent->init(); 
?>

<script>
  function cb_showcode(str) {
    document.getElementById('div_src').innerHTML=str;
  }
  function cb_runcode(str) {
    document.getElementById('div_src').innerHTML=str;
  }
</script>

<style>
  table,td,p { font-size: 12px; font-family: Verdana, Arial; }; 
</style>

<b>Cool Demos</b><br/><br/>
<table cellpadding=2 cellspacing=2 border=1 bgcolor=#efefef>
  <tr align=left valign=top>
    <td>1.</td>
    <td><b>Hello:</b> this is a simple 'hello world' kind of demo</td>
    <td><a href="#" onclick="agent.call('','runcode','cb_runcode','demo_hello.php')">
    [run&nbsp;code]</a></td>
    <td><a href="#" onclick="agent.call('','showcode','cb_showcode','demo_hello.php')">
    [show&nbsp;code]</a></td>
  </tr>
  <tr align=left valign=top>
    <td>2.</td>
    <td><b>Calc:</b> this is a simple calculator application</td>
    <td><a href="#" onclick="agent.call('','runcode','cb_runcode','demo_calc.php')">
    [run&nbsp;code]</a></td>
    <td><a href="#" onclick="agent.call('','showcode','cb_showcode','demo_calc.php')">
    [show&nbsp;code]</a></td>
  </tr>
  <tr align=left valign=top>
    <td>3.</td>
    <td><b>Array:</b> this demo shows how to send/receive arrays 
    in the remote scripting call.</td>
    <td><a href="#" onclick="agent.call('','runcode','cb_runcode','demo_array.php')">
    [run&nbsp;code]</a></td>
    <td><a href="#" onclick="agent.call('','showcode','cb_showcode','demo_array.php')">
    [show&nbsp;code]</a></td>
  </tr>
  <tr align=left valign=top>
    <td>4.</td>
    <td><b>Associated Array:</b> same as demo 3 but using associated arrays.</td>
    <td><a href="#" onclick="agent.call('','runcode','cb_runcode','demo_assoc_array.php')">
    [run&nbsp;code]</a></td>
    <td><a href="#" onclick="agent.call('','showcode','cb_showcode','demo_assoc_array.php')">
    [show&nbsp;code]</a></td>
  </tr>
  <tr align=left valign=top>
    <td>5.</td>
    <td><b>Portal:</b> this is a simple portal which has various demos in 
    one page.</td>
    <td><a href="demo_portal.php" target="_parent">[run&nbsp;code]</a></td>
    <td><a href="#" onclick="agent.call('','showcode','cb_showcode','demo_portal.php')">
    [show&nbsp;code]</a></td>
  </tr>
  <tr align=left valign=top>
    <td>6.</td>
    <td><b>Cool Demo:</b> this very page makes use of Ajax Agent!</td>
    <td>&nbsp;N/A</td>
    <td><a href="#" onclick="agent.call('','showcode','cb_showcode','demo_index.php')">
    [show&nbsp;code]</a></td>
  </tr>
</table>

<div id="div_src" style="width:100%;height:5000"></div>
