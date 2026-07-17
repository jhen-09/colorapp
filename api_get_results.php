<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'logged_in' => false, 'records' => []]);
    exit;
}

// 若資料表尚未建立（從未存過紀錄），回傳空陣列而不是報錯
try {
    $stmt = $pdo->prepare("
        SELECT id, color_type, color_title, skin_hex,
               DATE_FORMAT(created_at, '%Y/%m/%d %H:%i') AS created_at
        FROM analysis_results
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $records = [];
}

echo json_encode(['success' => true, 'logged_in' => true, 'records' => $records]);
