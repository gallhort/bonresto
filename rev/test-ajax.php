<?php
session_start();
header('Content-Type: application/json');

if (isset($_POST['id']) && isset($_POST['useful'])) {
    echo json_encode([
        'status' => 'test_ok',
        'received_id' => $_POST['id'],
        'received_useful' => $_POST['useful'],
        'session_user' => $_SESSION['user'] ?? 'anon'
    ]);
} else {
    echo json_encode([
        'status' => 'test_fail',
        'post_data' => $_POST
    ]);
}
?>