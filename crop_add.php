<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/farmer_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crops.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$farmerId = trim($_POST['farmer_id'] ?? '');
$area = trim($_POST['area'] ?? '');
$plantingDate = trim($_POST['planting_date'] ?? '');
$harvestDate = trim($_POST['harvest_date'] ?? '');
$status = trim($_POST['status'] ?? 'Growing');

$allowedTypes = [
    'Grain',
    'Vegetable',
    'Fruit',
    'Legume',
    'Oilseed',
    'Fiber',
    'Spice',
    'Tuber',
    'Cash Crop',
    'Fodder',
    'Plantation',
    'Other'
];

$allowedStatuses = [
    'Growing',
    'Harvested',
    'Pending'
];

$farmer = findFarmerById($farmerId);

if (
    $name === '' ||
    $type === '' ||
    !$farmer ||
    $area === '' ||
    !is_numeric($area) ||
    (float) $area <= 0 ||
    $plantingDate === '' ||
    $harvestDate === ''
) {
    header('Location: crops.php?error=missing');
    exit;
}

if (!in_array($type, $allowedTypes, true)) {
    header('Location: crops.php?error=type');
    exit;
}

if (!in_array($status, $allowedStatuses, true)) {
    $status = 'Growing';
}

if ($harvestDate < $plantingDate) {
    header('Location: crops.php?error=date');
    exit;
}

try {

    $stmt = $pdo->query("
        SELECT
            MAX(
                CAST(
                    SUBSTRING(id, 2)
                    AS UNSIGNED
                )
            ) AS max_number
        FROM crops
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newId =
        'C' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );

    $stmt = $pdo->prepare("
        INSERT INTO crops
        (
            id,
            name,
            category,
            farmer_id,
            land,
            planting_date,
            harvest_date,
            status
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $newId,
        $name,
        $type,
        $farmerId,
        (float) $area,
        $plantingDate,
        $harvestDate,
        $status
    ]);

    header('Location: crops.php?success=added');
    exit;

} catch (PDOException $e) {

    header('Location: crops.php?error=database');
    exit;
}