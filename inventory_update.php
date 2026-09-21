<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/inventory_data.php';

$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

$item = findInventoryById($id);

if (!$item) {
    header('Location: inventory.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $available = trim($_POST['available'] ?? '');
    $reserved = trim($_POST['reserved'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $threshold = trim($_POST['threshold'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Required fields
    |--------------------------------------------------------------------------
    */

    if (
        $available === '' ||
        $reserved === '' ||
        $location === '' ||
        $threshold === ''
    ) {

        $error = 'Please complete all required fields.';

    /*
    |--------------------------------------------------------------------------
    | Numeric validation
    |--------------------------------------------------------------------------
    */

    } elseif (
        !is_numeric($available) ||
        !is_numeric($reserved) ||
        !is_numeric($threshold)
    ) {

        $error = 'Stock values must be valid numbers.';

    } elseif (
        (float) $available < 0 ||
        (float) $reserved < 0 ||
        (float) $threshold < 0
    ) {

        $error = 'Stock values cannot be negative.';

    /*
    |--------------------------------------------------------------------------
    | Inventory balance validation
    |--------------------------------------------------------------------------
    */

    } elseif (
        ((float) $available + (float) $reserved) >
        (float) $item['total']
    ) {

        $error =
            'Available + reserved stock cannot exceed total stock.';

    } else {

        $availableNumber = (float) $available;
        $reservedNumber = (float) $reserved;
        $thresholdNumber = (float) $threshold;


        /*
        |--------------------------------------------------------------------------
        | Automatically calculate inventory status
        |--------------------------------------------------------------------------
        */

        if ($availableNumber <= 0) {

            $status = 'Out of Stock';

        } elseif ($reservedNumber > 0) {

            $status = 'Partially Reserved';

        } else {

            $status = 'Available';
        }


        /*
        |--------------------------------------------------------------------------
        | Update MySQL
        |--------------------------------------------------------------------------
        */

        try {

            $stmt = $pdo->prepare("
                UPDATE inventory

                SET
                    available = ?,
                    reserved = ?,
                    location = ?,
                    threshold = ?,
                    status = ?

                WHERE id = ?
            ");

            $stmt->execute([
                $availableNumber,
                $reservedNumber,
                $location,
                $thresholdNumber,
                $status,
                $id
            ]);

            header(
                'Location: inventory.php?success=updated'
            );

            exit;

        } catch (PDOException $e) {

            $error =
                'Unable to update inventory. Please try again.';
        }
    }
}


$pageTitle = 'Update Inventory';
$pageSubtitle = $item['id'] . ' — ' . $item['crop'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="inventory_details.php?id=<?= urlencode($item['id']) ?>"
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
            <h3>Update Stock</h3>

            <span class="card-subtitle">
                Total stock:
                <?= number_format((float) $item['total'], 1) ?>
                ton
            </span>
        </div>

    </div>


    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($item['id']) ?>"
            >


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Available Stock (ton)</label>

                    <input
                        type="number"
                        name="available"
                        class="app-input"
                        min="0"
                        step="0.1"
                        value="<?= htmlspecialchars((string) $item['available']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Reserved Stock (ton)</label>

                    <input
                        type="number"
                        name="reserved"
                        class="app-input"
                        min="0"
                        step="0.1"
                        value="<?= htmlspecialchars((string) $item['reserved']) ?>"
                        required
                    >

                </div>

            </div>


            <div class="app-form-group">

                <label>Warehouse / Location</label>

                <input
                    type="text"
                    name="location"
                    class="app-input"
                    value="<?= htmlspecialchars($item['location']) ?>"
                    required
                >

            </div>


            <div class="app-form-group">

                <label>Low Stock Threshold (ton)</label>

                <input
                    type="number"
                    name="threshold"
                    class="app-input"
                    min="0"
                    step="0.1"
                    value="<?= htmlspecialchars((string) $item['threshold']) ?>"
                    required
                >

            </div>


            <div class="form-actions">

                <a
                    href="inventory_details.php?id=<?= urlencode($item['id']) ?>"
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