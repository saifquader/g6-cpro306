<?php
// index.php
require_once 'config/database.php';

// If already logged in (via session or auto-login cookie), redirect
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, password_hash, role_id FROM users WHERE email = ? AND status = 'Active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Set Session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role_id'] = $user['role_id'];

                // Handle Remember Me
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); // Create random token
                    $tokenHash = hash('sha256', $token); // Store hash in DB
                    $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days

                    // Insert into DB
                    $insertToken = $pdo->prepare("INSERT INTO user_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
                    $insertToken->execute([$user['user_id'], $tokenHash, $expires]);

                    // Set Cookie (store plain token)
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // HttpOnly
                }

                if ($user['role_id'] === 'role-participant') {
                    redirect('participant_dashboard.php');
                } else {
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Invalid email or password. Please try again.';
            }
        } catch(PDOException $e) {
            $error = "Database Error. Please contact support.";
        }
    } else {
        $error = 'Please enter your email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - GridLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="auth-mesh-bg">
    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode" data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Theme">
        <i class="fa-solid fa-moon"></i>
    </button>

    <!-- Centered Glass Card -->
    <div class="apple-glass-card">
        
        <div class="brand-minimal text-center">
            <div class="brand-icon-wrapper">
                <i class="fa-solid fa-hands-holding-child"></i>
            </div>
            GridLink
        </div>

        <div class="text-center mb-4">
            <h3 class="fw-bold mb-1">Welcome back</h3>
            <p class="text-muted small">Enter your details to securely sign in.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center border-0 rounded-3 small py-2" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php" class="auth-form needs-validation" novalidate>
            <!-- Email Input -->
            <div class="form-floating position-relative">
                <input type="email" class="form-control ps-5" id="email" name="email" required placeholder="Enter your email" autocomplete="off" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label for="email" class="ps-5">Email address</label>
                <i class="fa-solid fa-envelope input-icon"></i>
            </div>

            <!-- Password Input -->
            <div class="form-floating position-relative">
                <input type="password" class="form-control ps-5 pe-5" id="password" name="password" required placeholder="Enter your password" autocomplete="off">
                <label for="password" class="ps-5">Password</label>
                <i class="fa-solid fa-lock input-icon"></i>
                <i class="fa-solid fa-eye password-toggle toggle-password" data-target="password" title="Show/Hide Password"></i>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <div class="form-check">
                    <input class="form-check-input shadow-none" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label text-muted small" for="remember">
                        Remember me
                    </label>
                </div>
                <a href="forgot_password.php" class="text-decoration-none small fw-semibold text-primary">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-premium mb-4">Sign In</button>

            <!-- Social Logins (Google Material styling) -->
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <button type="button" class="btn btn-social-minimal w-100" onclick="alert('Google Auth Integration Pending API Keys')">
                        <i class="fa-brands fa-google text-danger me-2"></i> Google
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-social-minimal w-100" onclick="alert('GitHub Auth Integration Pending API Keys')">
                        <i class="fa-brands fa-github me-2"></i> GitHub
                    </button>
                </div>
            </div>
            
            <div class="text-center mt-2 border-top pt-3">
                <span class="text-muted small">New Participant? </span>
                <a href="register.php" class="text-decoration-none small fw-semibold text-primary">Register for Portal</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
