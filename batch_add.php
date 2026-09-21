<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/crop_data.php';


/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: harvest_batches.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

$cropId = trim($_POST['crop_id'] ?? '');
$harvestDate = trim($_POST['harvest_date'] ?? '');
$quantity = trim($_POST['quantity'] ?? '');
$location = trim($_POST['location'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate required fields
|--------------------------------------------------------------------------
*/

if (
    $cropId === '' ||
    $harvestDate === '' ||
    $quantity === '' ||
    $location === ''
) {
    header('Location: harvest_batches.php?error=required');
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate crop
|--------------------------------------------------------------------------
*/

$crop = findCropById($cropId);

if (!$crop) {
    header('Location: harvest_batches.php?error=invalid_crop');
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate quantity
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($quantity) ||
    (float) $quantity <= 0
) {
    header('Location: harvest_batches.php?error=quantity');
    exit;
}

$quantityNumber = (float) $quantity;


/*
|--------------------------------------------------------------------------
| Validate harvest date
|--------------------------------------------------------------------------
*/

if ($harvestDate < $crop['planting_date']) {
    header('Location: harvest_batches.php?error=harvest_date');
    exit;
}


/*
|--------------------------------------------------------------------------
| Create Harvest Batch
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | Generate next Batch ID
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
        FROM harvest_batches
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newBatchId =
        'HB' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Insert into MySQL
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO harvest_batches
        (
            id,
            crop_id,
            harvest_date,
            quantity,
            location,
            qc_status,
            inventory_status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'Pending',
            'Awaiting QC'
        )
    ");

    $stmt->execute([
        $newBatchId,
        $cropId,
        $harvestDate,
        $quantityNumber,
        $location
    ]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    header(
        'Location: harvest_batches.php?success=added'
    );
    exit;

} catch (PDOException $e) {

    header(
        'Location: harvest_batches.php?error=database'
    );
    exit;
}