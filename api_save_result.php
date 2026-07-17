<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// 確保 analysis_results 資料表存在（比照 db.php 的自動建表風格）
$pdo->exec("
CREATE TABLE IF NOT EXISTS `analysis_results` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT NOT NULL,
    `color_type`  VARCHAR(50)  NOT NULL,
    `color_title` VARCHAR(50)  NOT NULL,
    `skin_hex`    VARCHAR(10)  DEFAULT NULL,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_time` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '請使用 POST']);
    exit;
}

// 未登入者不儲存（前端會靜默略過，不影響瀏覽結果）
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'logged_in' => false, 'message' => '未登入']);
    exit;
}

$colorType  = trim($_POST['color_type'] ?? '');
$colorTitle = trim($_POST['color_title'] ?? '');
$skinHex    = trim($_POST['skin_hex'] ?? '');

// 白名單驗證，避免寫入奇怪的值
$validTypes = [
    'light-spring','warm-spring','bright-spring',
    'light-summer','cool-summer','soft-summer',
    'soft-autumn','warm-autumn','deep-autumn',
    'bright-winter','cool-winter','deep-winter'
];
if (!in_array($colorType, $validTypes, true) || $colorTitle === '') {
    echo json_encode(['success' => false, 'message' => '參數錯誤']);
    exit;
}
if ($skinHex !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $skinHex)) {
    $skinHex = ''; // 格式不對就不存，不擋整筆
}

$stmt = $pdo->prepare('INSERT INTO analysis_results (user_id, color_type, color_title, skin_hex) VALUES (?, ?, ?, ?)');
$ok = $stmt->execute([
    $_SESSION['user_id'],
    $colorType,
    $colorTitle,
    $skinHex !== '' ? $skinHex : null
]);

echo json_encode(['success' => (bool)$ok]);
