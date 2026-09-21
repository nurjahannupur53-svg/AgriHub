<?php

require_once __DIR__ . '/../config/db.php';

function getAllCrops()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            c.id,
            c.name,
            c.category AS type,
            c.farmer_id,
            f.name AS farmer,
            c.land,
            c.planting_date,
            c.harvest_date,
            c.status
        FROM crops c
        INNER JOIN farmers f
            ON f.id = c.farmer_id
        ORDER BY c.id ASC
    ");

    $crops = $stmt->fetchAll();

    foreach ($crops as &$crop) {

        $crop['area'] =
            number_format(
                (float) $crop['land'],
                1,
                '.',
                ''
            ) . ' acres';
    }

    unset($crop);

    return $crops;
}

function findCropById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.name,
            c.category AS type,
            c.farmer_id,
            f.name AS farmer,
            c.land,
            c.planting_date,
            c.harvest_date,
            c.status
        FROM crops c
        INNER JOIN farmers f
            ON f.id = c.farmer_id
        WHERE c.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $crop = $stmt->fetch();

    if (!$crop) {
        return null;
    }

    $crop['area'] =
        number_format(
            (float) $crop['land'],
            1,
            '.',
            ''
        ) . ' acres';

    return $crop;
}