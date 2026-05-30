<?php
// messages.php
require_once 'config/database.php';
check_admin();
require_once 'includes/header.php';

$successMessage = '';

// Handle Sending Message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send_message') {
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (message_id, sender_id, participant_id, subject, body, sent_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            generate_uuid(),
            $_SESSION['user_id'],
            $_POST['participant_id'],
            $_POST['subject'],
            $_POST['body'],
            date('Y-m-d H:i:s')
        ]);
        $successMessage = "Message logged and sent successfully!";
    } catch(PDOException $e) {
        // Ignoring error handling for simplicity
    }
}

// Fetch Participants for dropdown
$participants = [];
try {
    $participants = $pdo->query("SELECT participant_id, first_name, last_name FROM participants ORDER BY first_name")->fetchAll();
} catch(PDOException $e) {}

// Fetch Messages
$messages = [];
try {
    $messages = $pdo->query("
        SELECT m.*, p.first_name as p_first, p.last_name as p_last, u.first_name as u_first, u.last_name as u_last 
        FROM messages m
        LEFT JOIN participants p ON m.participant_id = p.participant_id
        LEFT JOIN users u ON m.sender_id = u.user_id
        ORDER BY m.sent_at DESC
    ")->fetchAll();
} catch(PDOException $e) {}

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Client Communications</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
        <i class="fa-solid fa-paper-plane me-2"></i>New Message
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
                        <th>Date Sent</th>
                        <th>Sent To (Client)</th>
                        <th>Sent By (Staff)</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No messages sent yet.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($messages as $m): ?>
                        <tr>
                            <td><?= date('M d, Y H:i', strtotime($m['sent_at'])) ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($m['p_first'] . ' ' . $m['p_last']) ?></td>
                            <td><?= htmlspecialchars($m['u_first'] . ' ' . $m['u_last']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($m['subject']) ?></td>
                            <td><span class="badge bg-success"><i class="fa-solid fa-check me-1"></i><?= htmlspecialchars($m['status']) ?></span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" onclick="alert('Message: <?= htmlspecialchars(addslashes($m['body'])) ?>')">View</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div class="modal fade" id="newMessageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="messages.php">
          <input type="hidden" name="action" value="send_message">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Send Message to Client</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Recipient (Participant)</label>
                <select class="form-select" name="participant_id" required>
                    <option value="">Select Participant...</option>
                    <?php foreach($participants as $p): ?>
                        <option value="<?= $p['participant_id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Since clients do not have an app portal yet, logging a message here records an external communication (e.g. Email/SMS sent).</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" class="form-control" name="subject" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Message Body</label>
                <textarea class="form-control" name="body" rows="5" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-2"></i>Send Message</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
