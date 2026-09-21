<?php

require_once __DIR__ . '/../config/db.php';


/*
|--------------------------------------------------------------------------
| Get All IoT Devices
|--------------------------------------------------------------------------
*/

function getAllIoTDevices()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            i.id,
            i.vehicle_id,

            v.registration,
            v.type AS vehicle_type,

            i.active,
            i.speed,
            i.temperature,
            i.humidity,
            i.engine,
            i.latitude,
            i.longitude,

            i.delivery_id,

            CASE
                WHEN i.delivery_id IS NULL
                    THEN 'No active delivery'

                WHEN mo.buyer IS NULL
                    THEN 'No active delivery'

                ELSE mo.buyer
            END AS destination,

            DATE_FORMAT(
                i.last_update,
                '%Y-%m-%d %H:%i'
            ) AS last_update

        FROM iot_devices i

        INNER JOIN vehicles v
            ON v.id = i.vehicle_id

        LEFT JOIN deliveries d
            ON d.id = i.delivery_id

        LEFT JOIN market_orders mo
            ON mo.id = d.order_id

        ORDER BY i.id ASC
    ");

    $devices = $stmt->fetchAll();

    /*
    |--------------------------------------------------------------------------
    | Match Existing UI Data Types
    |--------------------------------------------------------------------------
    */

    foreach ($devices as &$device) {

        $device['active'] =
            (bool) $device['active'];

        $device['speed'] =
            (float) $device['speed'];

        $device['temperature'] =
            $device['temperature'] !== null
                ? (float) $device['temperature']
                : null;

        $device['humidity'] =
            $device['humidity'] !== null
                ? (float) $device['humidity']
                : null;

        $device['latitude'] =
            $device['latitude'] !== null
                ? (float) $device['latitude']
                : null;

        $device['longitude'] =
            $device['longitude'] !== null
                ? (float) $device['longitude']
                : null;

        $device['delivery_id'] =
            $device['delivery_id'] ?? '';
    }

    unset($device);

    return $devices;
}


/*
|--------------------------------------------------------------------------
| Find IoT Device By Vehicle
|--------------------------------------------------------------------------
*/

function findIoTByVehicle($vehicleId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            i.id,
            i.vehicle_id,

            v.registration,
            v.type AS vehicle_type,

            i.active,
            i.speed,
            i.temperature,
            i.humidity,
            i.engine,
            i.latitude,
            i.longitude,

            i.delivery_id,

            CASE
                WHEN i.delivery_id IS NULL
                    THEN 'No active delivery'

                WHEN mo.buyer IS NULL
                    THEN 'No active delivery'

                ELSE mo.buyer
            END AS destination,

            DATE_FORMAT(
                i.last_update,
                '%Y-%m-%d %H:%i'
            ) AS last_update

        FROM iot_devices i

        INNER JOIN vehicles v
            ON v.id = i.vehicle_id

        LEFT JOIN deliveries d
            ON d.id = i.delivery_id

        LEFT JOIN market_orders mo
            ON mo.id = d.order_id

        WHERE i.vehicle_id = ?

        LIMIT 1
    ");

    $stmt->execute([$vehicleId]);

    $device = $stmt->fetch();

    if (!$device) {
        return null;
    }

    $device['active'] =
        (bool) $device['active'];

    $device['speed'] =
        (float) $device['speed'];

    $device['temperature'] =
        $device['temperature'] !== null
            ? (float) $device['temperature']
            : null;

    $device['humidity'] =
        $device['humidity'] !== null
            ? (float) $device['humidity']
            : null;

    $device['latitude'] =
        $device['latitude'] !== null
            ? (float) $device['latitude']
            : null;

    $device['longitude'] =
        $device['longitude'] !== null
            ? (float) $device['longitude']
            : null;

    $device['delivery_id'] =
        $device['delivery_id'] ?? '';

    return $device;
}


/*
|--------------------------------------------------------------------------
| Get IoT Event Log
|--------------------------------------------------------------------------
*/

function getIoTEventLog()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            DATE_FORMAT(
                e.event_time,
                '%H:%i'
            ) AS time,

            v.registration AS vehicle,

            e.event_type AS type,

            e.message,

            e.level

        FROM iot_event_logs e

        INNER JOIN iot_devices i
            ON i.id = e.device_id

        INNER JOIN vehicles v
            ON v.id = i.vehicle_id

        ORDER BY
            e.event_time DESC,
            e.id DESC
    ");

    return $stmt->fetchAll();
}