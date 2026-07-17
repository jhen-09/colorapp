<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'logged_in' => false, 'message' => '請先登入']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '請使用 POST']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
// WHERE user_id 確保只能刪除自己的紀錄
$stmt = $pdo->prepare('DELETE FROM analysis_results WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
echo json_encode(['success' => true, 'deleted' => $stmt->rowCount() > 0]);
