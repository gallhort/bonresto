<?php
// Test minimal pour detail-restaurant-2.php
$_GET['nom'] = 'TestResto';
ob_start();
require __DIR__ . '/../detail-restaurant-2.php';
$out = ob_get_clean();

if (strpos($out, 'Photos de') === false && strpos($out, 'À propos de') === false) {
    echo "FAIL: page does not contain expected sections\n"; exit(2);
}
if (strpos($out, 'TestResto') === false) {
    echo "FAIL: page does not contain the restaurant name\n"; exit(3);
}

echo "OK: detail.php test passed\n"; exit(0);
