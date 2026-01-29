<?php
$team = $team ?? [];
$parentEmails = $parentEmails ?? [];
$playerEmails = $playerEmails ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-envelope me-2"></i>Send Team Message</h2>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($team['name'] ?? 'Unknown Team'); ?></p>
        </div>
        <a href="/coach/roster?id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Roster
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Message Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Compose Message</h5>
                </div>
                <div class="card-body">
                    <form action="/coach/send-message" method="POST" id="messageForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="team_id" value="<?php echo $team['id'] ?? 0; ?>">

                        <!-- Recipients -->
                        <div class="mb-3">
                            <label class="form-label"><strong>Send To</strong></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="recipients" id="recipientsParents" value="parents" checked>
                                <label class="btn btn-outline-primary" for="recipientsParents">
                                    <i class="fas fa-user-friends me-1"></i> Parents Only
                                    <span class="badge bg-light text-dark ms-1"><?php echo count($parentEmails); ?></span>
                                </label>

                                <input type="radio" class="btn-check" name="recipients" id="recipientsPlayers" value="players">
                                <label class="btn btn-outline-primary" for="recipientsPlayers">
                                    <i class="fas fa-running me-1"></i> Players Only
                                    <span class="badge bg-light text-dark ms-1"><?php echo count($playerEmails); ?></span>
                                </label>

                                <input type="radio" class="btn-check" name="recipients" id="recipientsBoth" value="both">
                                <label class="btn btn-outline-primary" for="recipientsBoth">
                                    <i class="fas fa-users me-1"></i> Both
                                    <span class="badge bg-light text-dark ms-1"><?php echo count($parentEmails) + count($playerEmails); ?></span>
                                </label>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label"><strong>Subject</strong></label>
                            <input type="text" class="form-control" id="subject" name="subject"
                                   placeholder="Enter email subject..." required maxlength="200">
                        </div>

                        <!-- Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label"><strong>Message</strong></label>
                            <textarea class="form-control" id="message" name="message" rows="10"
                                      placeholder="Type your message here..." required></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Your message will be sent from the league email address. Your name will be included as the sender.
                            </div>
                        </div>

                        <!-- Quick Templates -->
                        <div class="mb-3">
                            <label class="form-label"><strong>Quick Templates</strong></label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                        data-subject="Practice Reminder"
                                        data-message="Hi Team,\n\nThis is a reminder about our upcoming practice.\n\nDate: [DATE]\nTime: [TIME]\nLocation: [LOCATION]\n\nPlease arrive 15 minutes early for warm-ups.\n\nSee you there!">
                                    <i class="fas fa-clock me-1"></i> Practice Reminder
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                        data-subject="Game Day Information"
                                        data-message="Hi Team,\n\nHere are the details for our upcoming game:\n\nOpponent: [OPPONENT]\nDate: [DATE]\nTime: [TIME]\nLocation: [LOCATION]\n\nPlayers should arrive by [ARRIVAL TIME] in full uniform.\n\nGo team!">
                                    <i class="fas fa-baseball me-1"></i> Game Day
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                        data-subject="Schedule Change"
                                        data-message="Hi Team,\n\nPlease note the following schedule change:\n\n[DESCRIBE CHANGE]\n\nWe apologize for any inconvenience.\n\nPlease confirm you received this message.">
                                    <i class="fas fa-calendar-alt me-1"></i> Schedule Change
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                        data-subject="Team Announcement"
                                        data-message="Hi Team,\n\n[YOUR ANNOUNCEMENT HERE]\n\nPlease let me know if you have any questions.\n\nThank you!">
                                    <i class="fas fa-bullhorn me-1"></i> Announcement
                                </button>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Send Message
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="previewBtn">
                                <i class="fas fa-eye me-1"></i> Preview
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Recipients Preview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent Recipients</h6>
                </div>
                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                    <?php if (empty($parentEmails)): ?>
                        <p class="text-muted mb-0">No parent emails available.</p>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($parentEmails as $parent): ?>
                                <li class="mb-1">
                                    <small>
                                        <strong><?php echo htmlspecialchars($parent['full_name']); ?></strong><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($parent['email']); ?></span>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-running me-2"></i>Player Recipients</h6>
                </div>
                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                    <?php if (empty($playerEmails)): ?>
                        <p class="text-muted mb-0">No player emails available.</p>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($playerEmails as $player): ?>
                                <li class="mb-1">
                                    <small>
                                        <strong><?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?></strong><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($player['email']); ?></span>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Message Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>To:</strong> <span id="previewRecipients"></span>
                </div>
                <div class="mb-3">
                    <strong>Subject:</strong> <span id="previewSubject"></span>
                </div>
                <hr>
                <div id="previewMessage" style="white-space: pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="sendFromPreview">
                    <i class="fas fa-paper-plane me-1"></i> Send Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Template buttons
document.querySelectorAll('.template-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('subject').value = this.dataset.subject;
        document.getElementById('message').value = this.dataset.message.replace(/\\n/g, '\n');
    });
});

// Preview functionality
document.getElementById('previewBtn')?.addEventListener('click', function() {
    const recipients = document.querySelector('input[name="recipients"]:checked');
    const recipientLabels = {
        'parents': 'Parents Only',
        'players': 'Players Only',
        'both': 'Parents and Players'
    };

    document.getElementById('previewRecipients').textContent = recipientLabels[recipients.value] || 'Unknown';
    document.getElementById('previewSubject').textContent = document.getElementById('subject').value || '(No subject)';
    document.getElementById('previewMessage').textContent = document.getElementById('message').value || '(No message)';

    new bootstrap.Modal(document.getElementById('previewModal')).show();
});

// Send from preview
document.getElementById('sendFromPreview')?.addEventListener('click', function() {
    document.getElementById('messageForm').submit();
});
</script>
