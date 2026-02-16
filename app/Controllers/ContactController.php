<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;

class ContactController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('contact/index', [
            'title' => 'Nous contacter - Le Bon Resto',
            'meta_description' => 'Contactez l\'équipe Le Bon Resto pour toute question, suggestion ou signalement.',
            'success' => $_SESSION['contact_success'] ?? null,
        ]);
        unset($_SESSION['contact_success']);
    }

    public function send(Request $request): void
    {
        if (!verify_csrf()) {
            $_SESSION['contact_error'] = 'Token CSRF invalide. Veuillez rafraichir la page.';
            $this->redirect('/contact');
            return;
        }

        // Rate limiting: 3 messages per 10 minutes
        if (!RateLimiter::attempt('contact_' . session_id(), 3, 600)) {
            $_SESSION['contact_error'] = 'Trop de messages envoyés. Veuillez réessayer dans quelques minutes.';
            $this->redirect('/contact');
            return;
        }

        $name = trim($request->post('name') ?? '');
        $email = trim($request->post('email') ?? '');
        $subject = trim($request->post('subject') ?? '');
        $message = trim($request->post('message') ?? '');

        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Le nom est requis (minimum 2 caractères).';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Une adresse email valide est requise.';
        }
        if (empty($subject)) {
            $errors[] = 'Le sujet est requis.';
        }
        if (empty($message) || strlen($message) < 10) {
            $errors[] = 'Le message doit contenir au moins 10 caractères.';
        }
        if (strlen($message) > 5000) {
            $errors[] = 'Le message ne doit pas dépasser 5000 caractères.';
        }

        // Honeypot check
        if (!empty($request->post('website'))) {
            // Bot detected, silently redirect
            $this->redirect('/contact');
            return;
        }

        if (!empty($errors)) {
            $_SESSION['contact_errors'] = $errors;
            $_SESSION['contact_old'] = compact('name', 'email', 'subject', 'message');
            $this->redirect('/contact');
            return;
        }

        // Store in database
        $stmt = $this->db->prepare("
            INSERT INTO contact_messages (name, email, subject, message, ip_address, user_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $name,
            $email,
            $subject,
            $message,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SESSION['user']['id'] ?? null
        ]);

        $_SESSION['contact_success'] = 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.';
        $this->redirect('/contact');
    }
}
