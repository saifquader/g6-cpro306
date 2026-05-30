<?php
// participant_dashboard.php
require_once 'config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Ensure this is a participant
if ($_SESSION['role_id'] !== 'role-participant') {
    redirect('dashboard.php'); // Admins go to their dashboard
}

require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$participant = null;
$upcomingShifts = [];

try {
    // Get participant details
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $participant = $stmt->fetch();

    if ($participant) {
        $p_id = $participant['participant_id'];

        // Get Upcoming Shifts for this participant
        $stmt2 = $pdo->prepare("
            SELECT s.*, u.first_name as worker_first, u.last_name as worker_last 
            FROM shifts s 
            LEFT JOIN users u ON s.user_id = u.user_id 
            WHERE s.participant_id = ? AND s.status = 'Scheduled' AND s.shift_start >= NOW()
            ORDER BY s.shift_start ASC LIMIT 5
        ");
        $stmt2->execute([$p_id]);
        $upcomingShifts = $stmt2->fetchAll();
    }

} catch(PDOException $e) {}
?>

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 text-white" style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color)); border-radius: 15px; overflow: hidden;">
            <div class="card-body p-5 position-relative">
                <div style="position: relative; z-index: 2;">
                    <h2 class="fw-bold mb-1">Welcome to your Portal, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
                    <p class="mb-0 opacity-75 fs-5">Here's your care schedule and profile overview.</p>
                </div>
                <!-- Abstract decorative element -->
                <i class="fa-solid fa-hands-holding-child position-absolute" style="font-size: 15rem; right: -20px; bottom: -40px; opacity: 0.1; z-index: 1;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Upcoming Shifts -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Your Upcoming Support Shifts</h5>
            </div>
            <div class="card-body">
                <?php if (!$participant): ?>
                    <div class="alert alert-warning">Your participant profile is currently being set up. Please contact support.</div>
                <?php elseif (empty($upcomingShifts)): ?>
                    <div class="text-center text-muted py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fa-regular fa-calendar-check fs-1 text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-semibold">No upcoming shifts</h5>
                        <p>You have no shifts scheduled at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush mt-2">
                        <?php foreach($upcomingShifts as $shift): 
                            $date = date('l, j M', strtotime($shift['shift_start']));
                            $start = date('g:i A', strtotime($shift['shift_start']));
                            $end = date('g:i A', strtotime($shift['shift_end']));
                        ?>
                            <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 text-center me-4" style="width: 90px;">
                                        <div class="fw-bold text-primary small text-uppercase"><?= $date ?></div>
                                        <div class="fw-bold text-dark"><?= $start ?></div>
                                        <div class="small text-muted"><?= $end ?></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">Support Session</h6>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-user-nurse me-1"></i> 
                                            Worker: <?= $shift['worker_first'] ? htmlspecialchars($shift['worker_first'] . ' ' . $shift['worker_last']) : 'Pending Assignment' ?>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge rounded-pill bg-success">Scheduled</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Profile Summary -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">My Details</h5>
            </div>
            <div class="card-body">
                <?php if ($participant): ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 border-0 mb-2">
                            <small class="text-muted text-uppercase fw-semibold d-block">NDIS Number</small>
                            <span class="fw-bold"><?= htmlspecialchars($participant['ndis_number']) ?></span>
                        </li>
                        <li class="list-group-item px-0 border-0 mb-2">
                            <small class="text-muted text-uppercase fw-semibold d-block">Date of Birth</small>
                            <span><?= date('j M Y', strtotime($participant['date_of_birth'])) ?></span>
                        </li>
                        <li class="list-group-item px-0 border-0 mb-2">
                            <small class="text-muted text-uppercase fw-semibold d-block">Emergency Contact</small>
                            <span><?= htmlspecialchars($participant['emergency_contact']) ?></span>
                        </li>
                    </ul>
                    <div class="mt-4 pt-3 border-top text-center">
                        <p class="small text-muted mb-2">Need to update your details?</p>
                        <button class="btn btn-outline-primary btn-sm" onclick="alert('Contacting support is pending implementation.')">Contact Support</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
