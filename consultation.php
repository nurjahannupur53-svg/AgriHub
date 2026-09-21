<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/consultation_data.php';
require_once __DIR__ . '/includes/farmer_data.php';

$consultations = getAllConsultations();
$farmers = getAllFarmers();

$success = trim($_GET['success'] ?? '');

$pageTitle = 'Consultation';
$pageSubtitle = count($consultations) . ' farmer consultations';

require_once __DIR__ . '/includes/header.php';

?>

<?php if ($success === 'added'): ?>

    <div class="flash-message flash-success">
        Consultation scheduled successfully.
    </div>

<?php elseif ($success === 'updated'): ?>

    <div class="flash-message flash-success">
        Consultation updated successfully.
    </div>

<?php elseif ($success === 'cancelled'): ?>

    <div class="flash-message flash-success">
        Consultation cancelled successfully.
    </div>

<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="consultationSearch"
            placeholder="Search farmer, expert or topic"
        >

    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="consultationModal"
    >
        + New Consultation
    </button>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table
            class="app-table"
            id="consultationTable"
        >

            <thead>

            <tr>
                <th>ID</th>
                <th>Farmer</th>
                <th>Expert</th>
                <th>Topic</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            </thead>

            <tbody>

            <?php foreach ($consultations as $consultation): ?>

                <tr>

                    <td>
                        <strong>
                            <?= htmlspecialchars($consultation['id']) ?>
                        </strong>
                    </td>

                    <td>

                        <a
                            href="farmer_details.php?id=<?= urlencode($consultation['farmer_id']) ?>"
                            class="table-id-link"
                        >
                            <?= htmlspecialchars($consultation['farmer']) ?>
                        </a>

                    </td>

                    <td>
                        <?= htmlspecialchars($consultation['expert']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($consultation['topic']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($consultation['date']) ?>
                    </td>

                    <td>

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

                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="consultation_details.php?id=<?= urlencode($consultation['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>

                            <?php if (
                                !in_array(
                                    $consultation['status'],
                                    ['Completed', 'Cancelled'],
                                    true
                                )
                            ): ?>

                                <a
                                    href="consultation_update.php?id=<?= urlencode($consultation['id']) ?>"
                                    class="action-link edit"
                                >
                                    Update
                                </a>

                                <a
                                    href="consultation_cancel.php?id=<?= urlencode($consultation['id']) ?>"
                                    class="action-link delete"
                                    data-confirm="Cancel consultation <?= htmlspecialchars($consultation['id'], ENT_QUOTES) ?>?"
                                >
                                    Cancel
                                </a>

                            <?php endif; ?>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- NEW CONSULTATION MODAL -->

<div
    class="modal-overlay"
    id="consultationModal"
>

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Schedule Consultation</h2>
                <p>Schedule an agricultural consultation for a farmer.</p>
            </div>

            <button
                type="button"
                class="modal-close"
                data-modal-close
            >
                ×
            </button>

        </div>


        <form
            action="consultation_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="app-form-group">

                <label>Farmer</label>

                <select
                    name="farmer_id"
                    class="app-select"
                    required
                >

                    <option value="">Select farmer</option>

                    <?php foreach ($farmers as $farmer): ?>

                        <option value="<?= htmlspecialchars($farmer['id']) ?>">
                            <?= htmlspecialchars($farmer['id']) ?>
                            —
                            <?= htmlspecialchars($farmer['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="app-form-group">

                <label>Agricultural Expert</label>

                <select
                    name="expert"
                    class="app-select"
                    required
                >
                    <option value="">Select expert</option>
                    <option>Dr. Farid Ahmed</option>
                    <option>Agr. Nasima Khanom</option>
                    <option>Dr. Kamal Hasan</option>
                    <option>Dr. Mizanur Rahman</option>
                </select>

            </div>


            <div class="app-form-group">

                <label>Consultation Topic</label>

                <input
                    type="text"
                    name="topic"
                    class="app-input"
                    placeholder="e.g. Crop disease management"
                    required
                >

            </div>


            <div class="app-form-group">

                <label>Consultation Date</label>

                <input
                    type="date"
                    name="date"
                    class="app-input"
                    required
                >

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-modal-close
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Schedule Consultation
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("consultationSearch");

    const rows =
        document.querySelectorAll(
            "#consultationTable tbody tr"
        );

    if (!input) {
        return;
    }

    input.addEventListener("input", function () {

        const search =
            input.value.toLowerCase().trim();

        rows.forEach(function (row) {

            const text =
                row.innerText.toLowerCase();

            row.style.display =
                text.includes(search)
                    ? ""
                    : "none";

        });

    });

});
</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>