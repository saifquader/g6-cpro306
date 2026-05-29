<?php
// dashboard.php
require_once 'config/database.php';
check_auth();
require_once 'includes/header.php';

// Quick stats queries
$participantsCount = 0;
$upcomingShiftsCount = 0;
$pendingReportsCount = 0;
$todaysShifts = [];

try {
    $stmt1 = $pdo->query("SELECT COUNT(*) FROM participants");
    $participantsCount = $stmt1->fetchColumn();
    
    $stmt2 = $pdo->query("SELECT COUNT(*) FROM shifts WHERE status = 'Scheduled'");
    $upcomingShiftsCount = $stmt2->fetchColumn();
    
    $stmt3 = $pdo->query("SELECT COUNT(*) FROM incident_reports WHERE status = 'Reported'");
    $pendingReportsCount = $stmt3->fetchColumn();

    // Get Today's Shifts
    $today = date('Y-m-d');
    $stmt4 = $pdo->prepare("
        SELECT s.*, p.first_name, p.last_name 
        FROM shifts s 
        JOIN participants p ON s.participant_id = p.participant_id 
        WHERE DATE(s.start_time) = ? 
        ORDER BY s.start_time ASC
    ");
    $stmt4->execute([$today]);
    $todaysShifts = $stmt4->fetchAll();
} catch(PDOException $e) {}
?>

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 15px; overflow: hidden;">
            <div class="card-body p-5 position-relative">
                <div style="position: relative; z-index: 2;">
                    <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
                    <p class="mb-0 opacity-75 fs-5">Here's your overview for <?= date('l, j F Y') ?>.</p>
                </div>
                <!-- Abstract decorative element -->
                <i class="fa-solid fa-chart-line position-absolute" style="font-size: 15rem; right: -20px; bottom: -40px; opacity: 0.1; z-index: 1;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 p-4 border-0 shadow-sm" style="border-radius: 15px;">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-users fs-3 text-primary"></i>
                </div>
                <div class="ms-3">
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.8rem;">Active Participants</h6>
                    <h2 class="mb-0 fw-bold"><?= $participantsCount ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-4 border-0 shadow-sm" style="border-radius: 15px;">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-calendar-check fs-3 text-success"></i>
                </div>
                <div class="ms-3">
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.8rem;">Upcoming Shifts</h6>
                    <h2 class="mb-0 fw-bold"><?= $upcomingShiftsCount ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-4 border-0 shadow-sm" style="border-radius: 15px;">
            <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-file-contract fs-3 text-danger"></i>
                </div>
                <div class="ms-3">
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.8rem;">Pending Reports</h6>
                    <h2 class="mb-0 fw-bold"><?= $pendingReportsCount ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Schedule -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Today's Schedule</h5>
                <a href="schedule.php" class="btn btn-sm btn-light fw-semibold text-primary">View Calendar</a>
            </div>
            <div class="card-body">
                <?php if (empty($todaysShifts)): ?>
                    <div class="text-center text-muted py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fa-regular fa-calendar fs-1 text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-semibold">No shifts today</h5>
                        <p>You have a clear schedule for the rest of the day.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush mt-2">
                        <?php foreach($todaysShifts as $shift): 
                            $start = date('g:i A', strtotime($shift['start_time']));
                            $end = date('g:i A', strtotime($shift['end_time']));
                        ?>
                            <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 text-center me-3" style="width: 70px;">
                                        <div class="fw-bold text-primary"><?= $start ?></div>
                                        <div class="small text-muted"><?= $end ?></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($shift['first_name'] . ' ' . $shift['last_name']) ?></h6>
                                        <div class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i> Client Residence</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge rounded-pill bg-<?= $shift['status'] == 'Completed' ? 'success' : 'warning text-dark' ?>"><?= $shift['status'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Grid -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="participants.php" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100 text-center p-3 hover-lift transition-all">
                                <i class="fa-solid fa-user-plus fs-3 text-primary mb-2"></i>
                                <span class="small fw-semibold text-dark">Add Client</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="schedule.php" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100 text-center p-3 hover-lift transition-all">
                                <i class="fa-solid fa-clock fs-3 text-success mb-2"></i>
                                <span class="small fw-semibold text-dark">Schedule Shift</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="compliance.php" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100 text-center p-3 hover-lift transition-all">
                                <i class="fa-solid fa-file-signature fs-3 text-warning mb-2"></i>
                                <span class="small fw-semibold text-dark">Write Note</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="compliance.php" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100 text-center p-3 hover-lift transition-all">
                                <i class="fa-solid fa-triangle-exclamation fs-3 text-danger mb-2"></i>
                                <span class="small fw-semibold text-dark">Incident</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Mini Chart Section -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-3">Weekly Overview</h6>
                    <canvas id="weeklyChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Shifts',
                data: [3, 5, 2, 6, 4, 1, 0], // Dummy data for visual effect
                backgroundColor: 'rgba(67, 97, 238, 0.2)',
                borderColor: 'rgba(67, 97, 238, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, display: false },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<style>
.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
</style>

<?php require_once 'includes/footer.php'; ?>
