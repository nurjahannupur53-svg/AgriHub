<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/vehicle_data.php';


$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

$vehicle = findVehicleById($id);

if (!$vehicle) {
    header('Location: vehicles.php');
    exit;
}

$error = '';


/*
|--------------------------------------------------------------------------
| Update Vehicle
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $registration = trim($_POST['registration'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');
    $driver = trim($_POST['driver'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $allowedStatuses = [
        'Available',
        'Active',
        'In Transit',
        'Maintenance'
    ];


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (
        $registration === '' ||
        $type === '' ||
        $capacity === '' ||
        !in_array($status, $allowedStatuses, true)
    ) {

        $error = 'Please complete all required fields correctly.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Normalize Capacity
        |--------------------------------------------------------------------------
        |
        | Accepts:
        | 10
        | 10.5
        | 10 ton
        |
        */

        $capacityNumber = (float) preg_replace(
            '/[^0-9.]/',
            '',
            $capacity
        );

        if ($capacityNumber <= 0) {

            $error = 'Vehicle capacity must be greater than 0.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Driver
            |--------------------------------------------------------------------------
            */

            if ($driver === '') {
                $driver = 'Unassigned';
                $phone = '';
            }


            try {

                /*
                |--------------------------------------------------------------------------
                | Check Duplicate Registration
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    SELECT id
                    FROM vehicles
                    WHERE registration = ?
                      AND id <> ?
                    LIMIT 1
                ");

                $stmt->execute([
                    $registration,
                    $id
                ]);

                if ($stmt->fetch()) {

                    $error =
                        'This registration number is already used by another vehicle.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Update Database
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        UPDATE vehicles

                        SET
                            registration = ?,
                            type = ?,
                            capacity = ?,
                            driver = ?,
                            phone = ?,
                            status = ?

                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $registration,
                        $type,
                        $capacityNumber,
                        $driver,
                        $phone !== '' ? $phone : null,
                        $status,
                        $id
                    ]);


                    header(
                        'Location: vehicles.php?success=updated'
                    );

                    exit;
                }

            } catch (Throwable $e) {

                $error =
                    'Unable to update the vehicle. Please try again.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep submitted values if validation fails
    |--------------------------------------------------------------------------
    */

    $vehicle['registration'] = $registration;
    $vehicle['type'] = $type;
    $vehicle['capacity'] = $capacity;
    $vehicle['driver'] = $driver;
    $vehicle['phone'] = $phone;
    $vehicle['status'] = $status;
}


$pageTitle = 'Edit Vehicle';

$pageSubtitle =
    $vehicle['id'] . ' — ' . $vehicle['registration'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="vehicle_details.php?id=<?= urlencode($vehicle['id']) ?>"
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
        <h3>Vehicle & Driver Information</h3>
    </div>


    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($vehicle['id']) ?>"
            >


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Registration Number</label>

                    <input
                        type="text"
                        name="registration"
                        class="app-input"
                        value="<?= htmlspecialchars($vehicle['registration']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Vehicle Type</label>

                    <input
                        type="text"
                        name="type"
                        class="app-input"
                        value="<?= htmlspecialchars($vehicle['type']) ?>"
                        required
                    >

                </div>

            </div>


            <div class="app-form-group">

                <label>Capacity</label>

                <input
                    type="text"
                    name="capacity"
                    class="app-input"
                    value="<?= htmlspecialchars($vehicle['capacity']) ?>"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Driver Name</label>

                    <input
                        type="text"
                        name="driver"
                        class="app-input"
                        value="<?= $vehicle['driver'] !== 'Unassigned'
                            ? htmlspecialchars($vehicle['driver'])
                            : '' ?>"
                        placeholder="Leave blank if unassigned"
                    >

                </div>


                <div class="app-form-group">

                    <label>Driver Phone</label>

                    <input
                        type="text"
                        name="phone"
                        class="app-input"
                        value="<?= htmlspecialchars($vehicle['phone']) ?>"
                    >

                </div>

            </div>


            <div class="app-form-group">

                <label>Status</label>

                <select
                    name="status"
                    class="app-select"
                    required
                >

                    <?php

                    $statuses = [
                        'Available',
                        'Active',
                        'In Transit',
                        'Maintenance'
                    ];

                    ?>

                    <?php foreach ($statuses as $status): ?>

                        <option
                            value="<?= htmlspecialchars($status) ?>"
                            <?= $vehicle['status'] === $status
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars($status) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-actions">

                <a
                    href="vehicle_details.php?id=<?= urlencode($vehicle['id']) ?>"
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