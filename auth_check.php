<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $path = explode('/', $_SERVER['REQUEST_URI']);
    if (in_array('editor', $path)) {
        header("Location: ../auth/login_editor.php");
    } elseif (in_array('client', $path)) {
        header("Location: ../auth/login_client.php");
    }
    exit;
}


// Role-specific redirects
if (isset($_SESSION['role'])) {
    $requestedPath = $_SERVER['REQUEST_URI'];
    
    if (strpos($requestedPath, '/editor/') !== false && $_SESSION['role'] !== 'editor') {
        header("Location: ../auth/login_editor.php");
        exit;
    }
    
    if (strpos($requestedPath, '/client/') !== false && $_SESSION['role'] !== 'client') {
        header("Location: ../auth/login_client.php");
        exit;
    }
}
?>