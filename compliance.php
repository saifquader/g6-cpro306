<?php
// compliance.php
require_once 'config/database.php';
check_admin();
require_once 'includes/header.php';

// Fetch Participants and Users for dropdowns
$participants = [];
$notes = [];
$incidents = [];
try {
    $participants = $pdo->query("SELECT participant_id, first_name, last_name FROM participants ORDER BY first_name")->fetchAll();
    
    // Fetch Notes
    $notes = $pdo->query("
        SELECT n.*, p.first_name as p_first, p.last_name as p_last, u.first_name as u_first, u.last_name as u_last 
        FROM progress_notes n
        LEFT JOIN participants p ON n.participant_id = p.participant_id
        LEFT JOIN users u ON n.user_id = u.user_id
        ORDER BY n.created_date DESC
    ")->fetchAll();

    // Fetch Incidents
    $incidents = $pdo->query("
        SELECT i.*, p.first_name as p_first, p.last_name as p_last, u.first_name as u_first, u.last_name as u_last 
        FROM incident_reports i
        LEFT JOIN participants p ON i.participant_id = p.participant_id
        LEFT JOIN users u ON i.user_id = u.user_id
        ORDER BY i.incident_date DESC
    ")->fetchAll();
} catch(PDOException $e) {}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_note') {
        try {
            $stmt = $pdo->prepare("INSERT INTO progress_notes (note_id, participant_id, user_id, created_date, note_text, wellbeing_status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                generate_uuid(),
                $_POST['participant_id'],
                $_SESSION['user_id'],
                date('Y-m-d'),
                $_POST['note_text'],
                $_POST['wellbeing']
            ]);
            redirect('compliance.php');
        } catch(PDOException $e) {}
    } elseif ($_POST['action'] == 'add_incident') {
        try {
            $stmt = $pdo->prepare("INSERT INTO incident_reports (incident_id, participant_id, user_id, incident_date, incident_type, description, severity, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Reported')");
            $stmt->execute([
                generate_uuid(),
                $_POST['participant_id'],
                $_SESSION['user_id'],
                $_POST['incident_date'],
                $_POST['type'],
                $_POST['description'],
                $_POST['severity']
            ]);
            redirect('compliance.php');
        } catch(PDOException $e) {}
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Compliance & Reporting</h2>
    <div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="fa-solid fa-file-pen me-2"></i>New Progress Note
        </button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addIncidentModal">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>Report Incident
        </button>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="complianceTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
        <i class="fa-solid fa-file-lines me-1"></i> Progress Notes
        <span class="badge bg-primary ms-1"><?= count($notes) ?></span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link text-danger" id="incidents-tab" data-bs-toggle="tab" data-bs-target="#incidents" type="button" role="tab">
        <i class="fa-solid fa-triangle-exclamation me-1"></i> Incident Reports
        <span class="badge bg-danger ms-1"><?= count($incidents) ?></span>
    </button>
  </li>
</ul>

<div class="tab-content" id="complianceTabsContent">
  <!-- Progress Notes Tab -->
  <div class="tab-pane fade show active" id="notes" role="tabpanel">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-0">
            <?php if (empty($notes)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-file-lines fa-3x mb-3 opacity-25"></i>
                    <p>No progress notes found. Add one above.</p>
                </div>
            <?php else: ?>
            <div class="accordion accordion-flush" id="notesAccordion">
                <?php foreach($notes as $idx => $n): ?>
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#note-<?= $idx ?>"
                            aria-expanded="false"
                            aria-controls="note-<?= $idx ?>">
                            <div class="d-flex align-items-center w-100 me-3">
                                <div class="me-3">
                                    <?php
                                        $wb = $n['wellbeing_status'];
                                        $wbColor = 'secondary';
                                        if($wb == 'Excellent') $wbColor = 'success';
                                        elseif($wb == 'Good') $wbColor = 'info';
                                        elseif($wb == 'Fair') $wbColor = 'warning';
                                        elseif($wb == 'Poor') $wbColor = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $wbColor ?>"><?= htmlspecialchars($wb) ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars($n['p_first'] . ' ' . $n['p_last']) ?></strong>
                                    <span class="text-muted small ms-2">— <?= htmlspecialchars(substr($n['note_text'], 0, 60)) ?>...</span>
                                </div>
                                <div class="text-muted small text-end ms-auto me-2" style="white-space: nowrap;">
                                    <?= date('j M Y', strtotime($n['created_date'])) ?>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="note-<?= $idx ?>" class="accordion-collapse collapse">
                        <div class="accordion-body bg-light">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Participant</small>
                                    <span class="fw-bold"><?= htmlspecialchars($n['p_first'] . ' ' . $n['p_last']) ?></span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Recorded By</small>
                                    <span><?= htmlspecialchars($n['u_first'] . ' ' . $n['u_last']) ?></span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Date</small>
                                    <span><?= date('l, j F Y', strtotime($n['created_date'])) ?></span>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Full Note</small>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($n['note_text'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
  </div>

  <!-- Incident Reports Tab -->
  <div class="tab-pane fade" id="incidents" role="tabpanel">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-0">
            <?php if (empty($incidents)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-triangle-exclamation fa-3x mb-3 opacity-25"></i>
                    <p>No incident reports found.</p>
                </div>
            <?php else: ?>
            <div class="accordion accordion-flush" id="incidentsAccordion">
                <?php foreach($incidents as $idx => $i): ?>
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#incident-<?= $idx ?>"
                            aria-expanded="false"
                            aria-controls="incident-<?= $idx ?>">
                            <div class="d-flex align-items-center w-100 me-3">
                                <div class="me-3">
                                    <?php
                                        $sevBadge = 'secondary';
                                        if($i['severity'] == 'High' || $i['severity'] == 'Critical') $sevBadge = 'danger';
                                        elseif($i['severity'] == 'Medium') $sevBadge = 'warning text-dark';
                                        elseif($i['severity'] == 'Low') $sevBadge = 'info text-dark';
                                    ?>
                                    <span class="badge bg-<?= $sevBadge ?>"><?= htmlspecialchars($i['severity']) ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars($i['p_first'] . ' ' . $i['p_last']) ?></strong>
                                    <span class="text-muted small ms-2">— <?= htmlspecialchars($i['incident_type']) ?></span>
                                </div>
                                <div class="text-muted small text-end ms-auto me-2" style="white-space: nowrap;">
                                    <?= date('j M Y', strtotime($i['incident_date'])) ?>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="incident-<?= $idx ?>" class="accordion-collapse collapse">
                        <div class="accordion-body bg-light">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Participant</small>
                                    <span class="fw-bold"><?= htmlspecialchars($i['p_first'] . ' ' . $i['p_last']) ?></span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Reported By</small>
                                    <span><?= htmlspecialchars($i['u_first'] . ' ' . $i['u_last']) ?></span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Incident Date</small>
                                    <span><?= date('l, j F Y', strtotime($i['incident_date'])) ?></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Type</small>
                                    <span><?= htmlspecialchars($i['incident_type']) ?></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block">Status</small>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($i['status']) ?></span>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-muted text-uppercase fw-semibold d-block mb-1">Full Description</small>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($i['description'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
  </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="compliance.php">
          <input type="hidden" name="action" value="add_note">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">New Progress Note</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <label class="form-label">Wellbeing Status</label>
                <select class="form-select" name="wellbeing" required>
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Note Details</label>
                <textarea class="form-control" name="note_text" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Note</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Incident Modal -->
<div class="modal fade" id="addIncidentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="compliance.php">
          <input type="hidden" name="action" value="add_incident">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title fw-bold">Report Incident</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Date of Incident</label>
                    <input type="date" class="form-control" name="incident_date" required max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Severity</label>
                    <select class="form-select" name="severity" required>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Incident Type</label>
                <input type="text" class="form-control" name="type" required placeholder="e.g. Fall, Behavioural, Medical">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Submit Report</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
