<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header('Location: consultation.php');
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Lock Consultation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            status
        FROM consultations
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $consultation = $stmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | Consultation Must Exist
    |--------------------------------------------------------------------------
    */

    if (!$consultation) {

        $pdo->rollBack();

        header(
            'Location: consultation.php?error=notfound'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Completed / Cancelled Cannot Be Cancelled
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $consultation['status'],
            ['Completed', 'Cancelled'],
            true
        )
    ) {

        $pdo->rollBack();

        header(
            'Location: consultation.php?error=cannotcancel'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel Consultation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE consultations
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
        'Location: consultation.php?success=cancelled'
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