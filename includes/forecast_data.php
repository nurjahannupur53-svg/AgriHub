<?php

require_once __DIR__ . '/../config/db.php';


function getDemandForecasts()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            df.id,
            df.crop_id,
            c.name AS crop,
            df.current_demand AS current,
            df.predicted_demand AS predicted,
            df.confidence,
            df.region

        FROM demand_forecasts df

        INNER JOIN crops c
            ON c.id = df.crop_id

        ORDER BY df.id ASC
    ");

    return $stmt->fetchAll();
}


function findForecastByCrop($cropName)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            df.id,
            df.crop_id,
            c.name AS crop,
            df.current_demand AS current,
            df.predicted_demand AS predicted,
            df.confidence,
            df.region

        FROM demand_forecasts df

        INNER JOIN crops c
            ON c.id = df.crop_id

        WHERE LOWER(c.name) = LOWER(?)

        LIMIT 1
    ");

    $stmt->execute([$cropName]);

    $forecast = $stmt->fetch();

    return $forecast ?: null;
}