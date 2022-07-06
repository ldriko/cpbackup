<?php

/*
* call class cPbackup
*/
require_once 'cpbackup.php';

$settings = require_once 'settings.php';
$hosts = $settings['hosts'];

ini_set('max_execution_time', 600);
ini_set('date.timezone', 'Asia/Jakarta');
header('Content-Type: text/html; charset=utf-8');

$html = '<style>body { font-family:arial; }</style><h2>BACKUP DATABASE</h2>';

$starttime = microtime(true);
$total_size = 0;

foreach ($hosts as $host) {
    /*
    * define variabel as new cPbackup
    */
    $cpbackup = new cPbackup($settings['backup_path'] ?? null);

    /*
    * set minimum requirement parameter for homedir destination backup
    */
    $cpbackup->override_name = $host['override_name'];
    $cpbackup->hostname = $host['hostname'];
    $cpbackup->cpuser = $host['cpuser'];
    $cpbackup->cppasswd = $host['cppasswd'];
    $cpbackup->ssl = $host['ssl'];
    $cpbackup->proxy = $host['proxy'];
    $cpbackup->port = $host['port'];
    $cpbackup->max_number_of_file = $host['max_number_of_file'];
    $cpbackup->database = $host['database'];
    $result = $cpbackup->databaseBackup();

    $list = '';
    $size = 0;
    foreach ($result as $key => $value) {
        $list .= "<br>" . $value['result'];
        $size += $value['size'];
    }
    $total_size += $size;

    $html .= "<br>====================================================";
    $html .= "<br><b>" . $host['hostname'] . " (" . $cpbackup->formatSizeUnits($size) . ")</b>";
    $html .= $list;
    $html .= "<br>====================================================";
    $html .= "<br>====================================================<br>";
}

$execute_time = round(microtime(true) - $starttime, 3);
$html .= "<br><b>Total Size: " . $cpbackup->formatSizeUnits($total_size);
$html .= "<br>Server process time: " . $execute_time . " secs</b>";

echo $html;