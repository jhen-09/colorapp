<?php
$host = '127.0.0.1';
$dbname = 'color_app_db';
$user = 'root';
$pass = ''; // XAMPP 預設密碼為空

try {
    // 1. 先連線到 MySQL 伺服器 (不指定資料庫)
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. 自動建立資料庫 (如果不存在)
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;");
    
    // 3. 切換到該資料庫
    $pdo->exec("USE `$dbname`;");

    // 4. 自動建立 users 資料表 (如果不存在)
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    $pdo->exec($createTableSQL);

    // 嘗試新增 gender, birthday, avatar 欄位 (若已存在會報錯，忽略即可)
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `gender` VARCHAR(50) DEFAULT NULL, ADD COLUMN `birthday` DATE DEFAULT NULL, ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL;");
    } catch (PDOException $e) {
        // 欄位可能已存在，略過此錯誤
    }

} catch (PDOException $e) {
    die("資料庫連線與初始化失敗: " . $e->getMessage());
}
?>
