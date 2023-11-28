<?php
class BC_Timer{
var $stime;
var $etime;

function get_microtime(){
return microtime(TRUE);
}

function start_time(){
$this->stime = $this->get_microtime();
}

function end_time(){
$this->etime = $this->get_microtime();
}

function elapsed_time(){
return ($this->etime - $this->stime);
}
}
?>
