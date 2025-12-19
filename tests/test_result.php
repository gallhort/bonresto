<?php
// Test minimal pour result.php : s'assurer que la page se génère avec des paramètres valides
$_GET['adresse'] = 'Alger';
$_GET['foodType'] = 'Tous';
$_GET['currentgps'] = '36.75,3.05';
$_GET['tri'] = '1';

ob_start();
require __DIR__ . '/../result.php';
$out = ob_get_clean();

if (strpos($out, 'window.searchParams') === false) {
    echo "FAIL: script did not render expected JS searchParams\n"; exit(2);
}
if (strpos($out, 'restaurants-section') === false) {
    echo "FAIL: page does not contain restaurants section\n"; exit(3);
}

echo "OK: result.php test passed\n"; exit(0);
