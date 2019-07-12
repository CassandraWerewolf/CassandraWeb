<?php 
  include "rss/rss.php";

  // server side function call
  function hello() {
    $time = date("H:i:s");
    return "Hello World from server. The server time is $time.";
  }
  
 function sum($x, $y) {
    return $x+$y;
 }
  
  function gsearch () {
    $html = "<form action=http://www.google.com/search method=get target=_blank>";
    $html .= "<img src=http://www.google.com/images/logo_sm.gif><br>";
    $html .= "<input type=text name=q><input type=submit value=Search></form>";
    return $html;
  }

  function quote ($com) {
    $client = new SoapClient("http://services.xmethods.net/soap/urn:xmethods-delayed-quotes.wsdl"); 
    return $client->getQuote($com); 
  } 

  include_once("agent.php");
  
?>

<?php
  $agent->init();
?>

<script>

  function call_hello(str) {
    agent.call('','hello','callback_hello');
  }
  
  function callback_hello(str) {
    document.getElementById('div_hello').innerHTML = str;
  }
  
  function call_sum() {
    x = document.getElementById('x').value;
    y = document.getElementById('y').value;
    agent.call('','sum','callback_sum', x, y);
  }

  function callback_sum(str) {
    document.getElementById('z').value = str;
  }

  function call_gsearch() {
    agent.call('','gsearch','callback_gsearch');
  }

  function callback_gsearch(str) {
    document.getElementById('div_gsearch').innerHTML = str;
  }

  function call_rss() {
    str = document.getElementById('rssurl').value;
    agent.call('','rss','callback_rss','http://news.search.yahoo.com/news/rss?p='+str);
  }

  function callback_rss(str) {
    document.getElementById('div_rss').innerHTML = str;
  }

</script>

<b>AJAX AGENT Demo Portal</b><br/><br/>

<table>
  <tr align=left valign=top>
    <td>

    
      <table>
        <!-- portlet begins -->
        <tr align=left valign=top bgcolor=#ddeeff>
          <td><b>Hello World</b></td>
        </tr>
        <tr align=left valign=top>
          <td colspan=2><br/>

This is a simple test to demonstrate the server side function calling. 
Click <a href="#" onclick="call_hello()"><b>here</b></a> to test.<br/><br/>
<div id="div_hello"></div>

          </td>
        </tr>
        <tr align=left valign=top><td>&nbsp;</td></tr>
        <!-- portlet ends -->
        <!-- portlet begins -->
        <tr align=left valign=top bgcolor=#ddeeff>
          <td><b>Calculator</b></td>
        </tr>
        <tr align=left valign=top>
          <td colspan=2><br/>
          
<form>
  This is a simple calculator which calls 'sum' operation from the server.<br/><br/> 
  <input type="text" name="x" id="x" size="4"> +  
  <input type="text" name="y" id="y" size="4"> 
  <input type="button" name="OK" value="="
  onclick="call_sum()">
  <input type="text" name="z" id="z" size="4"> 
</form>  

          </td>
        </tr>
        <tr align=left valign=top><td>&nbsp;</td></tr>
        <!-- portlet ends -->
        <!-- portlet begins -->
        <tr align=left valign=top bgcolor=#ddeeff>
          <td><b>Google Search Box</b></td>
        </tr>
        <tr align=left valign=top>
          <td colspan=2><br/>

This is a simple dynamic content test which calls 'gsearch' that returns 
HTML content from the server. 
Click <a href=# onclick="call_gsearch()"><b>here</b></a> to test. <br/><br/>
<div id='div_gsearch'></div>

          </td>
        </tr>
        <tr align=left valign=top><td>&nbsp;</td></tr>
        <!-- portlet ends -->
      </table>
      
      
    </td>
    <td>

    
      <table>
        <!-- portlet begins -->
        <tr align=left valign=top bgcolor=#ddeeff>
          <td><b>RSS Search</b></td>
        </tr>
        <tr align=left valign=top>
          <td colspan=2><br/>

This is an RSS feed demo. This demo makes use of MagpieRSS for RSS parsing. Search Yahoo! News: <br/><br/>
<form onsubmit="call_rss();return false;">
  <input type="text" name="rssurl" id="rssurl" size="40" value='Skype'>
</form> <br/>
<div id='div_rss'></div>
          
          
          </td>
        </tr>
        <tr align=left valign=top><td>&nbsp;</td></tr>
        <!-- portlet ends -->
      </table>

    
    </td>
  </tr>
</table>
