<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/qc_data.php';
require_once __DIR__ . '/includes/batch_data.php';


/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quality_checks.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

$batchId = trim($_POST['batch_id'] ?? '');
$inspector = trim($_POST['inspector'] ?? '');
$date = trim($_POST['date'] ?? '');
$grade = trim($_POST['grade'] ?? '');
$moisture = trim($_POST['moisture'] ?? '');
$result = trim($_POST['result'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate Harvest Batch
|--------------------------------------------------------------------------
*/

$batch = findBatchById($batchId);

if (!$batch) {
    header('Location: quality_checks.php?error=invalid_batch');
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate required fields
|--------------------------------------------------------------------------
*/

if (
    $inspector === '' ||
    $date === '' ||
    $grade === '' ||
    $moisture === ''
) {
    header(
        'Location: quality_checks.php?batch=' .
        urlencode($batchId) .
        '&error=required'
    );
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate result
|--------------------------------------------------------------------------
*/

if (!in_array(
    $result,
    ['Approved', 'Rejected'],
    true
)) {
    header(
        'Location: quality_checks.php?batch=' .
        urlencode($batchId) .
        '&error=result'
    );
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate moisture
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($moisture) ||
    (float) $moisture < 0 ||
    (float) $moisture > 100
) {
    header(
        'Location: quality_checks.php?batch=' .
        urlencode($batchId) .
        '&error=moisture'
    );
    exit;
}

$moistureNumber = (float) $moisture;


/*
|--------------------------------------------------------------------------
| Inspection date cannot be before harvest date
|--------------------------------------------------------------------------
*/

if ($date < $batch['harvest_date']) {
    header(
        'Location: quality_checks.php?batch=' .
        urlencode($batchId) .
        '&error=date'
    );
    exit;
}


/*
|--------------------------------------------------------------------------
| Prevent duplicate QC for same Harvest Batch
|--------------------------------------------------------------------------
*/

if (findQualityCheckByBatch($batchId)) {
    header(
        'Location: quality_checks.php?batch=' .
        urlencode($batchId) .
        '&error=duplicate'
    );
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
    | Generate next QC ID
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
        FROM quality_checks
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newQcId =
        'QC' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Insert Quality Check
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO quality_checks
        (
            id,
            batch_id,
            inspector,
            inspection_date,
            grade,
            moisture,
            result
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $newQcId,
        $batchId,
        $inspector,
        $date,
        $grade,
        $moistureNumber,
        $result
    ]);


    /*
    |--------------------------------------------------------------------------
    | Determine Harvest Batch statuses
    |--------------------------------------------------------------------------
    */

    if ($result === 'Approved') {

        $qcStatus = 'Approved';
        $inventoryStatus = 'In Inventory';

    } else {

        $qcStatus = 'Rejected';
        $inventoryStatus = 'Rejected';
    }


    /*
    |--------------------------------------------------------------------------
    | Update Harvest Batch
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE harvest_batches
        SET
            qc_status = ?,
            inventory_status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $qcStatus,
        $inventoryStatus,
        $batchId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Commit both changes
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    header(
        'Location: quality_checks.php?batch=' .
        urlencode($batchId) .
        '&success=added'
    );

    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: quality_checks.php?batch=' .
        urlencode($batchId) .
        '&error=database'
    );

    exit;
}