<?php

/*
* call class cPbackup
*/
require_once 'cpbackup.php';
$server = require_once 'settings.php';

ini_set('max_execution_time', 600);
ini_set('date.timezone', 'Asia/Jakarta');
header('Content-Type: text/html; charset=utf-8');

$html = '<style>body { font-family:arial; }</style><h2>BACKUP DATABASE</h2>';

$starttime = microtime(true);
$total_size = 0;

foreach ($server as $val) {

    /*
    * define variabel as new cPbackup
    */
    $cpbackup = new cPbackup();

    /*
    * set minimum requirement parameter for homedir destination backup
    */
    $cpbackup->override_name = $val['override_name'];
    $cpbackup->hostname = $val['hostname'];
    $cpbackup->cpuser = $val['cpuser'];
    $cpbackup->cppasswd = $val['cppasswd'];
    $cpbackup->ssl = $val['ssl'];
    $cpbackup->proxy = $val['proxy'];
    $cpbackup->port = $val['port'];
    $cpbackup->max_number_of_file = $val['max_number_of_file'];
    $cpbackup->database = $val['database'];
    $result = $cpbackup->databaseBackup();

    $list = '';
    $size = 0;
    foreach ($result as $key => $value) {
        $list .= "<br>" . $value['result'];
        $size += $value['size'];
    }
    $total_size += $size;

    $html .= "<br>====================================================";
    $html .= "<br><b>" . $val['hostname'] . " (" . $cpbackup->formatSizeUnits($size) . ")</b>";
    $html .= $list;
    $html .= "<br>====================================================";
    $html .= "<br>====================================================<br>";
}

$execute_time = round(microtime(true) - $starttime, 3);
$html .= "<br><b>Total Size: " . $cpbackup->formatSizeUnits($total_size);
$html .= "<br>Server process time: " . $execute_time . " secs</b>";

echo $html;