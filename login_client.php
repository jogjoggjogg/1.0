<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'client'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'client';
            
            // Get client profile
            $stmt = $pdo->prepare("SELECT * FROM clients WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $_SESSION['client_id'] = $stmt->fetch()['id'];
            
            header("Location: ../client/index.php");
            exit;
        } else {
            $error = "Invalid client credentials!";
        }
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!-- Similar HTML to login_admin.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Client Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container" style="max-width: 400px; margin-top: 100px">
        <div class="card p-4 shadow">
            <h2 class="mb-4 text-center">Client Login</h2>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="username" class="form-control" required> <!-- Field name is "username" -->
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>