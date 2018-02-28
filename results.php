<?php

# Process the iperf-web form, spawn either a docker container 
# or just run the iperf command on the localhost. print output
# to stdout.

# Increase PHP max execution time to allow for longer tests.
set_time_limit(300);

$type    = (!empty($_REQUEST['type'])) ? $_REQUEST['type'] : 0;

if ($type) {

  $prog    = (!empty($_REQUEST['prog'])) ? $_REQUEST['prog'] : 'Ping';
  $params  = (!empty($_REQUEST['params'])) ? escapeshellcmd($_REQUEST['params']) : NULL;

  if ($prog == 'Ping') {
    $prog    = '/bin/ping';
    $command = $prog . ' ' . $params . ' 2>&1';
  } else {
    $prog    = '/usr/sbin/traceroute';
    $command = $prog . ' ' . $params . ' 2>&1';
  }

} else {
  $port_regex = '/[0-9]+/';

  $prog    = (!empty($_REQUEST['prog'])) ? $_REQUEST['prog'] : 'Sender';
  $version = (!empty($_REQUEST['version'])) ? $_REQUEST['version'] : 3;
  $params  = (!empty($_REQUEST['params'])) ? escapeshellcmd($_REQUEST['params']) : NULL;
  $target  = (!empty($_REQUEST['target'])) ? escapeshellcmd($_REQUEST['target']) : NULL;
  $port    = (preg_match($port_regex,$_REQUEST['port'])) ? $_REQUEST['port'] : 5001;

  if ($prog == 'Receiver') {
    $prog    = 'iperf3';
    $args   .= ' -s ';
    $command = $prog . $args;
  } else {
    $prog    = 'iperf3';
    $args    = ' -c ' . $target . ' -p ' . $port . ' ' . $params;
    $command = $prog . $args;
  } 
}

// Not sure where i stole this code from... Once i remember, i'll link to it here

$descriptorspec = array(
   0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
   2 => array("pipe", "w")    // stderr is a pipe that the child will write to
);

flush();
$proc = proc_open($command, $descriptorspec, $pipes, realpath('./'), array());
if (is_resource($proc)) {
    echo "Test execution begins...\n";
    flush();
    while ($s = fgets($pipes[1])) {
        echo '<li>' . $s . '<li>';
        flush();
    }
    while ($s = fgets($pipes[2])) {
        echo '<li>' . $s . '<li>';
        flush();
    }
    echo '<li>' . "Test execution has ended...\n" . '<li>';
    flush();
}

proc_close($proc);

?>
