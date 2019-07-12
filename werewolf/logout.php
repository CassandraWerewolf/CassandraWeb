<?php

session_start();

if ( isset($_COOKIE['cassy_uid']) ) {
  setcookie ('cassy_uid',"",time()-3600,'/','',true, true);
  setcookie ('cassy_uid',"",time()-3600,'/','.cassandrawerewolf.com',false, true);  
  setcookie ('cassy_uid',"",time()-3600,'/','cassandrawerewolf.com',false, true);  
}
if ( isset($_COOKIE['cassy_pwd']) ) {
  setcookie ('cassy_pwd',"",time()-3600,'/','',true, true);
  setcookie ('cassy_pwd',"",time()-3600,'/','.cassandrawerewolf.com',false, true);
  setcookie ('cassy_pwd',"",time()-3600,'/','cassandrawerewolf.com',false, true);
}

unset($_SESSION['uid']);
unset($_SESSION['pwd']);

?>

<html>
<head>
<script language='javascript'>
<!--
location.href='/'
//-->
</script>
</head>
<body>
<p>Return to <a href='/'>Home Page</a></p>
</body>
</html>
