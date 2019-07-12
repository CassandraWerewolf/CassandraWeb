<?php

// This function is used to change a url back to the file name that the .htaccess page used.

function rewrite_to_actual($rewrite) {
  $actual = $rewrite;
  $replace = array();
  $dir = "/var/www/html";
  $subdir = "";
  if ( preg_match('/automod/',$actual) ) {
    $dir .= "/automod";
	$subdir .= "automod/";
  }
  $htaccess = file_get_contents("$dir/.htaccess");
  $lines = preg_split("/\n/", $htaccess);
  for ( $i=0; $i<count($lines); $i++) {
    if ( preg_match("/^RewriteRule/", $lines[$i]) ) {
      $rewrite_lines[] = $lines[$i];
	}
  }
  for ( $i=0; $i<count($rewrite_lines); $i++) {
    $rule = preg_split("/\s/", $rewrite_lines[$i]);
	$rule[1] = preg_replace("/\^/","",$rule[1]);
	$rule_match = preg_replace("/\//","\/",$subdir).preg_replace("/\//","\/",$rule[1]);

	if ( preg_match("/$rule_match/ ",$rewrite) ) {
	   $rewrite_split = preg_split("/\//",$rewrite);
	   $rule_split = preg_split("/\//",$subdir.$rule[1]);
	   for ( $j=0; $j<count($rewrite_split); $j++ ) {
         if ( $rewrite_split[$j] != $rule_split[$j] ) {
		   $replace[] = $rewrite_split[$j];
		 }
	   }
       $actual = $rule[2];
	   break;
	}
  }
  for ( $k=0;$k<count($replace);$k++) {
    $string = "\\$".($k+1);
    $actual = preg_replace("/$string/",$replace[$k],$actual);
  }
  return $actual;
}
