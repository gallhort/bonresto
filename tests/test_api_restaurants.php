<?php
// Test minimal pour api_restaurants.php en incluant le fichier et capturant la sortie
$_GET['action'] = 'search';
$_GET['currentgps'] = '36.75,3.05';
$_GET['searchRadius'] = '10';
$_GET['page'] = '1';
$_GET['limit'] = '5';

ob_start();
require __DIR__ . '/../api_restaurants.php';
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
if (!isset($data['filters'])) {
    echo "FAIL: filters key missing\n"; exit(5);
}
echo "OK: api_restaurants test passed\n"; exit(0);
