<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header('Location: farmers.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Check Farmer Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM farmers
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

if (!$stmt->fetch()) {

    header('Location: farmers.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Delete Farmer
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        DELETE FROM farmers
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header(
        'Location: farmers.php?success=deleted'
    );

    exit;

} catch (PDOException $e) {

    header(
        'Location: farmers.php?error=delete'
    );

    exit;
}