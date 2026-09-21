<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/farmer_data.php';

$pageTitle = 'Farmers';
$pageSubtitle = '6 registered farmers';

$success = $_GET['success'] ?? '';

$farmers = getAllFarmers();

require_once __DIR__ . '/includes/header.php';

?>
<?php if ($success === 'added'): ?>
    <div class="flash-message flash-success">
        Farmer added successfully.
    </div>
<?php endif; ?>

<?php if ($success === 'updated'): ?>
    <div class="flash-message flash-success">
        Farmer updated successfully.
    </div>
<?php endif; ?>

<?php if ($success === 'deleted'): ?>
    <div class="flash-message flash-success">
        Farmer deleted successfully.
    </div>
<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">
        <span>⌕</span>

        <input
            type="text"
            id="farmerSearch"
            placeholder="Search by name or ID"
        >
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="addFarmerModal"
    >
        + Add Farmer
    </button>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table class="app-table" id="farmersTable">

            <thead>
            <tr>
                <th>Farmer ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Total Land</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>

            <?php foreach ($farmers as $farmer): ?>

                <tr>

                    <td>
                        <strong>
                            <?= htmlspecialchars($farmer['id']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($farmer['name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($farmer['phone']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($farmer['location']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($farmer['land']) ?>
                    </td>

                    <td>
                        <?php if ($farmer['status'] === 'Active'): ?>

                            <span class="status-badge status-success">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="status-badge status-danger">
                                Inactive
                            </span>

                        <?php endif; ?>
                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="farmer_details.php?id=<?= urlencode($farmer['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>

                            <a
                                href="farmer_edit.php?id=<?= urlencode($farmer['id']) ?>"
                                class="action-link edit"
                            >
                                Edit
                            </a>

                            <a
                                href="farmer_delete.php?id=<?= urlencode($farmer['id']) ?>"
                                class="action-link delete"
                                data-confirm="Are you sure you want to delete <?= htmlspecialchars($farmer['name'], ENT_QUOTES) ?>?"
                            >
                                Delete
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =====================================================
     ADD FARMER MODAL
===================================================== -->

<div class="modal-overlay" id="addFarmerModal">

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Add Farmer</h2>
                <p>Register a new farmer in AgriHub.</p>
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
            action="farmer_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="app-form-group">

                <label for="farmerName">
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="farmerName"
                    class="app-input"
                    placeholder="Enter farmer name"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label for="farmerPhone">
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        id="farmerPhone"
                        class="app-input"
                        placeholder="01XXXXXXXXX"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label for="farmerLocation">
                        Location
                    </label>

                    <input
                        type="text"
                        name="location"
                        id="farmerLocation"
                        class="app-input"
                        placeholder="District"
                        required
                    >

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label for="farmerLand">
                        Total Land (acres)
                    </label>

                    <input
                        type="number"
                        name="land"
                        id="farmerLand"
                        class="app-input"
                        min="0"
                        step="0.1"
                        placeholder="0.0"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label for="farmerEmail">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="farmerEmail"
                        class="app-input"
                        placeholder="farmer@example.com"
                    >

                </div>

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
                    Save Farmer
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput =
        document.getElementById("farmerSearch");

    const rows =
        document.querySelectorAll("#farmersTable tbody tr");

    if (searchInput) {

        searchInput.addEventListener("input", function () {

            const search =
                this.value.toLowerCase().trim();

            rows.forEach(function (row) {

                const text =
                    row.innerText.toLowerCase();

                row.style.display =
                    text.includes(search) ? "" : "none";

            });

        });

    }

});
</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>