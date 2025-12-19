<?php

class Login {

    private $error = "";

    public function evaluate($data)
    {
        foreach ($data as $key => $value) {
            if (empty($value)) {
                $this->error .= $key . " Champ vide !<br>";
            }
        }

        if ($this->error === "") {
            $this->check_user($data);
        } else {
            return $this->error;
        }
    }

    private function check_user($data)
    {
        $login    = $data['login_name'];
        $password = $data['login_pass'];

        $db   = new Database();
        $conn = $db->connect();

        // 🔐 Requête sécurisée
        $stmt = $conn->prepare("
            SELECT login, admin 
            FROM users 
            WHERE login = ? AND passwd = ?
            LIMIT 1
        ");
        $stmt->bind_param("ss", $login, $password);
        $stmt->execute();

        // ✅ COMPATIBLE TOUTES VERSIONS PHP
        $stmt->bind_result($db_login, $db_admin);

        if ($stmt->fetch()) {

            require_once __DIR__ . '/../auth/auth.php';

            // Connexion utilisateur
            loginUser($db_login, true);

            // Flag admin
            $_SESSION['admin'] = (int)$db_admin;

            // ===== REMEMBER TOKEN =====
            $token       = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);

            $stmtToken = $conn->prepare("
                INSERT INTO user_tokens (user, token, expires_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
            ");
            $stmtToken->bind_param("ss", $db_login, $hashedToken);
            $stmtToken->execute();

            setcookie(
                'remember_user',
                $db_login . ':' . $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                false,
                true
            );

            // 🔁 Redirection
    
                header('Location: ../index.php?login=ok');
            

        } else {
            echo 'connexion NOK';
        }
    }
}
