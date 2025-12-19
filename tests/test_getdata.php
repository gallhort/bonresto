<?php
// Test minimal pour getdata.php (GET path)
$_GET['type'] = 'Tous';
$_GET['lat'] = '36.75';
$_GET['lon'] = '3.05';
$_GET['radius'] = '10';
$_GET['start'] = '0';
$_GET['nb'] = '5';

ob_start();
require __DIR__ . '/../getdata.php';
$out = ob_get_clean();

$data = json_decode($out, true);
if (!is_array($data)) {
    echo "FAIL: réponse non JSON\n"; exit(2);
}
// Expect an array of restaurants (possibly empty)
if (!is_array($data)) {
    echo "FAIL: expected array\n"; exit(3);
}
echo "OK: getdata test passed\n"; exit(0);
