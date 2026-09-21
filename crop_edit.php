<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/crop_data.php';
require_once __DIR__ . '/includes/farmer_data.php';

$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

if ($id === '') {
    header('Location: crops.php');
    exit;
}

$crop = findCropById($id);

if (!$crop) {
    header('Location: crops.php');
    exit;
}

$error = '';

$types = [
    'Grain'      => 'Grain / Cereal',
    'Vegetable'  => 'Vegetable',
    'Fruit'      => 'Fruit',
    'Legume'     => 'Pulse / Legume',
    'Oilseed'    => 'Oilseed',
    'Fiber'      => 'Fiber',
    'Spice'      => 'Spice',
    'Tuber'      => 'Tuber / Root Crop',
    'Cash Crop'  => 'Cash Crop',
    'Fodder'     => 'Fodder / Feed Crop',
    'Plantation' => 'Plantation Crop',
    'Other'      => 'Other'
];

$statuses = [
    'Growing',
    'Pending',
    'Harvested'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $farmerId = trim($_POST['farmer_id'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $plantingDate = trim($_POST['planting_date'] ?? '');
    $harvestDate = trim($_POST['harvest_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $farmer = findFarmerById($farmerId);

    if (
        $name === '' ||
        $type === '' ||
        !$farmer ||
        $area === '' ||
        !is_numeric($area) ||
        (float) $area <= 0 ||
        $plantingDate === '' ||
        $harvestDate === '' ||
        $status === ''
    ) {
        $error = 'Please complete all required fields correctly.';
    } elseif (!array_key_exists($type, $types)) {

        $error = 'Please select a valid crop type.';

    } elseif (!in_array($status, $statuses, true)) {

        $error = 'Please select a valid crop status.';

    } elseif ($harvestDate < $plantingDate) {

        $error = 'Harvest date cannot be earlier than planting date.';

    } else {

        try {

            $stmt = $pdo->prepare("
                UPDATE crops
                SET
                    name = ?,
                    category = ?,
                    farmer_id = ?,
                    land = ?,
                    planting_date = ?,
                    harvest_date = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $name,
                $type,
                $farmerId,
                (float) $area,
                $plantingDate,
                $harvestDate,
                $status,
                $id
            ]);

            header(
                'Location: crop_details.php?id=' .
                urlencode($id) .
                '&updated=1'
            );
            exit;

        } catch (PDOException $e) {

            $error = 'Unable to update crop. Please try again.';

        }
    }

    /*
     * যদি validation fail করে,
     * user যে values লিখেছে সেগুলো form-এ রেখে দিই।
     */
    $crop['name'] = $name;
    $crop['type'] = $type;
    $crop['farmer_id'] = $farmerId;
    $crop['area'] = $area . ' acres';
    $crop['planting_date'] = $plantingDate;
    $crop['harvest_date'] = $harvestDate;
    $crop['status'] = $status;
}

$farmers = getAllFarmers();

$areaValue = preg_replace(
    '/[^0-9.]/',
    '',
    $crop['area']
);

$pageTitle = 'Edit Crop';
$pageSubtitle = $crop['id'] . ' — ' . $crop['name'];

require_once __DIR__ . '/includes/header.php';

?>


<div class="details-top-actions">

    <a
        href="crop_details.php?id=<?= urlencode($crop['id']) ?>"
        class="btn btn-light"
    >
        ← Back
    </a>

</div>


<?php if ($error !== ''): ?>

    <div class="flash-message flash-error">
        <?= htmlspecialchars($error) ?>
    </div>

<?php endif; ?>


<div class="app-card edit-form-card">

    <div class="card-header">
        <h3>Edit Crop Information</h3>
    </div>

    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($crop['id']) ?>"
            >


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Crop Name *</label>

                    <input
                        type="text"
                        name="name"
                        class="app-input"
                        value="<?= htmlspecialchars($crop['name']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Crop Type *</label>

                    <select
                        name="type"
                        class="app-select"
                        required
                    >

                        <?php foreach ($types as $value => $label): ?>

                            <option
                                value="<?= htmlspecialchars($value) ?>"
                                <?= $crop['type'] === $value
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars($label) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <div class="app-form-group">

                <label>Farmer *</label>

                <select
                    name="farmer_id"
                    class="app-select"
                    required
                >

                    <?php foreach ($farmers as $farmer): ?>

                        <option
                            value="<?= htmlspecialchars($farmer['id']) ?>"
                            <?= $crop['farmer_id'] === $farmer['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars($farmer['id']) ?>
                            —
                            <?= htmlspecialchars($farmer['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Area (acres) *</label>

                    <input
                        type="number"
                        name="area"
                        class="app-input"
                        min="0.1"
                        step="0.1"
                        value="<?= htmlspecialchars($areaValue) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Status *</label>

                    <select
                        name="status"
                        class="app-select"
                        required
                    >

                        <?php foreach ($statuses as $status): ?>

                            <option
                                value="<?= htmlspecialchars($status) ?>"
                                <?= $crop['status'] === $status
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars($status) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Planting Date *</label>

                    <input
                        type="date"
                        name="planting_date"
                        class="app-input"
                        value="<?= htmlspecialchars(
                            $crop['planting_date']
                        ) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Expected Harvest Date *</label>

                    <input
                        type="date"
                        name="harvest_date"
                        class="app-input"
                        value="<?= htmlspecialchars(
                            $crop['harvest_date']
                        ) ?>"
                        required
                    >

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="crop_details.php?id=<?= urlencode($crop['id']) ?>"
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