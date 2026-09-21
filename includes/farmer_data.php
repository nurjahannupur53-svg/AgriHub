<?php

require_once __DIR__ . '/../config/db.php';

function getAllFarmers()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            id,
            name,
            phone,
            email,
            location,
            land,
            nid,
            joined,
            status
        FROM farmers
        ORDER BY id ASC
    ");

    $farmers = $stmt->fetchAll();

    foreach ($farmers as &$farmer) {

        $farmer['land'] =
            number_format(
                (float) $farmer['land'],
                1,
                '.',
                ''
            ) . ' acres';

        $farmer['status'] =
            ucfirst(strtolower($farmer['status']));
    }

    unset($farmer);

    return $farmers;
}


function findFarmerById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            phone,
            email,
            location,
            land,
            nid,
            joined,
            status
        FROM farmers
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $farmer = $stmt->fetch();

    if (!$farmer) {
        return null;
    }

    $farmer['land'] =
        number_format(
            (float) $farmer['land'],
            1,
            '.',
            ''
        ) . ' acres';

    $farmer['status'] =
        ucfirst(strtolower($farmer['status']));

    return $farmer;
}