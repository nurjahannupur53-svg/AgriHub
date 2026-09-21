<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/super_shop_data.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header('Location: super_shop_orders.php');
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Lock Super Shop Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            status
        FROM super_shop_orders
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $order = $stmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | Validate Order
    |--------------------------------------------------------------------------
    */

    if (!$order) {

        $pdo->rollBack();

        header(
            'Location: super_shop_orders.php?error=notfound'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Delivered / Cancelled Orders Cannot Be Cancelled
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

        header(
            'Location: super_shop_orders.php?error=cannotcancel'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE super_shop_orders
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
        'Location: super_shop_orders.php?success=cancelled'
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