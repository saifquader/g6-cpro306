<?php
// register.php
require_once 'config/database.php';

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role_id'] === 'role-participant') {
        redirect('participant_dashboard.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $ndis_number = $_POST['ndis_number'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $address = $_POST['address'] ?? '';
    $emergency = $_POST['emergency'] ?? '';

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($password) && !empty($ndis_number)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email is already registered.';
            } else {
                $pdo->beginTransaction();

                $user_id = generate_uuid();
                $hash = password_hash($password, PASSWORD_BCRYPT);
                
                // 1. Create User (role-participant)
                $stmtUser = $pdo->prepare("INSERT INTO users (user_id, organisation_id, role_id, first_name, last_name, email, password_hash, status) VALUES (?, 'org-1', 'role-participant', ?, ?, ?, ?, 'Active')");
                $stmtUser->execute([$user_id, $first_name, $last_name, $email, $hash]);

                // 2. Create Linked Participant
                $participant_id = generate_uuid();
                $stmtPart = $pdo->prepare("INSERT INTO participants (participant_id, organisation_id, user_id, first_name, last_name, ndis_number, date_of_birth, address, emergency_contact, support_needs) VALUES (?, 'org-1', ?, ?, ?, ?, ?, ?, ?, 'Registered via Portal')");
                $stmtPart->execute([$participant_id, $user_id, $first_name, $last_name, $ndis_number, $dob, $address, $emergency]);

                $pdo->commit();
                
                // Auto-login after registration
                $_SESSION['user_id'] = $user_id;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['role_id'] = 'role-participant';
                
                redirect('participant_dashboard.php');
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Registration Error. Please try again or contact support.";
        }
    } else {
        $error = 'Please fill out all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Registration - GridLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-mesh-bg">
    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
        <i class="fa-solid fa-moon"></i>
    </button>

    <!-- Centered Glass Card -->
    <div class="apple-glass-card" style="max-width: 600px;">
        
        <div class="brand-minimal text-center mb-3">
            <div class="brand-icon-wrapper">
                <i class="fa-solid fa-hands-holding-child"></i>
            </div>
            GridLink Registration
        </div>

        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1">Create your Participant Portal Account</h5>
            <p class="text-muted small">Enter your details to register securely.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center border-0 rounded-3 small py-2" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="auth-form needs-validation" novalidate>
            <div class="row g-2">
                <div class="col-md-6 form-floating position-relative">
                    <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="First Name">
                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-6 form-floating position-relative">
                    <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Last Name">
                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-12 form-floating position-relative">
                    <input type="email" class="form-control ps-5" id="email" name="email" required placeholder="Email">
                    <label for="email" class="ps-5">Email Address <span class="text-danger">*</span></label>
                    <i class="fa-solid fa-envelope input-icon"></i>
                </div>
                <div class="col-md-12 form-floating position-relative">
                    <input type="password" class="form-control ps-5" id="password" name="password" required placeholder="Password">
                    <label for="password" class="ps-5">Password <span class="text-danger">*</span></label>
                    <i class="fa-solid fa-lock input-icon"></i>
                </div>
                <div class="col-md-6 form-floating position-relative">
                    <input type="text" class="form-control" id="ndis_number" name="ndis_number" required placeholder="NDIS Number">
                    <label for="ndis_number">NDIS Number <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-6 form-floating position-relative">
                    <input type="date" class="form-control" id="dob" name="dob" required min="1900-01-01" max="<?= date('Y-m-d') ?>">
                    <label for="dob">Date of Birth</label>
                </div>
                <div class="col-md-12 form-floating position-relative">
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address">
                    <label for="address">Full Address</label>
                </div>
                <div class="col-md-12 form-floating position-relative">
                    <input type="text" class="form-control" id="emergency" name="emergency" placeholder="Emergency Contact">
                    <label for="emergency">Emergency Contact (Name & Phone)</label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-premium mt-4 mb-3">Complete Registration</button>
            
            <div class="text-center">
                <span class="text-muted small">Already have an account? </span>
                <a href="index.php" class="text-decoration-none small fw-semibold text-primary">Sign In here</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
