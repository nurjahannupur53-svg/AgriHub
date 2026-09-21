<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: consultation.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Form Data
|--------------------------------------------------------------------------
*/

$farmerId = trim($_POST['farmer_id'] ?? '');
$expert = trim($_POST['expert'] ?? '');
$topic = trim($_POST['topic'] ?? '');
$date = trim($_POST['date'] ?? '');


/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if (
    $farmerId === '' ||
    $expert === '' ||
    $topic === '' ||
    $date === ''
) {
    header('Location: consultation.php?error=missing');
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

        header(
            'Location: consultation.php?error=farmer'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Only Active Farmer Can Create New Consultation
    |--------------------------------------------------------------------------
    */

    if ($farmer['status'] !== 'active') {

        $pdo->rollBack();

        header(
            'Location: consultation.php?error=farmerinactive'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Next Consultation ID
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
        FROM consultations
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newId =
        'CON' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Insert Consultation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO consultations
        (
            id,
            farmer_id,
            expert,
            topic,
            consultation_date,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'Scheduled'
        )
    ");

    $stmt->execute([
        $newId,
        $farmerId,
        $expert,
        $topic,
        $date
    ]);


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: consultation.php?success=added'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: consultation.php?error=database'
    );

    exit;
}