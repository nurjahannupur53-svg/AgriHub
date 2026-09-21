<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/order_data.php';


/*
|--------------------------------------------------------------------------
| Get Order ID
|--------------------------------------------------------------------------
*/

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header('Location: market_orders.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Database Transaction
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Lock Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            inventory_id,
            quantity,
            status
        FROM market_orders
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $order = $stmt->fetch();

    if (!$order) {

        $pdo->rollBack();

        header('Location: market_orders.php');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Delivered / Already Cancelled cannot be cancelled
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $order['status'],
            ['Delivered', 'Cancelled'],
            true
        )
    ) {

        $pdo->rollBack();

        header('Location: market_orders.php');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Lock Inventory
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            total,
            available,
            reserved
        FROM inventory
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        $order['inventory_id']
    ]);

    $inventory = $stmt->fetch();

    if (!$inventory) {
        throw new Exception('Inventory not found.');
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Stock Return
    |--------------------------------------------------------------------------
    */

    $orderQuantity =
        (float) $order['quantity'];

    $currentAvailable =
        (float) $inventory['available'];

    $currentReserved =
        (float) $inventory['reserved'];

    $total =
        (float) $inventory['total'];


    /*
    |--------------------------------------------------------------------------
    | Safety Check
    |--------------------------------------------------------------------------
    |
    | Only restore the quantity that is actually reserved.
    | This prevents historical/inconsistent data from creating stock.
    |
    */

    $returnQuantity = min(
        $orderQuantity,
        $currentReserved
    );

    $newReserved =
        $currentReserved - $returnQuantity;

    $newAvailable =
        $currentAvailable + $returnQuantity;


    /*
    |--------------------------------------------------------------------------
    | Never exceed Total Stock
    |--------------------------------------------------------------------------
    */

    if ($newAvailable + $newReserved > $total) {

        throw new Exception(
            'Inventory balance would exceed total stock.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Recalculate Inventory Status
    |--------------------------------------------------------------------------
    */

    if ($newAvailable <= 0) {

        $inventoryStatus = 'Out of Stock';

    } elseif ($newReserved > 0) {

        $inventoryStatus = 'Partially Reserved';

    } else {

        $inventoryStatus = 'Available';
    }


    /*
    |--------------------------------------------------------------------------
    | Update Inventory
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE inventory
        SET
            available = ?,
            reserved = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $newAvailable,
        $newReserved,
        $inventoryStatus,
        $inventory['id']
    ]);


    /*
    |--------------------------------------------------------------------------
    | Cancel Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE market_orders
        SET status = 'Cancelled'
        WHERE id = ?
    ");

    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: market_orders.php?success=cancelled'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: market_orders.php?error=database'
    );

    exit;
}