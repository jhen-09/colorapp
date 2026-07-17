<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'logged_in' => false, 'message' => '請先登入']);
    exit;
}
$uid = $_SESSION['user_id'];

$pdo->exec("CREATE TABLE IF NOT EXISTS `makeup_looks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `season_type` VARCHAR(50) DEFAULT NULL,
  `occasion` VARCHAR(30) DEFAULT NULL,
  `lip_hex` VARCHAR(7) DEFAULT NULL,
  `lip_opacity` TINYINT DEFAULT NULL,
  `lip_texture` VARCHAR(10) DEFAULT NULL,
  `eye_hex` VARCHAR(7) DEFAULT NULL,
  `eye_opacity` TINYINT DEFAULT NULL,
  `blush_hex` VARCHAR(7) DEFAULT NULL,
  `blush_opacity` TINYINT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_looks_user_time` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

// hex 驗證：不合法回傳 null（單項不擋整筆）
function hexOrNull($v) {
    $v = trim($v ?? '');
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $v) ? strtoupper($v) : null;
}
function opacityOrNull($v) {
    if ($v === null || $v === '') return null;
    $n = intval($v);
    return ($n >= 0 && $n <= 100) ? $n : null;
}

if ($action === 'save') {
    $stmt = $pdo->prepare('INSERT INTO makeup_looks
        (user_id, season_type, occasion, lip_hex, lip_opacity, lip_texture, eye_hex, eye_opacity, blush_hex, blush_opacity)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $ok = $stmt->execute([
        $uid,
        mb_substr(trim($_POST['season_type'] ?? ''), 0, 50) ?: null,
        mb_substr(trim($_POST['occasion'] ?? ''), 0, 30) ?: null,
        hexOrNull($_POST['lip_hex'] ?? ''),
        opacityOrNull($_POST['lip_opacity'] ?? null),
        in_array($_POST['lip_texture'] ?? '', ['matte', 'glossy'], true) ? $_POST['lip_texture'] : null,
        hexOrNull($_POST['eye_hex'] ?? ''),
        opacityOrNull($_POST['eye_opacity'] ?? null),
        hexOrNull($_POST['blush_hex'] ?? ''),
        opacityOrNull($_POST['blush_opacity'] ?? null),
    ]);
    echo json_encode(['success' => (bool)$ok]);

} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM makeup_looks WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $uid]);
    echo json_encode(['success' => true, 'deleted' => $stmt->rowCount() > 0]);

} else { // list
    $stmt = $pdo->prepare('SELECT id, season_type, occasion, lip_hex, lip_opacity, lip_texture,
                                  eye_hex, eye_opacity, blush_hex, blush_opacity,
                                  DATE_FORMAT(created_at, "%Y/%m/%d %H:%i") AS created_at
                           FROM makeup_looks WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
    $stmt->execute([$uid]);
    echo json_encode(['success' => true, 'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
