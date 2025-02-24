<?php
require '../includes/config.php';
require '../includes/auth_check.php';

if ($_SESSION['role'] !== 'editor') {
    header("Location: ../auth/login_editor.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['username'];  // Email is used as username
    $password = $_POST['password'];
    $balance = $_POST['balance'];
    $editor_id = $_SESSION['user_id'];  // ID of the logged-in editor

    try {
        // 1. Create a user for the client
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, created_by) VALUES (?, ?, 'client', ?)");
        $stmt->execute([$email, $hashed_password, $editor_id]);
        $user_id = $pdo->lastInsertId();  // Get the ID of the newly created user

        // 2. Create a client profile
        $queue_number = rand(1000, 9999);  // Random queue number
        $stmt = $pdo->prepare("INSERT INTO clients (user_id, name, balance, queue_number) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $balance, $queue_number]);

        $_SESSION['success'] = "Client created successfully!";
        header("Location: index.php");
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error creating client: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
} else {
    // If accessed directly, redirect to editor dashboard
    header("Location: index.php");
    exit;
}
?>