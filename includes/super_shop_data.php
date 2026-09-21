<?php

require_once __DIR__ . '/../config/db.php';


function getAllSuperShopOrders()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            sso.id,
            sso.shop,
            sso.product,
            sso.crop_id,
            c.name AS crop,
            sso.quantity,
            sso.unit,
            sso.order_date,
            sso.delivery_date,
            sso.status

        FROM super_shop_orders sso

        INNER JOIN crops c
            ON c.id = sso.crop_id

        ORDER BY sso.id ASC
    ");

    return $stmt->fetchAll();
}


function findSuperShopOrderById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            sso.id,
            sso.shop,
            sso.product,
            sso.crop_id,
            c.name AS crop,
            sso.quantity,
            sso.unit,
            sso.order_date,
            sso.delivery_date,
            sso.status

        FROM super_shop_orders sso

        INNER JOIN crops c
            ON c.id = sso.crop_id

        WHERE sso.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $order = $stmt->fetch();

    return $order ?: null;
}