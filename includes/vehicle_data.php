<?php

require_once __DIR__ . '/../config/db.php';


function getAllVehicles()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            id,
            registration,
            type,
            capacity,
            driver,
            phone,
            status
        FROM vehicles
        ORDER BY id ASC
    ");

    $vehicles = $stmt->fetchAll();

    foreach ($vehicles as &$vehicle) {

        $capacity = (float) $vehicle['capacity'];

        $vehicle['capacity'] =
            rtrim(
                rtrim(
                    number_format($capacity, 2, '.', ''),
                    '0'
                ),
                '.'
            ) . ' ton';

        $vehicle['phone'] =
            $vehicle['phone'] ?? '';
    }

    unset($vehicle);

    return $vehicles;
}


function findVehicleById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            id,
            registration,
            type,
            capacity,
            driver,
            phone,
            status
        FROM vehicles
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        return null;
    }

    $capacity = (float) $vehicle['capacity'];

    $vehicle['capacity'] =
        rtrim(
            rtrim(
                number_format($capacity, 2, '.', ''),
                '0'
            ),
            '.'
        ) . ' ton';

    $vehicle['phone'] =
        $vehicle['phone'] ?? '';

    return $vehicle;
}