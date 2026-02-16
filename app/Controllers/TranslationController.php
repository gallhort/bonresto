<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CacheService;
use PDO;

/**
 * F28 - Translation Controller (i18n Infrastructure)
 * Gestion de la localisation : changement de langue et chargement des traductions
 * Langues supportees : fr (francais), ar (arabe), en (anglais)
 *
 * Les traductions sont stockees en base dans la table `translations`
 * et mises en cache pour 1 heure.
 */
class TranslationController extends Controller
{
    /** Locales supportees */
    private const SUPPORTED_LOCALES = ['fr', 'ar', 'en'];

    /** Locale par defaut */
    private const DEFAULT_LOCALE = 'fr';

    /**
     * Definir la locale de l'utilisateur
     * POST /api/locale
     *
     * Accepte 'fr', 'ar', 'en'. Stocke dans $_SESSION['locale'].
     */
    public function setLocale(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $locale = trim($input['locale'] ?? '');

        if (empty($locale) || !in_array($locale, self::SUPPORTED_LOCALES)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Locale invalide. Valeurs acceptees : ' . implode(', ', self::SUPPORTED_LOCALES),
            ]);
            return;
        }

        $_SESSION['locale'] = $locale;

        // Map locale to display info
        $localeNames = [
            'fr' => ['name' => 'Francais', 'dir' => 'ltr'],
            'ar' => ['name' => 'العربية', 'dir' => 'rtl'],
            'en' => ['name' => 'English', 'dir' => 'ltr'],
        ];

        $info = $localeNames[$locale];

        echo json_encode([
            'success' => true,
            'locale' => $locale,
            'name' => $info['name'],
            'direction' => $info['dir'],
            'message' => 'Langue mise a jour',
        ]);
    }

    /**
     * Recuperer toutes les traductions pour une locale
     * GET /api/translations/{locale}
     *
     * Retourne un objet JSON plat { "key": "value", ... }
     * Les cles sont au format "group.key" (ex: "nav.home", "auth.login")
     * Mis en cache pendant 1 heure.
     */
    public function getTranslations(Request $request): void
    {
        header('Content-Type: application/json');

        $locale = trim($request->param('locale') ?? '');

        if (empty($locale) || !in_array($locale, self::SUPPORTED_LOCALES)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Locale invalide. Valeurs acceptees : ' . implode(', ', self::SUPPORTED_LOCALES),
            ]);
            return;
        }

        $cache = new CacheService();
        $cacheKey = "translations_{$locale}";

        $translations = $cache->remember($cacheKey, function () use ($locale) {
            return $this->loadTranslationsFromDb($locale);
        }, 3600);

        // Also check for file-based translations as fallback
        $fileTranslations = $this->loadTranslationsFromFile($locale);

        // Merge: DB translations take priority over file-based
        $merged = array_merge($fileTranslations, $translations);

        echo json_encode([
            'success' => true,
            'locale' => $locale,
            'direction' => ($locale === 'ar') ? 'rtl' : 'ltr',
            'count' => count($merged),
            'translations' => $merged,
        ]);
    }

    /**
     * Charger les traductions depuis la base de donnees
     * Table: translations (locale, translation_key, translation_value)
     */
    private function loadTranslationsFromDb(string $locale): array
    {
        $translations = [];

        try {
            $stmt = $this->db->prepare("
                SELECT translation_key, translation_value
                FROM translations
                WHERE locale = :locale
                ORDER BY translation_key
            ");
            $stmt->execute([':locale' => $locale]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $translations[$row['translation_key']] = $row['translation_value'];
            }
        } catch (\Exception $e) {
            // Table may not exist yet during initial setup
            // Return empty array - file-based translations will be used as fallback
        }

        return $translations;
    }

    /**
     * Charger les traductions depuis un fichier PHP
     * Fichier: config/lang/{locale}.php retournant un tableau associatif
     */
    private function loadTranslationsFromFile(string $locale): array
    {
        $langFile = ROOT_PATH . '/config/lang/' . $locale . '.php';

        if (!file_exists($langFile)) {
            return [];
        }

        $data = include $langFile;

        if (!is_array($data)) {
            return [];
        }

        // Flatten nested array to dot notation keys
        return $this->flattenArray($data);
    }

    /**
     * Aplatir un tableau multi-dimensionnel en notation point
     * ['nav' => ['home' => 'Accueil']] => ['nav.home' => 'Accueil']
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix !== '' ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $fullKey));
            } else {
                $result[$fullKey] = (string)$value;
            }
        }

        return $result;
    }

    /**
     * Helper statique : Obtenir la locale courante depuis la session
     */
    public static function getCurrentLocale(): string
    {
        return $_SESSION['locale'] ?? self::DEFAULT_LOCALE;
    }

    /**
     * Helper statique : Obtenir la direction du texte pour la locale courante
     */
    public static function getCurrentDirection(): string
    {
        $locale = self::getCurrentLocale();
        return ($locale === 'ar') ? 'rtl' : 'ltr';
    }
}
