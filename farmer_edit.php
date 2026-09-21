<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/farmer_data.php';

$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

$farmer = findFarmerById($id);

if (!$farmer) {
    header('Location: farmers.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Save edit
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $land = trim($_POST['land'] ?? '');
    $nid = trim($_POST['nid'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');

    if (
        $name === '' ||
        $phone === '' ||
        $location === '' ||
        $land === ''
    ) {
        $error = 'Please complete all required fields.';
    } else {

        try {

    require_once __DIR__ . '/config/db.php';

    $databaseStatus =
        strtolower($status);

    $stmt = $pdo->prepare("
        UPDATE farmers
        SET
            name = ?,
            phone = ?,
            email = ?,
            location = ?,
            land = ?,
            nid = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $name,
        $phone,
        $email !== '' ? $email : null,
        $location,
        (float) $land,
        $nid !== '' ? $nid : null,
        $databaseStatus,
        $id
    ]);

    header(
        'Location: farmer_details.php?id=' .
        urlencode($id) .
        '&updated=1'
    );

    exit;

} catch (PDOException $e) {

    $error =
        'Unable to update farmer. Email or NID may already be in use.';
}
    }
}


$landValue = preg_replace(
    '/[^0-9.]/',
    '',
    $farmer['land']
);


$pageTitle = 'Edit Farmer';
$pageSubtitle = $farmer['id'] . ' — ' . $farmer['name'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="farmer_details.php?id=<?= urlencode($farmer['id']) ?>"
        class="btn btn-light"
    >
        ← Back
    </a>

</div>


<?php if (!empty($error)): ?>

    <div class="flash-message flash-error">
        <?= htmlspecialchars($error) ?>
    </div>

<?php endif; ?>


<div class="app-card edit-form-card">

    <div class="card-header">
        <h3>Farmer Information</h3>
    </div>

    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($farmer['id']) ?>"
            >


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Full Name *</label>

                    <input
                        class="app-input"
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars($farmer['name']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Phone *</label>

                    <input
                        class="app-input"
                        type="text"
                        name="phone"
                        value="<?= htmlspecialchars($farmer['phone']) ?>"
                        required
                    >

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Email</label>

                    <input
                        class="app-input"
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($farmer['email'] ?? '') ?>"
                    >

                </div>


                <div class="app-form-group">

                    <label>Location *</label>

                    <input
                        class="app-input"
                        type="text"
                        name="location"
                        value="<?= htmlspecialchars($farmer['location']) ?>"
                        required
                    >

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Total Land (acres) *</label>

                    <input
                        class="app-input"
                        type="number"
                        step="0.1"
                        min="0"
                        name="land"
                        value="<?= htmlspecialchars($landValue) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>NID</label>

                    <input
                        class="app-input"
                        type="text"
                        name="nid"
                        value="<?= htmlspecialchars($farmer['nid'] ?? '') ?>"
                    >

                </div>

            </div>


            <div class="app-form-group">

                <label>Status</label>

                <select
                    class="app-select"
                    name="status"
                >

                    <option
                        value="Active"
                        <?= $farmer['status'] === 'Active' ? 'selected' : '' ?>
                    >
                        Active
                    </option>

                    <option
                        value="Inactive"
                        <?= $farmer['status'] === 'Inactive' ? 'selected' : '' ?>
                    >
                        Inactive
                    </option>

                </select>

            </div>


            <div class="form-actions">

                <a
                    href="farmer_details.php?id=<?= urlencode($farmer['id']) ?>"
                    class="btn btn-light"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>