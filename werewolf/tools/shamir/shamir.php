<?php
echo json_encode($_GET['function']($_GET));
exit();

function get_shares($data) {
    if(isset($data['num_shares']) && !empty($data['num_shares']) && 
    isset($data['num_thresh']) && !empty($data['num_thresh']) && 
    isset($data['secret']) && !empty($data['secret']) &&
    $data['num_thresh'] <= $data['num_shares'] &&
    strlen($data['secret']) < 255 && $data['num_shares'] <= 50 &&
    strlen($data['ident']) <= 20) {
        $ident = (isset($data['ident']) && !empty($data['ident'])) ? " -w " . escapeshellarg($data['ident']) : "";
        $output = null;
	    $k = array();
        $cmd = sprintf('echo %s | /usr/local/bin/ssss-split -Q -t %d -n %d %s', escapeshellarg($data['secret']), $data['num_thresh'], $data['num_shares'], $ident);
        $output = shell_exec($cmd);
        $k['shares_list'] = $output;
        #$k['shares_list'] = $ident;
	    return($k);
    }
}

function get_secret($data) {
    if(isset($data['num_thresh']) && !empty($data['num_thresh']) && 
    isset($data['shares_list']) && !empty($data['shares_list']) &&
    $data['num_thresh'] <= 50 && strlen($data['shares_list']) <=15000) {
	    $k = array();
        $cmd = sprintf('echo %s | /usr/local/bin/ssss-combine -Q -t %d 2>&1', escapeshellarg($data['shares_list']), $data['num_thresh']);
        $output = array();
        exec($cmd, $output);
        $k['secret'] = str_replace("'", "", $output[1]); 
	    return($k);
    }
}
?>
