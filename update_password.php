<?php
require 'includes/config.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->execute([$hashed_password, $username]);
    echo "Password updated successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>