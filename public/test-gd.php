<?php
if (function_exists('imagecreatefromjpeg')) {
    echo "✅ imagecreatefromjpeg existe<br>";
} else {
    echo "❌ imagecreatefromjpeg N'EXISTE PAS<br>";
}

if (function_exists('imagecreatefrompng')) {
    echo "✅ imagecreatefrompng existe<br>";
} else {
    echo "❌ imagecreatefrompng N'EXISTE PAS<br>";
}

if (function_exists('imagewebp')) {
    echo "✅ imagewebp existe<br>";
} else {
    echo "❌ imagewebp N'EXISTE PAS<br>";
}

if (extension_loaded('gd')) {
    echo "✅ Extension GD chargée<br>";
    echo "<pre>";
    print_r(gd_info());
    echo "</pre>";
} else {
    echo "❌ Extension GD NON CHARGÉE !<br>";
}