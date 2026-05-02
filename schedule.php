<?php
// schedule.php
require_once 'config/database.php';
check_auth();
require_once 'includes/header.php';

// Fetch Participants and Users for dropdowns
$participants = [];
$workers = [];
$shifts = [];
try {
    $participants = $pdo->query("SELECT participant_id, first_name, last_name FROM participants ORDER BY first_name")->fetchAll();
    $workers = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE status='Active'")->fetchAll();
    
    // Fetch Shifts
    $shifts = $pdo->query("
        SELECT s.*, p.first_name as p_first, p.last_name as p_last, u.first_name as u_first, u.last_name as u_last 
        FROM shifts s
        LEFT JOIN participants p ON s.participant_id = p.participant_id
        LEFT JOIN users u ON s.user_id = u.user_id
        ORDER BY s.shift_start DESC
    ")->fetchAll();
} catch(PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_shift') {
    try {
        $stmt = $pdo->prepare("INSERT INTO shifts (shift_id, participant_id, user_id, shift_start, shift_end, location, status) VALUES (?, ?, ?, ?, ?, ?, 'Scheduled')");
        $stmt->execute([
            generate_uuid(),
            $_POST['participant_id'],
            $_POST['user_id'],
            $_POST['start_time'],
            $_POST['end_time'],
            $_POST['location']
        ]);
        redirect('schedule.php');
    } catch(PDOException $e) {}
}

// Prepare JSON events for FullCalendar
$calendarEvents = [];
foreach ($shifts as $s) {
    $color = '#ffc107'; // Scheduled (yellow)
    if ($s['status'] == 'Completed') $color = '#198754'; // Green
    if ($s['status'] == 'Cancelled') $color = '#dc3545'; // Red

    $calendarEvents[] = [
        'id' => $s['shift_id'],
        'title' => $s['p_first'] . ' ' . $s['p_last'] . ' (Worker: ' . $s['u_first'] . ')',
        'start' => $s['shift_start'],
        'end' => $s['shift_end'],
        'backgroundColor' => $color,
        'borderColor' => $color,
        'extendedProps' => [
            'location' => $s['location'],
            'status' => $s['status']
        ]
    ];
}
?>

<!-- FullCalendar CSS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Service Scheduling</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
        <i class="fa-solid fa-calendar-plus me-2"></i>Schedule Shift
    </button>
</div>

<ul class="nav nav-pills mb-3" id="scheduleTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="calendar-tab" data-bs-toggle="pill" data-bs-target="#calendar-view" type="button" role="tab">Visual Calendar</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="list-tab" data-bs-toggle="pill" data-bs-target="#list-view" type="button" role="tab">List View</button>
  </li>
</ul>

<div class="tab-content" id="scheduleTabsContent">
    <!-- Calendar View Tab -->
    <div class="tab-pane fade show active" id="calendar-view" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- List View Tab -->
    <div class="tab-pane fade" id="list-view" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Participant</th>
                                <th>Assigned Worker</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($shifts)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No shifts scheduled.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($shifts as $s): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($s['p_first'] . ' ' . $s['p_last']) ?></td>
                                    <td><?= htmlspecialchars($s['u_first'] . ' ' . $s['u_last']) ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($s['shift_start'])) ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($s['shift_end'])) ?></td>
                                    <td><?= htmlspecialchars($s['location']) ?></td>
                                    <td>
                                        <span class="badge rounded-pill badge-status-<?= strtolower($s['status']) ?>"><?= htmlspecialchars($s['status']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Shift Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="schedule.php">
          <input type="hidden" name="action" value="add_shift">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Schedule New Shift</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Participant</label>
                <select class="form-select" name="participant_id" required>
                    <option value="">Select Participant...</option>
                    <?php foreach($participants as $p): ?>
                        <option value="<?= $p['participant_id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Support Worker</label>
                <select class="form-select" name="user_id" required>
                    <option value="">Select Worker...</option>
                    <?php foreach($workers as $w): ?>
                        <option value="<?= $w['user_id'] ?>"><?= htmlspecialchars($w['first_name'] . ' ' . $w['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Start Time</label>
                    <input type="datetime-local" class="form-control" name="start_time" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Time</label>
                    <input type="datetime-local" class="form-control" name="end_time" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" required placeholder="123 Example St">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Shift</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = <?= json_encode($calendarEvents) ?>;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventClick: function(info) {
            alert('Shift: ' + info.event.title + '\nLocation: ' + info.event.extendedProps.location + '\nStatus: ' + info.event.extendedProps.status);
        }
    });

    calendar.render();

    // Re-render calendar when switching tabs to avoid size issues
    var calendarTab = document.getElementById('calendar-tab')
    calendarTab.addEventListener('shown.bs.tab', function (event) {
        calendar.render();
    })
});
</script>

<?php require_once 'includes/footer.php'; ?>
