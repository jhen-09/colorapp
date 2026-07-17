<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'success',
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'avatar' => $_SESSION['avatar'] ?? '',
            'gender' => $_SESSION['gender'] ?? '',
            'birthday' => $_SESSION['birthday'] ?? ''
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'logged_in' => false, 'message' => 'Not logged in']);
}
