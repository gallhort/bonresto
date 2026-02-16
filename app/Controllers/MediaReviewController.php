<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use PDO;

/**
 * F31 - Media Review Controller
 * Upload et suppression de fichiers video/audio associes aux avis
 * Formats supportes: mp4, webm (video), mp3, wav, ogg (audio)
 * Limites: 50 Mo video, 10 Mo audio
 */
class MediaReviewController extends Controller
{
    /** Extensions et types MIME autorises par categorie */
    private const ALLOWED_VIDEO = [
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];

    private const ALLOWED_AUDIO = [
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
    ];

    /** Tailles max en octets */
    private const MAX_VIDEO_SIZE = 50 * 1024 * 1024; // 50 MB
    private const MAX_AUDIO_SIZE = 10 * 1024 * 1024; // 10 MB

    /**
     * Upload un fichier media (video ou audio) pour un avis
     * POST /api/reviews/{id}/media
     *
     * Seul l'auteur de l'avis peut uploader un fichier.
     * Un seul fichier media par avis (remplace l'existant).
     */
    public function upload(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $reviewId = (int)$request->param('id');

        if ($reviewId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID d\'avis invalide']);
            return;
        }

        // Rate limit: 10 uploads per hour
        if (!RateLimiter::attempt("media_upload_$userId", 10, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop d\'uploads. Reessayez plus tard.']);
            return;
        }

        // Verify review exists and belongs to user
        $stmt = $this->db->prepare("
            SELECT id, user_id, restaurant_id, media_path, media_type
            FROM reviews
            WHERE id = :rid
        ");
        $stmt->execute([':rid' => $reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Avis introuvable']);
            return;
        }

        if ((int)$review['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez modifier que vos propres avis']);
            return;
        }

        // Check file upload
        if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['media']['error'] ?? UPLOAD_ERR_NO_FILE;
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE   => 'Fichier trop volumineux (limite serveur)',
                UPLOAD_ERR_FORM_SIZE  => 'Fichier trop volumineux (limite formulaire)',
                UPLOAD_ERR_PARTIAL    => 'Fichier partiellement telecharge',
                UPLOAD_ERR_NO_FILE    => 'Aucun fichier envoye',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'ecrire le fichier',
            ];
            $errorMsg = $errorMessages[$errorCode] ?? 'Erreur d\'upload inconnue';

            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $errorMsg]);
            return;
        }

        $file = $_FILES['media'];
        $tmpPath = $file['tmp_name'];
        $originalName = $file['name'];
        $fileSize = $file['size'];

        // Detect MIME type from file content (not from user-supplied Content-Type)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($tmpPath);

        // Determine media type (video or audio) and validate
        $mediaType = null;
        $allowedExt = null;

        // Check video types
        foreach (self::ALLOWED_VIDEO as $ext => $mime) {
            if ($detectedMime === $mime) {
                $mediaType = 'video';
                $allowedExt = $ext;
                break;
            }
        }

        // Check audio types if not video
        if ($mediaType === null) {
            foreach (self::ALLOWED_AUDIO as $ext => $mime) {
                if ($detectedMime === $mime) {
                    $mediaType = 'audio';
                    $allowedExt = $ext;
                    break;
                }
            }
        }

        if ($mediaType === null) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Format non supporte. Formats acceptes : mp4, webm (video), mp3, wav, ogg (audio)',
                'detected_mime' => $detectedMime,
            ]);
            return;
        }

        // Validate file size
        $maxSize = ($mediaType === 'video') ? self::MAX_VIDEO_SIZE : self::MAX_AUDIO_SIZE;
        $maxSizeMB = ($mediaType === 'video') ? 50 : 10;

        if ($fileSize > $maxSize) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => "Fichier trop volumineux. Maximum : {$maxSizeMB} Mo pour les fichiers {$mediaType}",
            ]);
            return;
        }

        if ($fileSize <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le fichier est vide']);
            return;
        }

        // Build upload path
        $uploadDir = ROOT_PATH . '/public/uploads/reviews/media/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = $mediaType . '_' . $reviewId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedExt;
        $fullPath = $uploadDir . $filename;
        $relativePath = '/uploads/reviews/media/' . $filename;

        // Delete existing media file if any
        if (!empty($review['media_path'])) {
            $existingFile = ROOT_PATH . '/public' . $review['media_path'];
            if (file_exists($existingFile)) {
                @unlink($existingFile);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($tmpPath, $fullPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Impossible de sauvegarder le fichier']);
            return;
        }

        // Update review in database
        $updateStmt = $this->db->prepare("
            UPDATE reviews
            SET media_path = :path, media_type = :type, updated_at = NOW()
            WHERE id = :rid
        ");
        $updateStmt->execute([
            ':path' => $relativePath,
            ':type' => $mediaType,
            ':rid' => $reviewId,
        ]);

        echo json_encode([
            'success' => true,
            'message' => ucfirst($mediaType) . ' ajoute a votre avis avec succes !',
            'media' => [
                'path' => $relativePath,
                'type' => $mediaType,
                'extension' => $allowedExt,
                'size' => $fileSize,
                'size_human' => $this->formatFileSize($fileSize),
            ],
        ]);
    }

    /**
     * Supprimer le media d'un avis
     * POST /api/reviews/{id}/media/delete
     *
     * Seul l'auteur de l'avis peut supprimer le media.
     */
    public function delete(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $reviewId = (int)$request->param('id');

        if ($reviewId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID d\'avis invalide']);
            return;
        }

        // Verify review exists and belongs to user
        $stmt = $this->db->prepare("
            SELECT id, user_id, media_path, media_type
            FROM reviews
            WHERE id = :rid
        ");
        $stmt->execute([':rid' => $reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Avis introuvable']);
            return;
        }

        if ((int)$review['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez modifier que vos propres avis']);
            return;
        }

        if (empty($review['media_path']) || ($review['media_type'] ?? 'none') === 'none') {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Aucun media associe a cet avis']);
            return;
        }

        // Delete file from disk
        $filePath = ROOT_PATH . '/public' . $review['media_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Reset media fields in database
        $updateStmt = $this->db->prepare("
            UPDATE reviews
            SET media_path = NULL, media_type = 'none', updated_at = NOW()
            WHERE id = :rid
        ");
        $updateStmt->execute([':rid' => $reviewId]);

        echo json_encode([
            'success' => true,
            'message' => 'Media supprime avec succes',
        ]);
    }

    /**
     * Formater une taille de fichier en unite lisible
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' Mo';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' Ko';
        }
        return $bytes . ' o';
    }
}
