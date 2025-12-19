<?php
// CONFIG
include_once __DIR__ . '/connect.php';
$mysqli = new mysqli($mysqliHost, $mysqliUser, $mysqliPass, $mysqliDb);
if ($mysqli->connect_error) die("Erreur DB : " . $mysqli->connect_error);

$folder = __DIR__ . "/assets/images/vendeur/"; // chemin absolu
$extensions = ['jpg','jpeg','png','webp'];

// Normalize helper : remove accents, lowercase, keep only a-z0-9
function normalize($str) {
    $str = trim($str);
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $str = strtolower($str);
    // remove everything except letters and numbers
    $str = preg_replace('/[^a-z0-9]/', '', $str);
    return $str;
}

// Build map of existing files in folder => normalized form
$files = scandir($folder);
$fileMap = []; // normalized => array of filenames
foreach ($files as $f) {
    if (in_array($f, ['.','..'])) continue;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (!in_array($ext, $extensions)) continue;
    $norm = normalize(pathinfo($f, PATHINFO_FILENAME)); // without extension
    if (!isset($fileMap[$norm])) $fileMap[$norm] = [];
    $fileMap[$norm][] = $f;
}

// Query rows to treat (photos.main contains target name like "assets/images/vendeur/Bachawarma restaurant.jpg")
$sql = "SELECT Nom, main FROM photos";
$res = $mysqli->query($sql);
if (!$res) die("Erreur SQL: " . $mysqli->error);

while ($row = $res->fetch_assoc()) {
    $nom = $row['Nom'];
    $mainInDb = $row['main']; // e.g. assets/images/vendeur/Bachawarma restaurant.jpg

    // target filename as in DB (basename)
    $targetFilename = basename($mainInDb);
    $targetPath = $folder . $targetFilename;

    // If already exists -> skip
    if (file_exists($targetPath)) {
        echo "✅ Déjà ok : $targetFilename\n";
        continue;
    }

    // Build normalized keys to search
    // 1) normalize the Nom (preferred) => this represents expected name to match
    $normFromNom = normalize($nom);

    // 2) also consider normalization from basename of mainInDb (in case DB holds different spelling)
    $normFromDbBasename = normalize(pathinfo($targetFilename, PATHINFO_FILENAME));

    // Candidate lookup order:
    // A) exact normalized match for nom
    $candidate = null;
    if (isset($fileMap[$normFromNom])) {
        $candidate = $fileMap[$normFromNom][0]; // take first candidate
        $reason = "match_norm_nom";
    }
    // B) exact normalized match for DB basename
    elseif (isset($fileMap[$normFromDbBasename])) {
        $candidate = $fileMap[$normFromDbBasename][0];
        $reason = "match_norm_dbname";
    }
    // C) fuzzy search: compute levenshtein between normFromNom and every key, pick minimal
    else {
        $best = null; $bestDist = PHP_INT_MAX; $bestKey = null;
        foreach ($fileMap as $key => $arr) {
            $dist = levenshtein($normFromNom, $key);
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = $arr[0];
                $bestKey = $key;
            }
        }
        // Accept if distance small relative to length (threshold tunable)
        if ($best !== null) {
            $len = max(1, strlen($normFromNom));
            $ratio = $bestDist / $len;
            // threshold: accept if ratio <= 0.35 OR absolute dist <= 2
            if ($ratio <= 0.35 || $bestDist <= 2) {
                $candidate = $best;
                $reason = "fuzzy (dist=$bestDist, ratio=" . round($ratio,2) . ")";
            }
        }
    }

    if ($candidate === null) {
        echo "❌ Introuvable pour '$nom' (norm='$normFromNom')\n";
        continue;
    }

    // Candidate exists: we will rename it to the exact name present in DB (basename)
    $src = $folder . $candidate;
    $dst = $targetPath;

    // If destination exists (unlikely because we checked earlier) skip
    if (file_exists($dst)) {
        echo "⚠ Destination existe déjà: $targetFilename (skip)\n";
        continue;
    }

    // Perform rename
    if (rename($src, $dst)) {
        echo "✔ Renommé [$reason] : $candidate  →  $targetFilename\n";
        // Note: we DO NOT touch DB as requested
        // Optionally remove candidate from map to avoid reuse
        $keyToRemove = normalize(pathinfo($candidate, PATHINFO_FILENAME));
        if (isset($fileMap[$keyToRemove])) {
            // remove $candidate from array
            $idx = array_search($candidate, $fileMap[$keyToRemove]);
            if ($idx !== false) unset($fileMap[$keyToRemove][$idx]);
            if (empty($fileMap[$keyToRemove])) unset($fileMap[$keyToRemove]);
        }
    } else {
        echo "✖ Échec renommage: $candidate → $targetFilename\n";
    }
}

echo "Terminé.\n";
