Ajax Agent for PHP v.0.3. Helping WEB become the platform.
            Copyright (c) 2006 AjaxAgent.org
----------------------------------------------------------

Ajax Agent for PHP is a cross platform PHP-JSON based Ajax 
framework. The aim of this framework is to make the Ajax 
work as simple as possible so that application developers 
can develop Ajax applications without going through a steep 
learning curve.

Based on the feedbacks, bug reports & tips received on Ajax
Agent v.0.1 & v.0.2, we prioritized our todos & made an 
improved version v.0.3 available on our website.

The changes in release v.0.3:

1. Bug fixes 

   - the bug related to absence of $_SERVER['REQUEST_URI'] in
     PHP over IIS is now fixed

   - the bug that arises from certain server settings are
     now fixed. proper use of 'isset' function helped fix 
     this issue.


The changes in release v.0.2:

1. Bug fixes 

   - the bug related to arrays & associated arrays is now 
     fixed. 

   - the bug that arises from certain server settings are
     now fixed. proper use of 'isset' function & '<?php>' 
     tag helped fix these issues.
   
2. Externalized source

   - the client-side JavaScript is now externalized so that
     the HTML output of your PHP script stays clean.

3. New method

   - an additional method 'abort' is introduced to help abort
     Ajax requests when needed.

Please refer to the online documentation & demos for more 
info. Have fun coding.

Thanks & regards,
Steve Hemmady, Anuta Udyawar
Ajax Agent Team
contact@ajaxagent.org
