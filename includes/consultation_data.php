<?php

require_once __DIR__ . '/../config/db.php';


function getAllConsultations()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            con.id,
            con.farmer_id,
            f.name AS farmer,
            con.expert,
            con.topic,
            con.consultation_date AS date,
            con.status

        FROM consultations con

        INNER JOIN farmers f
            ON f.id = con.farmer_id

        ORDER BY con.id ASC
    ");

    return $stmt->fetchAll();
}


function findConsultationById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            con.id,
            con.farmer_id,
            f.name AS farmer,
            con.expert,
            con.topic,
            con.consultation_date AS date,
            con.status

        FROM consultations con

        INNER JOIN farmers f
            ON f.id = con.farmer_id

        WHERE con.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    $consultation = $stmt->fetch();

    return $consultation ?: null;
}