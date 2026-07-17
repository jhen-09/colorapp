<?php
require 'db.php';
$pdo->exec("UPDATE users SET avatar = REPLACE(REPLACE(avatar, '&hair=short', ''), '&hair=long', '')");
echo "Fixed DB";
?>
