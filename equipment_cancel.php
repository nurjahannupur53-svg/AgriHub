<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header('Location: equipment_booking.php');
    exit;
}


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

    $booking = $stmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | Booking Must Exist
    |--------------------------------------------------------------------------
    */

    if (!$booking) {

        $pdo->rollBack();

        header(
            'Location: equipment_booking.php?error=notfound'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Returned / Cancelled Booking Cannot Be Cancelled
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $booking['status'],
            ['Returned', 'Cancelled'],
            true
        )
    ) {

        $pdo->rollBack();

        header(
            'Location: equipment_booking.php?error=cannotcancel'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel Booking
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE equipment_bookings
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
        'Location: equipment_booking.php?success=cancelled'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: equipment_booking.php?error=database'
    );

    exit;
}