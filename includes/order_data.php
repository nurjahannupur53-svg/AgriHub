<?php

require_once __DIR__ . '/../config/db.php';


function getAllMarketOrders()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            mo.id,
            mo.inventory_id,
            i.batch_id,
            mo.buyer,
            c.name AS crop,
            mo.quantity,
            mo.amount,
            mo.order_date,
            mo.delivery_date,
            mo.payment,
            mo.status

        FROM market_orders mo

        INNER JOIN inventory i
            ON i.id = mo.inventory_id

        INNER JOIN harvest_batches hb
            ON hb.id = i.batch_id

        INNER JOIN crops c
            ON c.id = hb.crop_id

        ORDER BY mo.id ASC
    ");

    return $stmt->fetchAll();
}


function findMarketOrderById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            mo.id,
            mo.inventory_id,
            i.batch_id,
            mo.buyer,
            c.name AS crop,
            mo.quantity,
            mo.amount,
            mo.order_date,
            mo.delivery_date,
            mo.payment,
            mo.status

        FROM market_orders mo

        INNER JOIN inventory i
            ON i.id = mo.inventory_id

        INNER JOIN harvest_batches hb
            ON hb.id = i.batch_id

        INNER JOIN crops c
            ON c.id = hb.crop_id

        WHERE mo.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $order = $stmt->fetch();

    return $order ?: null;
}