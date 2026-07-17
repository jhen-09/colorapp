<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $birthday = $_POST['birthday'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($gender) || empty($birthday)) {
        echo json_encode(['success' => false, 'message' => '請填寫完整資訊']);
        exit;
    }

    // 檢查 email 是否已存在
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => '此 Email 已經註冊過']);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 設定預設可愛大頭貼 (DiceBear 7.x micah 不支援 hair 參數，改用 seed 加綴字產生不同風格)
    $avatar = 'https://api.dicebear.com/7.x/micah/svg?seed=' . urlencode($username); // 預設使用使用者名稱
    if ($gender === 'female') {
        $avatar = 'https://api.dicebear.com/7.x/micah/svg?seed=' . urlencode($username . 'Female');
    } else if ($gender === 'male') {
        $avatar = 'https://api.dicebear.com/7.x/micah/svg?seed=' . urlencode($username . 'Male');
    }

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, gender, birthday, avatar) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt->execute([$username, $email, $hashed_password, $gender, $birthday, $avatar])) {
        // 註冊成功後自動登入
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['avatar'] = $avatar;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '註冊失敗，請稍後再試']);
    }
}
