<?php
// forgot_password.php
require_once 'config/database.php';
require_once 'includes/mailer.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id, first_name FROM users WHERE email = ? AND status = 'Active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Save token
                $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE user_id = ?");
                $update->execute([$token, $expires, $user['user_id']]);

                // Send email
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/ndis/reset_password.php?token=" . $token;
                
                $subject = "Password Reset Request - GridLink";
                $body = "
                    <h3>Hello " . htmlspecialchars($user['first_name']) . ",</h3>
                    <p>We received a request to reset your password.</p>
                    <p>Click the link below to set a new password. This link will expire in 1 hour.</p>
                    <p><a href='$resetLink' style='background:#4361ee;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Reset Password</a></p>
                    <br>
                    <p>If you did not request this, please ignore this email.</p>
                ";

                $emailSent = send_email($email, $subject, $body);
                
                if ($emailSent) {
                    $success = "A password reset link has been sent to your email.";
                } else {
                    $error = "Failed to send email. Please check your Gmail SMTP settings in config/mail.php.";
                }
            } else {
                // Show same success message to prevent email enumeration
                $success = "A password reset link has been sent to your email.";
            }
        } catch(PDOException $e) {
            $error = "Database Error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GridLink NDIS - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Reset Password</h3>
            <p class="text-muted small">Enter your email to receive a reset link</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i>Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>
