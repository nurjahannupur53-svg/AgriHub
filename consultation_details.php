<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/consultation_data.php';

$id = trim($_GET['id'] ?? '');

$consultation = findConsultationById($id);

if (!$consultation) {
    header('Location: consultation.php');
    exit;
}

$pageTitle = 'Consultation Details';
$pageSubtitle =
    $consultation['id'] . ' — ' . $consultation['farmer'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="consultation.php"
        class="btn btn-light"
    >
        ← Back to Consultations
    </a>


    <?php if (
        !in_array(
            $consultation['status'],
            ['Completed', 'Cancelled'],
            true
        )
    ): ?>

        <div class="detail-action-group">

            <a
                href="consultation_update.php?id=<?= urlencode($consultation['id']) ?>"
                class="btn btn-primary"
            >
                Update Consultation
            </a>

            <a
                href="consultation_cancel.php?id=<?= urlencode($consultation['id']) ?>"
                class="btn btn-light"
                data-confirm="Cancel this consultation?"
            >
                Cancel
            </a>

        </div>

    <?php endif; ?>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Consultation Information</h3>
    </div>

    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Consultation ID</span>

                <strong>
                    <?= htmlspecialchars($consultation['id']) ?>
                </strong>
            </div>


            <div class="info-item">

                <span>Farmer</span>

                <a
                    href="farmer_details.php?id=<?= urlencode($consultation['farmer_id']) ?>"
                    class="detail-value-link"
                >
                    <?= htmlspecialchars($consultation['farmer']) ?>
                </a>

            </div>


            <div class="info-item">
                <span>Expert</span>

                <strong>
                    <?= htmlspecialchars($consultation['expert']) ?>
                </strong>
            </div>


            <div class="info-item">
                <span>Date</span>

                <strong>
                    <?= htmlspecialchars($consultation['date']) ?>
                </strong>
            </div>


            <div class="info-item">
                <span>Topic</span>

                <strong>
                    <?= htmlspecialchars($consultation['topic']) ?>
                </strong>
            </div>


            <div class="info-item">

                <span>Status</span>

                <?php
                $statusClass = 'status-warning';

                if ($consultation['status'] === 'Completed') {
                    $statusClass = 'status-success';
                } elseif ($consultation['status'] === 'Cancelled') {
                    $statusClass = 'status-danger';
                }
                ?>

                <span class="status-badge <?= $statusClass ?>">
                    <?= htmlspecialchars($consultation['status']) ?>
                </span>

            </div>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>