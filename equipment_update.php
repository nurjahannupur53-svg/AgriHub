<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/equipment_data.php';

$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

$booking = findEquipmentBookingById($id);

if (!$booking) {
    header('Location: equipment_booking.php');
    exit;
}

if ($booking['status'] === 'Cancelled') {
    header('Location: equipment_booking.php');
    exit;
}

$error = '';


/*
|--------------------------------------------------------------------------
| Update Booking
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $allowedStatuses = [
        'Active',
        'Returned'
    ];


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $startDate === '' ||
        $endDate === '' ||
        $endDate < $startDate
    ) {

        $error = 'Please enter a valid booking date range.';

    } elseif (!in_array($status, $allowedStatuses, true)) {

        $error = 'Please select a valid status.';

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Booking
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    status
                FROM equipment_bookings
                WHERE id = ?
                LIMIT 1
                FOR UPDATE
            ");

            $stmt->execute([$id]);

            $lockedBooking = $stmt->fetch();

            if (!$lockedBooking) {
                throw new Exception('Booking not found.');
            }


            /*
            |--------------------------------------------------------------------------
            | Cancelled Booking Cannot Be Updated
            |--------------------------------------------------------------------------
            */

            if ($lockedBooking['status'] === 'Cancelled') {

                $pdo->rollBack();

                header(
                    'Location: equipment_booking.php?error=cancelled'
                );

                exit;
            }


            /*
            |--------------------------------------------------------------------------
            | Update MySQL
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE equipment_bookings
                SET
                    start_date = ?,
                    end_date = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $startDate,
                $endDate,
                $status,
                $id
            ]);


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            header(
                'Location: equipment_booking.php?success=updated'
            );

            exit;


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                'Unable to update the booking. Please try again.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Submitted Values If Validation Fails
    |--------------------------------------------------------------------------
    */

    $booking['start_date'] = $startDate;
    $booking['end_date'] = $endDate;
    $booking['status'] = $status;
}


$pageTitle = 'Update Equipment Booking';

$pageSubtitle =
    $booking['id'] . ' — ' . $booking['equipment'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="equipment_details.php?id=<?= urlencode($booking['id']) ?>"
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

        <div>

            <h3>Update Booking</h3>

            <span class="card-subtitle">
                <?= htmlspecialchars($booking['farmer']) ?>
            </span>

        </div>

    </div>


    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($booking['id']) ?>"
            >


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Start Date</label>

                    <input
                        type="date"
                        name="start_date"
                        class="app-input"
                        value="<?= htmlspecialchars($booking['start_date']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>End Date</label>

                    <input
                        type="date"
                        name="end_date"
                        class="app-input"
                        value="<?= htmlspecialchars($booking['end_date']) ?>"
                        required
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

                    <option
                        value="Active"
                        <?= $booking['status'] === 'Active'
                            ? 'selected'
                            : '' ?>
                    >
                        Active
                    </option>

                    <option
                        value="Returned"
                        <?= $booking['status'] === 'Returned'
                            ? 'selected'
                            : '' ?>
                    >
                        Returned
                    </option>

                </select>

            </div>


            <div class="form-actions">

                <a
                    href="equipment_details.php?id=<?= urlencode($booking['id']) ?>"
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