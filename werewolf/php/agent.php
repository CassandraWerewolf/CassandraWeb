<?php
include_once "htaccess.php";
/*
--------------------------------------------------------------------
Ajax Agent for PHP v.0.3. Copyright (c) 2006 ajaxagent.org. 
@author: Steve Hemmady, Anuta Udyawar <contact at ajaxagent dot org>
This program is free software; you can redistribute it under the 
terms of the GNU General Public License as published by the Free 
Software Foundation; either version 2 of the License, or (at your 
option) any later version. This program is distributed in the hope 
that it will be useful, but WITHOUT ANY WARRANTY; without even the 
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
PURPOSE. See the GNU General Public License for more info 
at http://www.gnu.org/licenses/gpl.txt
--------------------------------------------------------------------
*/

// produce the client-side script (json.js & agent.js)
if(isset($_GET['ajaxagent']) && $_GET['ajaxagent']=='js') {
?>
<!--
/*
Copyright (c) 2005 JSON.org

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The Software shall be used for Good, not Evil.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/*
    The global object JSON contains two methods.

    JSON.stringify(value) takes a JavaScript value and produces a JSON text.
    The value must not be cyclical.

    JSON.parse(text) takes a JSON text and produces a JavaScript value. It will
    throw a 'JSONError' exception if there is an error.
*/
var JSON = {
    copyright: '(c)2005 JSON.org',
    license: 'http://www.crockford.com/JSON/license.html',
/*
    Stringify a JavaScript value, producing a JSON text.
*/
    stringify: function (v) {
        var a = [];

/*
    Emit a string.
*/
        function e(s) {
            a[a.length] = s;
        }

/*
    Convert a value.
*/
        function g(x) {
            var c, i, l, v;

            switch (typeof x) {
            case 'object':
                if (x) {
                    if (x instanceof Array) {
                        e('[');
                        l = a.length;
                        for (i = 0; i < x.length; i += 1) {
                            v = x[i];
                            if (typeof v != 'undefined' &&
                                    typeof v != 'function') {
                                if (l < a.length) {
                                    e(',');
                                }
                                g(v);
                            }
                        }
                        e(']');
                        return;
                    } else if (typeof x.toString != 'undefined') {
                        e('{');
                        l = a.length;
                        for (i in x) {
                            v = x[i];
                            if (x.hasOwnProperty(i) &&
                                    typeof v != 'undefined' &&
                                    typeof v != 'function') {
                                if (l < a.length) {
                                    e(',');
                                }
                                g(i);
                                e(':');
                                g(v);
                            }
                        }
                        return e('}');
                    }
                }
                e('null');
                return;
            case 'number':
                e(isFinite(x) ? +x : 'null');
                return;
            case 'string':
                l = x.length;
                e('"');
                for (i = 0; i < l; i += 1) {
                    c = x.charAt(i);
                    if (c >= ' ') {
                        if (c == '\\' || c == '"') {
                            e('\\');
                        }
                        e(c);
                    } else {
                        switch (c) {
                        case '\b':
                            e('\\b');
                            break;
                        case '\f':
                            e('\\f');
                            break;
                        case '\n':
                            e('\\n');
                            break;
                        case '\r':
                            e('\\r');
                            break;
                        case '\t':
                            e('\\t');
                            break;
                        default:
                            c = c.charCodeAt();
                            e('\\u00' + Math.floor(c / 16).toString(16) +
                                (c % 16).toString(16));
                        }
                    }
                }
                e('"');
                return;
            case 'boolean':
                e(String(x));
                return;
            default:
                e('null');
                return;
            }
        }
        g(v);
        return a.join('');
    },
/*
    Parse a JSON text, producing a JavaScript value.
*/
    parse: function (text) {
        return (/^(\s+|[,:{}\[\]]|"(\\["\\\/bfnrtu]|[^\x00-\x1f"\\]+)*"|-?\d+(\.\d*)?([eE][+-]?\d+)?|true|false|null)+$/.test(text)) &&
            eval('(' + text + ')');
    }
};


// client-side agent implementation 
this_url = "<?php echo $_GET['this_url'] ?>";
function Agent() {
  this.debug = false; // default
  this.call = function () {
    var aa_sfunc = "";
    var aa_cfunc = "";
    var result = "";
    var xmlHttpObject;
    if(arguments.length<3) {
      alert("Incorrect number of parameters. Please check your function call");
      return;
    } 
    aa_url=arguments[0];
    aa_sfunc=arguments[1];
    aa_cfunc=arguments[2];

    if((aa_url==null)||(aa_url=="")) aa_url = this_url;
    var aa_poststr = "aa_afunc=call&aa_sfunc=" + encodeURI(aa_sfunc) +
        "&aa_cfunc=" + encodeURI(aa_cfunc);
    for(var i=3; i<arguments.length; i++) {
      if(typeof(arguments[i])=='object') {
        aa_poststr += "&aa_sfunc_args[]="+encodeURI(JSON.stringify(arguments[i]));
      } else {
        aa_poststr += "&aa_sfunc_args[]="+encodeURI(arguments[i]);
      }
    }
    xmlHttpObject = false;
    if (window.XMLHttpRequest) { // Mozilla, Safari,...
       xmlHttpObject = new XMLHttpRequest();
       if (xmlHttpObject.overrideMimeType) {
          xmlHttpObject.overrideMimeType('text/xml');
       }
    } else if (window.ActiveXObject) { // IE
       try {
          xmlHttpObject = new ActiveXObject("Msxml2.XMLHTTP");
       } catch (e) {
          try {
             xmlHttpObject = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {}
       }
    }
    if (!xmlHttpObject) {
       alert('Agent unable to establish communication  :( ');
       return false;
    }
    
    if((aa_sfunc==null)||(aa_sfunc=="")) {
      if(arguments[3]) aa_poststr=arguments[3];
    }
    
    if((aa_cfunc==null)||(aa_cfunc=="")) {
      xmlHttpObject.open('POST', aa_url, false);
      xmlHttpObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      //xmlHttpObject.setRequestHeader("Content-length", arguments.length+1);
      //xmlHttpObject.setRequestHeader("Connection", "close"); // not needed
      xmlHttpObject.send(aa_poststr);
      return xmlHttpObject.responseText;
    } else {
      xmlHttpObject.onreadystatechange = function () {
        if (xmlHttpObject.readyState == 4) {
           if (xmlHttpObject.status == 200) {
              result = xmlHttpObject.responseText;
              result = result.replace(/\\\"/g,'"');
              if(document.getElementById(aa_cfunc)) {
                try {
                  document.getElementById(aa_cfunc).innerHTML=result;
                }
                catch (e) {
                  document.getElementById(aa_cfunc).value=result;
                }               
              } else {
                if (JSON.parse(result)) 
                  eval(aa_cfunc+"(JSON.parse(result));");
                else 
                  eval(aa_cfunc+"(result);");
              }
           } else {
              if(xmlHttpObject.status!=0) {
                alert('There was a problem with the request.');
              }
           }
        }
      }
      xmlHttpObject.open('POST', aa_url, true);
      xmlHttpObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      //xmlHttpObject.setRequestHeader("Content-length", arguments.length+1);
      //xmlHttpObject.setRequestHeader("Connection", "close"); // not needed
      xmlHttpObject.send(aa_poststr);
      return xmlHttpObject;
    }
  }
  this.listen = function (aa_event, aa_cfunc) {
    // listener function will come here
  }
}
var agent = new Agent();

//-->
<?php
exit();
}

/**********************************************************************/
/****         FOLLOWING BLOCK BORROWED FROM JSON.PHP               ****/
/**********************************************************************/
  
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* Converts to and from JSON format.
*
* JSON (JavaScript Object Notation) is a lightweight data-interchange
* format. It is easy for humans to read and write. It is easy for machines
* to parse and generate. It is based on a subset of the JavaScript
* Programming Language, Standard ECMA-262 3rd Edition - December 1999.
* This feature can also be found in  Python. JSON is a text format that is
* completely language independent but uses conventions that are familiar
* to programmers of the C-family of languages, including C, C++, C#, Java,
* JavaScript, Perl, TCL, and many others. These properties make JSON an
* ideal data-interchange language.
*
* This package provides a simple encoder and decoder for JSON notation. It
* is intended for use with client-side Javascript applications that make
* use of HTTPRequest to perform server communication functions - data can
* be encoded into JSON notation for use in a client-side javascript, or
* decoded from incoming Javascript requests. JSON format is native to
* Javascript, and can be directly eval()'ed with no further parsing
* overhead
*
* All strings should be in ASCII or UTF-8 format!
*
* LICENSE: Redistribution and use in source and binary forms, with or
* without modification, are permitted provided that the following
* conditions are met: Redistributions of source code must retain the
* above copyright notice, this list of conditions and the following
* disclaimer. Redistributions in binary form must reproduce the above
* copyright notice, this list of conditions and the following disclaimer
* in the documentation and/or other materials provided with the
* distribution.
*
* THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
* WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
* MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
* NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
* BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
* OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
* ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
* TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
* USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
* DAMAGE.
*
* @category
* @package     Services_JSON
* @author      Michal Migurski <mike-json@teczno.com>
* @author      Matt Knapp <mdknapp[at]gmail[dot]com>
* @author      Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
* @copyright   2005 Michal Migurski
* @version     CVS: $Id: JSON.php,v 1.30 2006/03/08 16:10:20 migurski Exp $
* @license     http://www.opensource.org/licenses/bsd-license.php
* @link        http://pear.php.net/pepr/pepr-proposal-show.php?id=198
*/

/**
* Marker constant for Services_JSON::decode(), used to flag stack state
*/
define('SERVICES_JSON_SLICE',   1);

/**
* Marker constant for Services_JSON::decode(), used to flag stack state
*/
define('SERVICES_JSON_IN_STR',  2);

/**
* Marker constant for Services_JSON::decode(), used to flag stack state
*/
define('SERVICES_JSON_IN_ARR',  3);

/**
* Marker constant for Services_JSON::decode(), used to flag stack state
*/
define('SERVICES_JSON_IN_OBJ',  4);

/**
* Marker constant for Services_JSON::decode(), used to flag stack state
*/
define('SERVICES_JSON_IN_CMT', 5);

/**
* Behavior switch for Services_JSON::decode()
*/
define('SERVICES_JSON_LOOSE_TYPE', 16);

/**
* Behavior switch for Services_JSON::decode()
*/
define('SERVICES_JSON_SUPPRESS_ERRORS', 32);

/**
* Converts to and from JSON format.
*
* Brief example of use:
*
* <code>
* // create a new instance of Services_JSON
* $json = new Services_JSON();
*
* // convert a complexe value to JSON notation, and send it to the browser
* $value = array('foo', 'bar', array(1, 2, 'baz'), array(3, array(4)));
* $output = $json->encode($value);
*
* print($output);
* // prints: ["foo","bar",[1,2,"baz"],[3,[4]]]
*
* // accept incoming POST data, assumed to be in JSON notation
* $input = file_get_contents('php://input', 1000000);
* $value = $json->decode($input);
* </code>
*/
class Services_JSON
{
   /**
    * constructs a new JSON instance
    *
    * @param    int     $use    object behavior flags; combine with boolean-OR
    *
    *                           possible values:
    *                           - SERVICES_JSON_LOOSE_TYPE:  loose typing.
    *                                   "{...}" syntax creates associative arrays
    *                                   instead of objects in decode().
    *                           - SERVICES_JSON_SUPPRESS_ERRORS:  error suppression.
    *                                   Values which can't be encoded (e.g. resources)
    *                                   appear as NULL instead of throwing errors.
    *                                   By default, a deeply-nested resource will
    *                                   bubble up with an error, so all return values
    *                                   from encode() should be checked with isError()
    */
    function Services_JSON($use = 0)
    {
        $this->use = $use;
    }

   /**
    * convert a string from one UTF-16 char to one UTF-8 char
    *
    * Normally should be handled by mb_convert_encoding, but
    * provides a slower PHP-only method for installations
    * that lack the multibye string extension.
    *
    * @param    string  $utf16  UTF-16 character
    * @return   string  UTF-8 character
    * @access   private
    */
    function utf162utf8($utf16)
    {
        // oh please oh please oh please oh please oh please
        if(function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
        }

        

        // ignoring UTF-32 for now, sorry
        return '';
    }

   /**
    * convert a string from one UTF-8 char to one UTF-16 char
    *
    * Normally should be handled by mb_convert_encoding, but
    * provides a slower PHP-only method for installations
    * that lack the multibye string extension.
    *
    * @param    string  $utf8   UTF-8 character
    * @return   string  UTF-16 character
    * @access   private
    */
    function utf82utf16($utf8)
    {
        // oh please oh please oh please oh please oh please
        if(function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }

   /**
    * encodes an arbitrary variable into JSON format
    *
    * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
    *                           see argument 1 to Services_JSON() above for array-parsing behavior.
    *                           if var is a strng, note that encode() always expects it
    *                           to be in ASCII or UTF-8 format!
    *
    * @return   mixed   JSON string representation of input var or an error if a problem occurs
    * @access   public
    */
    function encode($var)
    {
        return json_encode($var);
    }

   /**
    * array-walking function for use in generating JSON-formatted name-value pairs
    *
    * @param    string  $name   name of key to use
    * @param    mixed   $value  reference to an array element to be encoded
    *
    * @return   string  JSON-formatted name-value pair, like '"name":value'
    * @access   private
    */
    function name_value($name, $value)
    {
        $encoded_value = $this->encode($value);

        if(Services_JSON::isError($encoded_value)) {
            return $encoded_value;
        }

        return $this->encode(strval($name)) . ':' . $encoded_value;
    }

   /**
    * reduce a string by removing leading and trailing comments and whitespace
    *
    * @param    $str    string      string value to strip of comments and whitespace
    *
    * @return   string  string value stripped of comments and whitespace
    * @access   private
    */
    function reduce_string($str)
    {
        $str = preg_replace(array(

                // eliminate single line comments in '// ...' form
                '#^\s*//(.+)$#m',

                // eliminate multi-line comments in '/* ... */' form, at start of string
                '#^\s*/\*(.+)\*/#Us',

                // eliminate multi-line comments in '/* ... */' form, at end of string
                '#/\*(.+)\*/\s*$#Us'

            ), '', $str);

        // eliminate extraneous space
        return trim($str);
    }

   /**
    * decodes a JSON string into appropriate variable
    *
    * @param    string  $str    JSON-formatted string
    *
    * @return   mixed   number, boolean, string, array, or object
    *                   corresponding to given JSON input string.
    *                   See argument 1 to Services_JSON() above for object-output behavior.
    *                   Note that decode() always returns strings
    *                   in ASCII or UTF-8 format!
    * @access   public
    */
    function decode($str)
    {
        return json_decode($str);
    }

    /**
     * @todo Ultimately, this should just call PEAR::isError()
     */
    function isError($data, $code = null)
    {
        if (class_exists('pear')) {
            return PEAR::isError($data, $code);
        } elseif (is_object($data) && (get_class($data) == 'services_json_error' ||
                                 is_subclass_of($data, 'services_json_error'))) {
            return true;
        }

        return false;
    }
}

if (class_exists('PEAR_Error')) {

    class Services_JSON_Error extends PEAR_Error
    {
        function Services_JSON_Error($message = 'unknown error', $code = null,
                                     $mode = null, $options = null, $userinfo = null)
        {
            parent::PEAR_Error($message, $code, $mode, $options, $userinfo);
        }
    }

} else {

    /**
     * @todo Ultimately, this class shall be descended from PEAR_Error
     */
    class Services_JSON_Error
    {
        function Services_JSON_Error($message = 'unknown error', $code = null,
                                     $mode = null, $options = null, $userinfo = null)
        {

        }
    }

}
    
// Server side Ajax Agent implimentation follows
$agent = new Agent;
if (isset($_POST['aa_afunc'])) $aa_afunc = $_POST['aa_afunc']; else $aa_afunc="";
if (isset($_POST['aa_sfunc'])) $aa_sfunc = $_POST['aa_sfunc']; else $aa_sfunc="";
if (isset($_POST['aa_event'])) $aa_event = $_POST['aa_event']; else $aa_event="";
if (isset($_POST['aa_cfunc'])) $aa_cfunc = $_POST['aa_cfunc']; else $aa_cfunc="";
if (isset($_POST['aa_sfunc_args'])) $aa_sfunc_args = $_POST['aa_sfunc_args']; 
else $aa_sfunc_args="";

if($_SERVER['REQUEST_URI']==null||$_SERVER['REQUEST_URI']=="") {
  $aa_url = $_SERVER['PHP_SELF'];
} else {
  $aa_url = rewrite_to_actual($_SERVER['REQUEST_URI']);
}

if($aa_afunc=="call") {
  $agent->call($aa_sfunc, $aa_cfunc, $aa_sfunc_args);
}
  
if($aa_afunc=="listen") {
  $agent->listen($aa_event, $aa_cfunc, $aa_sfunc_args);
}
  
class Agent {
  function call ($aa_sfunc, $aa_cfunc, $aa_sfunc_args) {
    $json = new Services_JSON();
    $aa_sfunc_args_dc=array();
    if($aa_sfunc_args && sizeof($aa_sfunc_args)>=1) {
      foreach ($aa_sfunc_args as $aa_arg) {
        if ((strpos($aa_arg, "[")!==false) || (strpos($aa_arg, "{")!==false)) {
          if ((strpos($aa_arg, "[")===0) || (strpos($aa_arg, "{")===0)) {
            $aa_arg = str_replace('\"', '"', $aa_arg);
            $aa_arg_dc = $json->decode($aa_arg);
            array_push($aa_sfunc_args_dc,$aa_arg_dc);
          } else {
            array_push($aa_sfunc_args_dc,$aa_arg);
          }
        } else {
          array_push($aa_sfunc_args_dc,$aa_arg);
        }
      }
    }

    // sfix # sf001
    $arr = get_defined_functions();
    if (!in_array(strtolower($aa_sfunc), $arr["user"]) && !in_array($aa_sfunc, $arr["user"])) exit();

    $ret = call_user_func_array($aa_sfunc, $aa_sfunc_args_dc);
    if(is_array($ret) || is_object($ret)) {
      $ret = $json->encode($ret);
      $ret = str_replace('\"', '"', $ret);
      echo $ret; 
    } else {
      echo $ret;
    }
    exit();
  }

  function listen ($aa_event, $aa_cfunc, $aa_sfunc_args) {
    // to be implemented
    exit();
  }

  function init () {
    global $aa_url;
	$cgi = "?";
	if ( preg_match("/\?/",$aa_url) ) { $cgi = "&"; }
?>
<script type="text/javascript" src="<?=$aa_url;?><?=$cgi;?>ajaxagent=js&this_url=<?php echo urlencode($aa_url); ?>">
<!--
/*
--------------------------------------------------------------------
Ajax Agent for PHP v.0.3. Copyright (c) 2006 ajaxagent.org. 
@author: Steve Hemmady, Anuta Udyawar <contact at ajaxagent dot org>
This program is free software; you can redistribute it under the 
terms of the GNU General Public License as published by the Free 
Software Foundation; either version 2 of the License, or (at your 
option) any later version. This program is distributed in the hope 
that it will be useful, but WITHOUT ANY WARRANTY; without even the 
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
PURPOSE. See the GNU General Public License for more info 
at http://www.gnu.org/licenses/gpl.txt
--------------------------------------------------------------------
*/
//-->
</script>
<?php
  }
}
?>
