<?php
// participants.php
require_once 'config/database.php';
check_auth();
require_once 'includes/header.php';

// Handle Add Participant (Dummy action for layout)
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO participants (participant_id, organisation_id, first_name, last_name, ndis_number, date_of_birth, address, emergency_contact, support_needs) VALUES (?, 'org-1', ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            generate_uuid(),
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['ndis_number'],
            $_POST['dob'],
            $_POST['address'],
            $_POST['emergency'],
            $_POST['needs']
        ]);
        $successMessage = "Participant added successfully!";
    } catch(PDOException $e) {
        // Ignoring error handling for simplicity in prototype
    }
}

// Fetch participants
$participants = [];
try {
    $stmt = $pdo->query("SELECT * FROM participants ORDER BY last_name ASC");
    $participants = $stmt->fetchAll();
} catch(PDOException $e) {}

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Client Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
        <i class="fa-solid fa-plus me-2"></i>New Participant
    </button>
</div>

<?php if($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $successMessage ?>
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
                        <th>NDIS Number</th>
                        <th>Date of Birth</th>
                        <th>Emergency Contact</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participants)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No participants found. Add a participant to get started.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($participants as $p): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($p['first_name'],0,1) . substr($p['last_name'],0,1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($p['ndis_number']) ?></td>
                            <td><?= htmlspecialchars($p['date_of_birth']) ?></td>
                            <td><?= htmlspecialchars($p['emergency_contact']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></button>
                                <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Participant Modal -->
<div class="modal fade" id="addParticipantModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="participants.php">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Add New Participant</h5>
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
                <div class="col-md-6">
                    <label class="form-label">NDIS Number</label>
                    <input type="text" class="form-control" name="ndis_number" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="dob" required min="1900-01-01" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="address" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Emergency Contact (Name & Phone)</label>
                    <input type="text" class="form-control" name="emergency" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Support Needs / Notes</label>
                    <textarea class="form-control" name="needs" rows="3" required></textarea>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Participant</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
