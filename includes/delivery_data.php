<?php

require_once __DIR__ . '/../config/db.php';


function getAllDeliveries()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            d.id,
            d.order_id,
            mo.buyer,
            v.driver,
            d.vehicle_id,
            v.registration AS vehicle,
            d.delivery_date,
            d.status

        FROM deliveries d

        INNER JOIN market_orders mo
            ON mo.id = d.order_id

        INNER JOIN vehicles v
            ON v.id = d.vehicle_id

        ORDER BY d.id ASC
    ");

    return $stmt->fetchAll();
}


function findDeliveryById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            d.id,
            d.order_id,
            mo.buyer,
            v.driver,
            d.vehicle_id,
            v.registration AS vehicle,
            d.delivery_date,
            d.status

        FROM deliveries d

        INNER JOIN market_orders mo
            ON mo.id = d.order_id

        INNER JOIN vehicles v
            ON v.id = d.vehicle_id

        WHERE d.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $delivery = $stmt->fetch();

    return $delivery ?: null;
}


function findDeliveryByOrder($orderId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            d.id,
            d.order_id,
            mo.buyer,
            v.driver,
            d.vehicle_id,
            v.registration AS vehicle,
            d.delivery_date,
            d.status

        FROM deliveries d

        INNER JOIN market_orders mo
            ON mo.id = d.order_id

        INNER JOIN vehicles v
            ON v.id = d.vehicle_id

        WHERE d.order_id = ?

        LIMIT 1
    ");

    $stmt->execute([$orderId]);

    $delivery = $stmt->fetch();

    return $delivery ?: null;
}