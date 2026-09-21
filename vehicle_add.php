<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vehicles.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$registration = trim($_POST['registration'] ?? '');
$type = trim($_POST['type'] ?? '');
$capacity = trim($_POST['capacity'] ?? '');
$driver = trim($_POST['driver'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$status = trim($_POST['status'] ?? 'Available');


/*
|--------------------------------------------------------------------------
| Validate Status
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'Available',
    'Active',
    'In Transit',
    'Maintenance'
];

if (
    $registration === '' ||
    $type === '' ||
    $capacity === '' ||
    !in_array($status, $allowedStatuses, true)
) {
    header('Location: vehicles.php?error=missing');
    exit;
}


/*
|--------------------------------------------------------------------------
| Normalize Capacity
|--------------------------------------------------------------------------
|
| Supports:
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
    header('Location: vehicles.php?error=capacity');
    exit;
}


/*
|--------------------------------------------------------------------------
| Driver
|--------------------------------------------------------------------------
*/

if ($driver === '') {
    $driver = 'Unassigned';
    $phone = '';
}


/*
|--------------------------------------------------------------------------
| Insert Vehicle
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Registration
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM vehicles
        WHERE registration = ?
        LIMIT 1
    ");

    $stmt->execute([$registration]);

    if ($stmt->fetch()) {

        $pdo->rollBack();

        header(
            'Location: vehicles.php?error=registration'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Next VEH ID
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
        FROM vehicles
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newId =
        'VEH' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Save to Database
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO vehicles
        (
            id,
            registration,
            type,
            capacity,
            driver,
            phone,
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
            ?
        )
    ");

    $stmt->execute([
        $newId,
        $registration,
        $type,
        $capacityNumber,
        $driver,
        $phone !== '' ? $phone : null,
        $status
    ]);


    $pdo->commit();


    header(
        'Location: vehicles.php?success=added'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: vehicles.php?error=database'
    );

    exit;
}