<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'logged_in' => false, 'message' => '請先登入']);
    exit;
}
$uid = $_SESSION['user_id'];

// 自動建表（比照 db.php 風格，未匯入 SQL 也能動）
$pdo->exec("CREATE TABLE IF NOT EXISTS `favorite_colors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `hex` VARCHAR(7) NOT NULL,
  `name` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_user_hex` (`user_id`, `hex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

if ($action === 'add') {
    $hex  = trim($_POST['hex'] ?? '');
    $name = mb_substr(trim($_POST['name'] ?? ''), 0, 50);
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
        echo json_encode(['success' => false, 'message' => '色碼格式錯誤']);
        exit;
    }
    // INSERT IGNORE：重複收藏同色時靜默略過（uniq_user_hex）
    $stmt = $pdo->prepare('INSERT IGNORE INTO favorite_colors (user_id, hex, name) VALUES (?, ?, ?)');
    $stmt->execute([$uid, strtoupper($hex), $name ?: null]);
    echo json_encode(['success' => true, 'added' => $stmt->rowCount() > 0]);

} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM favorite_colors WHERE id = ? AND user_id = ?'); // 只能刪自己的
    $stmt->execute([$id, $uid]);
    echo json_encode(['success' => true, 'deleted' => $stmt->rowCount() > 0]);

} else { // list
    $stmt = $pdo->prepare('SELECT id, hex, name, DATE_FORMAT(created_at, "%Y/%m/%d") AS created_at
                           FROM favorite_colors WHERE user_id = ? ORDER BY created_at DESC LIMIT 60');
    $stmt->execute([$uid]);
    echo json_encode(['success' => true, 'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
