<?php

require_once __DIR__ . '/../config/db.php';


function getAllInventory()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            i.id,
            i.batch_id,
            c.name AS crop,
            i.total,
            i.available,
            i.reserved,
            i.location,
            i.status,
            i.threshold
        FROM inventory i

        INNER JOIN harvest_batches hb
            ON hb.id = i.batch_id

        INNER JOIN crops c
            ON c.id = hb.crop_id

        ORDER BY i.id ASC
    ");

    return $stmt->fetchAll();
}


function findInventoryById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            i.id,
            i.batch_id,
            c.name AS crop,
            i.total,
            i.available,
            i.reserved,
            i.location,
            i.status,
            i.threshold
        FROM inventory i

        INNER JOIN harvest_batches hb
            ON hb.id = i.batch_id

        INNER JOIN crops c
            ON c.id = hb.crop_id

        WHERE i.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $item = $stmt->fetch();

    return $item ?: null;
}


function findInventoryByBatch($batchId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            i.id,
            i.batch_id,
            c.name AS crop,
            i.total,
            i.available,
            i.reserved,
            i.location,
            i.status,
            i.threshold
        FROM inventory i

        INNER JOIN harvest_batches hb
            ON hb.id = i.batch_id

        INNER JOIN crops c
            ON c.id = hb.crop_id

        WHERE i.batch_id = ?

        LIMIT 1
    ");

    $stmt->execute([$batchId]);

    $item = $stmt->fetch();

    return $item ?: null;
}