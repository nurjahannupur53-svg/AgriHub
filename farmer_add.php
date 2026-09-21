<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: farmers.php');
    exit;
}


$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$land = trim($_POST['land'] ?? '');
$email = trim($_POST['email'] ?? '');


if (
    $name === '' ||
    $phone === '' ||
    $location === '' ||
    $land === ''
) {
    header('Location: farmers.php?error=missing');
    exit;
}


if (
    $email !== '' &&
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    header('Location: farmers.php?error=email');
    exit;
}


/*
|--------------------------------------------------------------------------
| Generate next Farmer ID
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        MAX(
            CAST(
                SUBSTRING(id, 2)
                AS UNSIGNED
            )
        ) AS max_number
    FROM farmers
");

$row = $stmt->fetch();

$nextNumber =
    ((int) ($row['max_number'] ?? 0)) + 1;

$newId =
    'F' .
    str_pad(
        $nextNumber,
        3,
        '0',
        STR_PAD_LEFT
    );


/*
|--------------------------------------------------------------------------
| Insert Farmer
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO farmers
        (
            id,
            name,
            phone,
            location,
            land,
            email,
            status,
            joined
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'active',
            CURDATE()
        )
    ");

    $stmt->execute([
        $newId,
        $name,
        $phone,
        $location,
        (float) $land,
        $email !== '' ? $email : null
    ]);

    header(
        'Location: farmers.php?success=added'
    );

    exit;

} catch (PDOException $e) {

    header(
        'Location: farmers.php?error=database'
    );

    exit;
}