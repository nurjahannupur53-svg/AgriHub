<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/crop_data.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header('Location: crops.php');
    exit;
}

$crop = findCropById($id);

if (!$crop) {
    header('Location: crops.php');
    exit;
}

try {

    $stmt = $pdo->prepare("
        DELETE FROM crops
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: crops.php?success=deleted');
    exit;

} catch (PDOException $e) {

    header('Location: crops.php?error=delete');
    exit;
}