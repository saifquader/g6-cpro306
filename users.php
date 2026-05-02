<?php
// users.php
require_once 'config/database.php';
check_auth();
require_once 'includes/header.php';

// Only admins should ideally manage users, though we'll keep it simple
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_user') {
    try {
        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (user_id, organisation_id, role_id, first_name, last_name, email, password_hash, phone, status) VALUES (?, 'org-1', ?, ?, ?, ?, ?, ?, 'Active')");
        $stmt->execute([
            generate_uuid(),
            $_POST['role_id'],
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $hash,
            $_POST['phone']
        ]);
        $successMessage = "User added successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error adding user. Email might already exist.";
    }
}

// Fetch users
$users = [];
$roles = [];
try {
    $users = $pdo->query("
        SELECT u.*, r.role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.role_id 
        ORDER BY u.first_name ASC
    ")->fetchAll();
    
    $roles = $pdo->query("SELECT * FROM roles")->fetchAll();
} catch(PDOException $e) {}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Staff Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fa-solid fa-user-plus me-2"></i>New Staff / Admin
    </button>
</div>

<?php if($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $successMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errorMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No users found.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $u['role_id'] == 'role-admin' ? 'primary' : 'info' ?>">
                                    <?= htmlspecialchars($u['role_name'] ?? 'Unknown') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone']) ?></td>
                            <td>
                                <span class="badge bg-<?= $u['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                    <?= htmlspecialchars($u['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="users.php">
          <input type="hidden" name="action" value="add_user">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Add New Staff / Admin</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="first_name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="last_name" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone">
                </div>
                <div class="col-12">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role_id" required>
                        <?php foreach($roles as $r): ?>
                            <option value="<?= $r['role_id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save User</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
