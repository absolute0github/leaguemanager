<?php
// Get data from controller
$pending = $pending ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalCount = $totalCount ?? 0;
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Pending Account Registrations</h2>
            <p class="text-muted">User accounts awaiting admin approval</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-danger">
                <?php echo $totalCount; ?> Pending
            </span>
        </div>
    </div>

    <?php if (empty($pending)): ?>
        <div class="alert alert-info">
            <strong>No pending registrations.</strong> All user registrations have been reviewed.
        </div>
    <?php else: ?>
        <!-- Pending Registrations Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Email (Username)</th>
                        <th>Linked Player</th>
                        <th>Age Group</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $registration): ?>
                        <?php
                            $hasLinkedPlayer = !empty($registration['player_id']);
                            $playerName = $hasLinkedPlayer
                                ? trim(($registration['first_name'] ?? '') . ' ' . ($registration['last_name'] ?? ''))
                                : null;
                        ?>
                        <tr>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($registration['email'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($registration['email'] ?? ''); ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($hasLinkedPlayer): ?>
                                    <strong><?php echo htmlspecialchars($playerName); ?></strong>
                                    <span class="badge bg-success ms-1">Linked</span>
                                <?php else: ?>
                                    <span class="text-muted">No player linked</span>
                                    <span class="badge bg-warning text-dark ms-1">New User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hasLinkedPlayer && !empty($registration['age_group'])): ?>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($registration['age_group']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php
                                        $date = new DateTime($registration['registered_at'] ?? 'now');
                                        echo $date->format('M d, Y H:i');
                                    ?>
                                </small>
                            </td>
                            <td>
                                <!-- Approve Modal Trigger -->
                                <button type="button" class="btn btn-sm btn-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#approveModal<?php echo $registration['user_id']; ?>">
                                    Approve
                                </button>

                                <!-- Reject Modal Trigger -->
                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal<?php echo $registration['user_id']; ?>">
                                    Reject
                                </button>
                            </td>
                        </tr>

                        <!-- Approve Modal -->
                        <div class="modal fade" id="approveModal<?php echo $registration['user_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Approve Registration</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Approve account for <strong><?php echo htmlspecialchars($registration['email']); ?></strong>?</p>
                                        <?php if ($hasLinkedPlayer): ?>
                                            <p class="text-muted">This account is linked to player: <?php echo htmlspecialchars($playerName); ?></p>
                                        <?php else: ?>
                                            <p class="text-muted">This is a new user with no linked players. They will be able to add players after approval.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form method="POST" action="/admin/registrations/approve" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($registration['user_id']); ?>">
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal<?php echo $registration['user_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Registration</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="/admin/registrations/reject">
                                        <div class="modal-body">
                                            <p>Reject account for <strong><?php echo htmlspecialchars($registration['email']); ?></strong>?</p>

                                            <div class="mb-3">
                                                <label for="reason<?php echo $registration['user_id']; ?>" class="form-label">
                                                    Reason (optional)
                                                </label>
                                                <textarea class="form-control" id="reason<?php echo $registration['user_id']; ?>"
                                                          name="reason" rows="3"
                                                          placeholder="Explain why this registration is being rejected..."></textarea>
                                            </div>

                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($registration['user_id']); ?>">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/admin/pending-registrations?page=<?php echo max(1, $page - 1); ?>">
                            Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="/admin/pending-registrations?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/admin/pending-registrations?page=<?php echo min($totalPages, $page + 1); ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
