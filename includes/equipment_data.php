<?php

require_once __DIR__ . '/../config/db.php';


function getAllEquipmentBookings()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            eb.id,
            eb.equipment,
            eb.purpose,
            eb.farmer_id,
            f.name AS farmer,
            eb.start_date,
            eb.end_date,
            eb.status

        FROM equipment_bookings eb

        INNER JOIN farmers f
            ON f.id = eb.farmer_id

        ORDER BY eb.id ASC
    ");

    return $stmt->fetchAll();
}


function findEquipmentBookingById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            eb.id,
            eb.equipment,
            eb.purpose,
            eb.farmer_id,
            f.name AS farmer,
            eb.start_date,
            eb.end_date,
            eb.status

        FROM equipment_bookings eb

        INNER JOIN farmers f
            ON f.id = eb.farmer_id

        WHERE eb.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $booking = $stmt->fetch();

    return $booking ?: null;
}