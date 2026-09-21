<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: super_shop_orders.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Form Data
|--------------------------------------------------------------------------
*/

$shop = trim($_POST['shop'] ?? '');
$product = trim($_POST['product'] ?? '');
$crop = trim($_POST['crop'] ?? '');
$quantity = (int) ($_POST['quantity'] ?? 0);
$orderDate = trim($_POST['order_date'] ?? '');
$deliveryDate = trim($_POST['delivery_date'] ?? '');


/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if (
    $shop === '' ||
    $product === '' ||
    $crop === '' ||
    $quantity <= 0 ||
    $orderDate === '' ||
    $deliveryDate === ''
) {
    header('Location: super_shop_orders.php?error=missing');
    exit;
}


/*
|--------------------------------------------------------------------------
| Date Validation
|--------------------------------------------------------------------------
*/

if ($deliveryDate < $orderDate) {
    header('Location: super_shop_orders.php?error=date');
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Find Crop
    |--------------------------------------------------------------------------
    |
    | Existing form currently sends crop name, for example:
    | Aman Rice, Wheat, Lentil, Brinjal.
    |
    | Therefore we resolve the crop name to its real crop_id.
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            name
        FROM crops
        WHERE name = ?
        LIMIT 1
    ");

    $stmt->execute([$crop]);

    $cropRow = $stmt->fetch();

    if (!$cropRow) {

        $pdo->rollBack();

        header(
            'Location: super_shop_orders.php?error=crop'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Next Super Shop Order ID
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT
            MAX(
                CAST(
                    SUBSTRING(id, 3)
                    AS UNSIGNED
                )
            ) AS max_number
        FROM super_shop_orders
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newId =
        'SS' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Insert Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO super_shop_orders
        (
            id,
            shop,
            product,
            crop_id,
            quantity,
            unit,
            order_date,
            delivery_date,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'packs',
            ?,
            ?,
            'Pending'
        )
    ");

    $stmt->execute([
        $newId,
        $shop,
        $product,
        $cropRow['id'],
        $quantity,
        $orderDate,
        $deliveryDate
    ]);


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: super_shop_orders.php?success=added'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: super_shop_orders.php?error=database'
    );

    exit;
}