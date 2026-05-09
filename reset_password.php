<?php
// reset_password.php
require_once 'config/database.php';

$success = '';
$error = '';
$validToken = false;
$userId = null;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $validToken = true;
            $userId = $user['user_id'];
        } else {
            $error = "This password reset link is invalid or has expired.";
        }
    } catch(PDOException $e) {
        $error = "Database Error.";
    }
} else {
    $error = "No token provided.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($password) || strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?");
            $stmt->execute([$hash, $userId]);
            
            $success = "Your password has been successfully reset!";
            $validToken = false; // Hide the form
        } catch(PDOException $e) {
            $error = "Error updating password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GridLink NDIS - Set New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Set New Password</h3>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-outline-primary">Go to Login</a>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($validToken): ?>
        <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="8">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Update Password</button>
        </form>
        <?php endif; ?>
        
        <?php if (!$validToken && !$success): ?>
            <div class="text-center mt-3">
                <a href="forgot_password.php" class="text-decoration-none small">Request a new link</a> | 
                <a href="index.php" class="text-decoration-none small">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
