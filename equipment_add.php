<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/farmer_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: equipment_booking.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Form Data
|--------------------------------------------------------------------------
*/

$equipment = trim($_POST['equipment'] ?? '');
$purpose = trim($_POST['purpose'] ?? '');
$farmerId = trim($_POST['farmer_id'] ?? '');
$startDate = trim($_POST['start_date'] ?? '');
$endDate = trim($_POST['end_date'] ?? '');


/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if (
    $equipment === '' ||
    $purpose === '' ||
    $farmerId === '' ||
    $startDate === '' ||
    $endDate === ''
) {
    header('Location: equipment_booking.php?error=missing');
    exit;
}


/*
|--------------------------------------------------------------------------
| Date Validation
|--------------------------------------------------------------------------
*/

if ($endDate < $startDate) {
    header('Location: equipment_booking.php?error=date');
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Validate Farmer
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            status
        FROM farmers
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$farmerId]);

    $farmer = $stmt->fetch();

    if (!$farmer) {

        $pdo->rollBack();

        header('Location: equipment_booking.php?error=farmer');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Only Active Farmer Can Receive New Booking
    |--------------------------------------------------------------------------
    */

    if ($farmer['status'] !== 'active') {

        $pdo->rollBack();

        header(
            'Location: equipment_booking.php?error=farmerinactive'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Next Equipment Booking ID
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
        FROM equipment_bookings
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newId =
        'EB' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Insert Booking
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO equipment_bookings
        (
            id,
            equipment,
            purpose,
            farmer_id,
            start_date,
            end_date,
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
            'Active'
        )
    ");

    $stmt->execute([
        $newId,
        $equipment,
        $purpose,
        $farmerId,
        $startDate,
        $endDate
    ]);


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: equipment_booking.php?success=added'
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