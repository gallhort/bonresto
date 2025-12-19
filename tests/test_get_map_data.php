<?php
// Test minimal pour get_map_data.php en incluant le fichier et capturant la sortie
$_GET['currentgps'] = '36.75,3.05';
$_GET['radius'] = '10';
$_GET['start'] = '0';
$_GET['nb'] = '5';

ob_start();
require __DIR__ . '/../get_map_data.php';
$out = ob_get_clean();

$data = json_decode($out, true);
if (!is_array($data)) {
    echo "FAIL: réponse non JSON\n"; exit(2);
}
if (empty($data['success'])) {
    echo "FAIL: success flag false or missing\n"; print_r($data); exit(3);
}
if (!isset($data['restaurants'])) {
    echo "FAIL: restaurants key missing\n"; exit(4);
}
echo "OK: get_map_data test passed\n"; exit(0);
