<?php
require_once '../includes/config.php';
require_role('editor');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_client'])) {
        // Create new client logic
        $user_id = create_client_user($_POST);
        create_client_profile($user_id, $_POST, $_SESSION['user_id']);
        $_SESSION['success'] = "Client created successfully!";
    } elseif (isset($_POST['update_client'])) {
        update_client_profile($_POST, $_SESSION['user_id']);
        $_SESSION['success'] = "Client updated successfully!";
    }
    header("Location: index.php");
    exit;
}

// Get editor's clients
$clients = get_editor_clients($_SESSION['user_id']);

function create_client_user($data) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'client')");
        $stmt->execute([$data['email'], password_hash($data['password'], PASSWORD_DEFAULT)]);
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        $_SESSION['error'] = "User creation failed: " . $e->getMessage();
        return false;
    }
}

function create_client_profile($user_id, $data, $editor_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO clients 
            (user_id, created_by, name, email, phone, verified, telegram_link, balance, payout_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $editor_id,
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['verified'],
            $data['telegram'],
            $data['balance'],
            date('Y-m-d', strtotime($data['payout_date']))
        ]);
    } catch(PDOException $e) {
        $_SESSION['error'] = "Profile creation failed: " . $e->getMessage();
    }
}

function update_client_profile($data, $editor_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE clients SET
            name = ?, phone = ?, verified = ?, telegram_link = ?,
            balance = ?, payout_date = ?
            WHERE id = ? AND created_by = ?");
        $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['verified'],
            $data['telegram'],
            $data['balance'],
            date('Y-m-d', strtotime($data['payout_date'])),
            $data['client_id'],
            $editor_id
        ]);
    } catch(PDOException $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
    }
}

function get_editor_clients($editor_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE created_by = ?");
        $stmt->execute([$editor_id]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error loading clients: " . $e->getMessage();
        return [];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Editor Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-form { transition: all 0.3s ease; }
        .editing { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Header and Success/Error Messages -->
        <div class="d-flex justify-content-between mb-4">
            <h1>Editor Panel</h1>
            <div>
                <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php include '../includes/messages.php'; ?>

        <!-- Client Management Form -->
        <div class="card mb-4 edit-form" id="clientForm">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="client_id" id="clientId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-md-6">
                            <input type="tel" name="phone" class="form-control" placeholder="Phone" required>
                        </div>
                        <div class="col-md-6">
                            <select name="verified" class="form-select" required>
                                <option value="no">Not Verified</option>
                                <option value="yes">Verified</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="url" name="telegram" class="form-control" 
                                   placeholder="Telegram Link" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="balance" class="form-control" 
                                   placeholder="Balance" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <input type="date" name="payout_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Client Password" id="passwordField" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="create_client" 
                                    class="btn btn-success" id="submitButton">
                                Create Client
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Client List -->
        <div class="card">
            <div class="card-body">
                <h3>Managed Clients</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Payout Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?= htmlspecialchars($client['name']) ?></td>
                            <td>
                                <a href="<?= $client['telegram_link'] ?>" target="_blank">
                                    <?= $client['phone'] ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-<?= $client['verified'] === 'yes' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($client['verified']) ?>
                                </span>
                            </td>
                            <td><?= date('m/d/Y', strtotime($client['payout_date'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-client" 
                                        data-client='<?= htmlentities(json_encode($client)) ?>'>
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Edit Client Functionality
        document.querySelectorAll('.edit-client').forEach(btn => {
            btn.addEventListener('click', () => {
                const client = JSON.parse(btn.dataset.client);
                document.querySelector('input[name="name"]').value = client.name;
                document.querySelector('input[name="email"]').value = client.email;
                document.querySelector('input[name="phone"]').value = client.phone;
                document.querySelector('select[name="verified"]').value = client.verified;
                document.querySelector('input[name="telegram"]').value = client.telegram_link;
                document.querySelector('input[name="balance"]').value = client.balance;
                document.querySelector('input[name="payout_date"]').value = 
                    new Date(client.payout_date).toISOString().split('T')[0];
                document.querySelector('#clientId').value = client.id;
                
                document.querySelector('#passwordField').required = false;
                document.querySelector('#submitButton').name = 'update_client';
                document.querySelector('#submitButton').textContent = 'Update Client';
                document.querySelector('#clientForm').classList.add('editing');
            });
        });
    </script>
</body>
</html>