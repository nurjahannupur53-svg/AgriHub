<?php

require_once __DIR__ . '/../config/db.php';


function getAllBatches()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            hb.id,
            hb.crop_id,
            c.name AS crop,
            c.farmer_id,
            f.name AS farmer,
            hb.harvest_date,
            hb.quantity,
            hb.qc_status,
            hb.location,
            hb.inventory_status
        FROM harvest_batches hb

        INNER JOIN crops c
            ON c.id = hb.crop_id

        INNER JOIN farmers f
            ON f.id = c.farmer_id

        ORDER BY hb.id ASC
    ");

    $batches = $stmt->fetchAll();

    foreach ($batches as &$batch) {

        $quantity = (float) $batch['quantity'];

        $batch['quantity'] =
            rtrim(
                rtrim(
                    number_format($quantity, 2, '.', ''),
                    '0'
                ),
                '.'
            ) . ' ton';
    }

    unset($batch);

    return $batches;
}


function findBatchById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            hb.id,
            hb.crop_id,
            c.name AS crop,
            c.farmer_id,
            f.name AS farmer,
            hb.harvest_date,
            hb.quantity,
            hb.qc_status,
            hb.location,
            hb.inventory_status
        FROM harvest_batches hb

        INNER JOIN crops c
            ON c.id = hb.crop_id

        INNER JOIN farmers f
            ON f.id = c.farmer_id

        WHERE hb.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $batch = $stmt->fetch();

    if (!$batch) {
        return null;
    }

    $quantity = (float) $batch['quantity'];

    $batch['quantity'] =
        rtrim(
            rtrim(
                number_format($quantity, 2, '.', ''),
                '0'
            ),
            '.'
        ) . ' ton';

    return $batch;
}