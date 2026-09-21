<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/inventory_data.php';
require_once __DIR__ . '/includes/batch_data.php';

$pageTitle = 'Inventory';
$pageSubtitle = 'Warehouse stock and availability';

$batchFilter = trim($_GET['batch'] ?? '');
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';


/*
|--------------------------------------------------------------------------
| Automatically create inventory for an approved batch
|--------------------------------------------------------------------------
|
| Example:
| inventory.php?batch=HB009
|
| Inventory is created only when:
| 1. Batch exists
| 2. QC is Approved
| 3. Inventory does not already exist
|
*/

if ($batchFilter !== '') {

    $batch = findBatchById($batchFilter);

    if (
        $batch &&
        $batch['qc_status'] === 'Approved' &&
        !findInventoryByBatch($batchFilter)
    ) {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Re-check inside transaction
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM inventory
                WHERE batch_id = ?
                LIMIT 1
            ");

            $stmt->execute([$batchFilter]);

            $existingInventory = $stmt->fetch();


            if (!$existingInventory) {

                /*
                |--------------------------------------------------------------------------
                | Generate next Inventory ID
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->query("
                    SELECT
                        MAX(
                            CAST(
                                SUBSTRING(id, 4)
                                AS UNSIGNED
                            )
                        ) AS max_number
                    FROM inventory
                ");

                $row = $stmt->fetch();

                $nextNumber =
                    ((int) ($row['max_number'] ?? 0)) + 1;

                $newInventoryId =
                    'INV' .
                    str_pad(
                        $nextNumber,
                        3,
                        '0',
                        STR_PAD_LEFT
                    );


                /*
                |--------------------------------------------------------------------------
                | Convert batch quantity to numeric value
                |--------------------------------------------------------------------------
                |
                | batch_data.php currently returns values such as:
                | 7.2 ton
                |
                */

                $quantity = (float) preg_replace(
                    '/[^0-9.]/',
                    '',
                    $batch['quantity']
                );


                if ($quantity <= 0) {
                    throw new Exception(
                        'Invalid harvest batch quantity.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Create Inventory record
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    INSERT INTO inventory
                    (
                        id,
                        batch_id,
                        total,
                        available,
                        reserved,
                        location,
                        status,
                        threshold
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        0,
                        ?,
                        'Available',
                        2.00
                    )
                ");

                $stmt->execute([
                    $newInventoryId,
                    $batch['id'],
                    $quantity,
                    $quantity,
                    $batch['location']
                ]);


                /*
                |--------------------------------------------------------------------------
                | Keep Harvest Batch status synchronized
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    UPDATE harvest_batches
                    SET inventory_status = 'In Inventory'
                    WHERE id = ?
                ");

                $stmt->execute([
                    $batch['id']
                ]);
            }


            $pdo->commit();

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'inventory_create';
        }
    }
}


/*
|--------------------------------------------------------------------------
| Get inventory from MySQL
|--------------------------------------------------------------------------
*/

$inventory = getAllInventory();


/*
|--------------------------------------------------------------------------
| Summary totals
|--------------------------------------------------------------------------
*/

$totalStock = 0;
$availableStock = 0;
$reservedStock = 0;
$lowStockCount = 0;

foreach ($inventory as $item) {

    $totalStock += (float) $item['total'];

    $availableStock +=
        (float) $item['available'];

    $reservedStock +=
        (float) $item['reserved'];

    if (
        (float) $item['available'] <
        (float) $item['threshold']
    ) {
        $lowStockCount++;
    }
}


/*
|--------------------------------------------------------------------------
| Optional batch filter
|--------------------------------------------------------------------------
*/

if ($batchFilter !== '') {

    $inventory = array_values(
        array_filter(
            $inventory,
            function ($item) use ($batchFilter) {

                return
                    $item['batch_id'] ===
                    $batchFilter;
            }
        )
    );
}


require_once __DIR__ . '/includes/header.php';

?>


<?php if ($success === 'updated'): ?>

    <div class="flash-message flash-success">
        Inventory updated successfully.
    </div>

<?php endif; ?>


<?php if ($error === 'inventory_create'): ?>

    <div class="flash-message flash-error">
        Inventory could not be created for this batch.
    </div>

<?php endif; ?>


<!-- SUMMARY CARDS -->

<div class="inventory-stat-grid">

    <div class="inventory-stat-card">

        <span>Total Stock</span>

        <strong>
            <?= number_format($totalStock, 1) ?> ton
        </strong>

        <small>
            Across all inventory
        </small>

    </div>


    <div class="inventory-stat-card">

        <span>Available</span>

        <strong>
            <?= number_format($availableStock, 1) ?> ton
        </strong>

        <small>
            Ready for orders
        </small>

    </div>


    <div class="inventory-stat-card">

        <span>Reserved</span>

        <strong>
            <?= number_format($reservedStock, 1) ?> ton
        </strong>

        <small>
            Reserved for buyers
        </small>

    </div>


    <div class="inventory-stat-card warning">

        <span>Low Stock Alerts</span>

        <strong>
            <?= $lowStockCount ?>
        </strong>

        <small>
            Below threshold
        </small>

    </div>

</div>


<!-- LOW STOCK ALERT -->

<?php if ($lowStockCount > 0): ?>

    <div class="inventory-alert">

        <div class="inventory-alert-icon">
            !
        </div>

        <div>

            <strong>Low Stock Alert</strong>

            <p>
                <?= $lowStockCount ?>
                inventory item<?= $lowStockCount === 1 ? '' : 's' ?>
                <?= $lowStockCount === 1 ? 'is' : 'are' ?>
                below the configured stock threshold.
            </p>

        </div>

    </div>

<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="inventorySearch"
            placeholder="Search inventory, batch or crop"
        >

    </div>


    <?php if ($batchFilter !== ''): ?>

        <a
            href="inventory.php"
            class="btn btn-light"
        >
            Clear Batch Filter
        </a>

    <?php endif; ?>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table
            class="app-table"
            id="inventoryTable"
        >

            <thead>

                <tr>
                    <th>Inventory ID</th>
                    <th>Batch</th>
                    <th>Crop</th>
                    <th>Total Stock</th>
                    <th>Available</th>
                    <th>Reserved</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>

            </thead>


            <tbody>

            <?php if ($inventory): ?>

                <?php foreach ($inventory as $item): ?>

                    <tr>

                        <td>

                            <strong>
                                <?= htmlspecialchars($item['id']) ?>
                            </strong>

                        </td>


                        <td>

                            <a
                                href="batch_details.php?id=<?= urlencode($item['batch_id']) ?>"
                                class="table-id-link"
                            >
                                <?= htmlspecialchars($item['batch_id']) ?>
                            </a>

                        </td>


                        <td>
                            <?= htmlspecialchars($item['crop']) ?>
                        </td>


                        <td>
                            <?= number_format((float) $item['total'], 1) ?>
                            ton
                        </td>


                        <td>
                            <?= number_format((float) $item['available'], 1) ?>
                            ton
                        </td>


                        <td>
                            <?= number_format((float) $item['reserved'], 1) ?>
                            ton
                        </td>


                        <td>
                            <?= htmlspecialchars($item['location']) ?>
                        </td>


                        <td>

                            <?php

                            $statusClass = 'status-success';

                            if (
                                $item['status'] ===
                                'Partially Reserved'
                            ) {

                                $statusClass =
                                    'status-warning';

                            } elseif (
                                $item['status'] ===
                                'Out of Stock'
                            ) {

                                $statusClass =
                                    'status-danger';
                            }

                            ?>

                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($item['status']) ?>
                            </span>

                        </td>


                        <td>

                            <div class="action-buttons">

                                <a
                                    href="inventory_details.php?id=<?= urlencode($item['id']) ?>"
                                    class="action-link view"
                                >
                                    View
                                </a>


                                <a
                                    href="inventory_update.php?id=<?= urlencode($item['id']) ?>"
                                    class="action-link edit"
                                >
                                    Update
                                </a>


                                <a
                                    href="demand_forecast.php?crop=<?= urlencode($item['crop']) ?>"
                                    class="action-link forecast"
                                >
                                    Forecast
                                </a>


                                <a
                                    href="market_orders.php?inventory=<?= urlencode($item['id']) ?>"
                                    class="action-link order"
                                >
                                    Order
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="9">

                        <div class="empty-state">
                            No inventory record found.
                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("inventorySearch");

    const rows =
        document.querySelectorAll(
            "#inventoryTable tbody tr"
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