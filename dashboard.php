<?php
// dashboard.php
require_once 'config/database.php';
check_auth();
require_once 'includes/header.php';

// Quick stats queries (using try-catch just in case tables are empty/missing)
$participantsCount = 0;
$upcomingShiftsCount = 0;
$pendingReportsCount = 0;

try {
    $stmt1 = $pdo->query("SELECT COUNT(*) FROM participants");
    $participantsCount = $stmt1->fetchColumn();
    
    $stmt2 = $pdo->query("SELECT COUNT(*) FROM shifts WHERE status = 'Scheduled'");
    $upcomingShiftsCount = $stmt2->fetchColumn();
    
    $stmt3 = $pdo->query("SELECT COUNT(*) FROM incident_reports WHERE status = 'Reported'");
    $pendingReportsCount = $stmt3->fetchColumn();
} catch(PDOException $e) {
    // Ignore DB errors on stats for now
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Welcome back, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
        <p class="text-muted">Here's what's happening at GridLink today.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Stat Card 1 -->
    <div class="col-md-4">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-users stat-icon"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1">Active Participants</h6>
                    <h3 class="mb-0 fw-bold"><?= $participantsCount ?></h3>
                </div>
            </div>
        </div>
    </div>
    <!-- Stat Card 2 -->
    <div class="col-md-4">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-calendar-check stat-icon text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1">Upcoming Shifts</h6>
                    <h3 class="mb-0 fw-bold"><?= $upcomingShiftsCount ?></h3>
                </div>
            </div>
        </div>
    </div>
    <!-- Stat Card 3 -->
    <div class="col-md-4">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-file-contract stat-icon text-danger"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1">Pending Incident Reports</h6>
                    <h3 class="mb-0 fw-bold"><?= $pendingReportsCount ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Today's Schedule</h5>
                <a href="schedule.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-5">
                    <i class="fa-regular fa-calendar fa-3x mb-3"></i>
                    <p>No shifts scheduled for today.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="users.php" class="btn btn-light border text-start"><i class="fa-solid fa-user-plus text-info me-2"></i> Add Staff / Admin</a>
                <a href="participants.php" class="btn btn-light border text-start"><i class="fa-solid fa-plus text-primary me-2"></i> Add Participant</a>
                <a href="schedule.php" class="btn btn-light border text-start"><i class="fa-solid fa-clock text-success me-2"></i> Schedule Shift</a>
                <a href="compliance.php" class="btn btn-light border text-start"><i class="fa-solid fa-file-signature text-warning me-2"></i> Write Progress Note</a>
                <a href="compliance.php" class="btn btn-light border text-start"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Report Incident</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
