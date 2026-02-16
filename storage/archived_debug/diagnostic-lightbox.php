<?php
/**
 * FICHIER DE DIAGNOSTIC
 * À placer dans : public/diagnostic-lightbox.php
 * Puis accéder via : http://localhost/diagnostic-lightbox.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostic Lightbox</h1>";
echo "<hr>";

// Test 1 : Vérifier que PHP fonctionne
echo "<h2>✅ Test 1 : PHP fonctionne</h2>";
echo "<p>Version PHP : " . phpversion() . "</p>";
echo "<hr>";

// Test 2 : Vérifier les templates literals JavaScript
echo "<h2>Test 2 : Template Literals JavaScript</h2>";
?>
<script>
const test = `Ceci est un template literal`;
console.log('✅ Template literals fonctionnent:', test);
alert('✅ JavaScript fonctionne ! Template literal : ' + test);
</script>
<p>Si tu vois une alert, les template literals fonctionnent.</p>
<hr>

<!-- Test 3 : Fonction openLightbox simple -->
<h2>Test 3 : Fonction openLightbox</h2>

<div class="review-card" data-review-id="999">
    <div class="review-photo-item" onclick="testOpenLightbox(999)" style="width:200px; height:200px; background:#ddd; cursor:pointer; border:2px solid #000; display:flex; align-items:center; justify-content:center;">
        <strong>CLIQUE ICI</strong>
    </div>
</div>

<script>
console.log('🔵 Script de test chargé');

function testOpenLightbox(reviewId) {
    console.log('🖼️ testOpenLightbox appelée avec reviewId:', reviewId);
    alert('✅ LA FONCTION MARCHE ! reviewId = ' + reviewId);
}

console.log('🧪 typeof testOpenLightbox:', typeof testOpenLightbox);
</script>

<hr>

<!-- Test 4 : Arrow functions -->
<h2>Test 4 : Arrow Functions</h2>
<script>
try {
    const arrowFunc = (x) => x * 2;
    console.log('✅ Arrow function:', arrowFunc(5));
    document.write('<p>✅ Arrow functions fonctionnent : ' + arrowFunc(5) + '</p>');
} catch(e) {
    console.error('❌ Arrow functions ne fonctionnent pas:', e);
    document.write('<p style="color:red">❌ Arrow functions ne fonctionnent pas : ' + e + '</p>');
}
</script>

<hr>

<!-- Test 5 : Backticks -->
<h2>Test 5 : Backticks dans querySelectorAll</h2>
<div id="test-backtick" data-id="123">Element de test</div>
<script>
try {
    const testId = 123;
    const element = document.querySelector(`[data-id="${testId}"]`);
    console.log('✅ Backticks fonctionnent:', element);
    document.write('<p>✅ Backticks fonctionnent : ' + (element ? 'Trouvé' : 'Non trouvé') + '</p>');
} catch(e) {
    console.error('❌ Backticks ne fonctionnent pas:', e);
    document.write('<p style="color:red">❌ Backticks ne fonctionnent pas : ' + e + '</p>');
}
</script>

<hr>

<h2>📋 Instructions</h2>
<ol>
    <li>Ouvre la console (F12)</li>
    <li>Regarde tous les messages de log</li>
    <li>Clique sur le carré gris</li>
    <li>Vérifie les résultats de chaque test</li>
</ol>

<h3>Si un test échoue :</h3>
<p>Ton navigateur ne supporte peut-être pas la syntaxe ES6 moderne.</p>
<p><strong>Solution :</strong> Remplacer les template literals par des concaténations normales</p>

<hr>

<h2>🔧 Code de remplacement si backticks ne marchent pas</h2>
<pre style="background:#f0f0f0; padding:10px; border:1px solid #ccc;">
// ❌ NE MARCHE PAS (avec backticks)
const element = document.querySelector(`[data-id="${testId}"]`);

// ✅ MARCHE (sans backticks)
const element = document.querySelector('[data-id="' + testId + '"]');
</pre>

<?php
echo "<hr>";
echo "<p><strong>Diagnostic terminé.</strong> Vérifie la console et les alerts.</p>";
?>
