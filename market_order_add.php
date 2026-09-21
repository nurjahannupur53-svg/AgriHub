<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/order_data.php';
require_once __DIR__ . '/includes/inventory_data.php';


/*
|--------------------------------------------------------------------------
| Only allow POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: market_orders.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

$inventoryId = trim($_POST['inventory_id'] ?? '');
$buyer = trim($_POST['buyer'] ?? '');
$quantity = trim($_POST['quantity'] ?? '');
$amount = trim($_POST['amount'] ?? '');
$orderDate = trim($_POST['order_date'] ?? '');
$deliveryDate = trim($_POST['delivery_date'] ?? '');
$payment = trim($_POST['payment'] ?? 'Unpaid');


/*
|--------------------------------------------------------------------------
| Required fields
|--------------------------------------------------------------------------
*/

if (
    $inventoryId === '' ||
    $buyer === '' ||
    $quantity === '' ||
    $amount === '' ||
    $orderDate === '' ||
    $deliveryDate === ''
) {
    header('Location: market_orders.php?error=missing');
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate numeric values
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($quantity) ||
    !is_numeric($amount) ||
    (float) $quantity <= 0 ||
    (float) $amount <= 0
) {
    header('Location: market_orders.php?error=missing');
    exit;
}

$quantityNumber = (float) $quantity;
$amountNumber = (float) $amount;


/*
|--------------------------------------------------------------------------
| Validate payment
|--------------------------------------------------------------------------
*/

$allowedPayments = [
    'Paid',
    'Partial',
    'Unpaid'
];

if (!in_array($payment, $allowedPayments, true)) {
    $payment = 'Unpaid';
}


/*
|--------------------------------------------------------------------------
| Validate dates
|--------------------------------------------------------------------------
*/

if ($deliveryDate < $orderDate) {
    header('Location: market_orders.php?error=date');
    exit;
}


/*
|--------------------------------------------------------------------------
| Check inventory
|--------------------------------------------------------------------------
*/

$inventory = findInventoryById($inventoryId);

if (!$inventory) {
    header('Location: market_orders.php?error=inventory');
    exit;
}

if (
    $quantityNumber >
    (float) $inventory['available']
) {
    header('Location: market_orders.php?error=stock');
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
    | Lock and re-check inventory
    |--------------------------------------------------------------------------
    |
    | Important:
    | Another order could be created between the first stock check
    | and the INSERT. FOR UPDATE prevents overselling.
    |
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            total,
            available,
            reserved,
            location,
            threshold,
            status
        FROM inventory
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$inventoryId]);

    $lockedInventory = $stmt->fetch();

    if (!$lockedInventory) {
        throw new Exception('Inventory not found.');
    }

    $currentAvailable =
        (float) $lockedInventory['available'];

    $currentReserved =
        (float) $lockedInventory['reserved'];

    if ($quantityNumber > $currentAvailable) {

        $pdo->rollBack();

        header(
            'Location: market_orders.php?error=stock'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate next Order ID
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
        FROM market_orders
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newId =
        'ORD' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Insert Market Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO market_orders
        (
            id,
            inventory_id,
            buyer,
            quantity,
            amount,
            order_date,
            delivery_date,
            payment,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'Processing'
        )
    ");

    $stmt->execute([
        $newId,
        $inventoryId,
        $buyer,
        $quantityNumber,
        $amountNumber,
        $orderDate,
        $deliveryDate,
        $payment
    ]);


    /*
    |--------------------------------------------------------------------------
    | Calculate new Inventory values
    |--------------------------------------------------------------------------
    */

    $newAvailable =
        $currentAvailable - $quantityNumber;

    $newReserved =
        $currentReserved + $quantityNumber;

    if ($newAvailable <= 0) {
        $inventoryStatus = 'Out of Stock';
    } else {
        $inventoryStatus = 'Partially Reserved';
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
        $inventoryId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: market_orders.php?success=added'
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