<?php
session_start();

// Database Configuration
$host = "localhost";
$dbname = "finance_portal";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Authentication Check
function require_role($role) {
    if ($_SESSION['role'] !== $role) {
        header("Location: ../auth/login_$role.php");
        exit;
    }
}
?>