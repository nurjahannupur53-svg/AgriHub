<?php

require_once __DIR__ . '/../config/db.php';


function getAllQualityChecks()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            qc.id,
            qc.batch_id,
            c.name AS crop,
            qc.inspector,
            qc.inspection_date AS date,
            qc.grade,
            qc.moisture,
            qc.result
        FROM quality_checks qc

        INNER JOIN harvest_batches hb
            ON hb.id = qc.batch_id

        INNER JOIN crops c
            ON c.id = hb.crop_id

        ORDER BY qc.id ASC
    ");

    $checks = $stmt->fetchAll();

    foreach ($checks as &$check) {

        $moisture = (float) $check['moisture'];

        $check['moisture'] =
            rtrim(
                rtrim(
                    number_format(
                        $moisture,
                        2,
                        '.',
                        ''
                    ),
                    '0'
                ),
                '.'
            ) . '%';
    }

    unset($check);

    return $checks;
}


function findQualityCheckByBatch($batchId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            qc.id,
            qc.batch_id,
            c.name AS crop,
            qc.inspector,
            qc.inspection_date AS date,
            qc.grade,
            qc.moisture,
            qc.result
        FROM quality_checks qc

        INNER JOIN harvest_batches hb
            ON hb.id = qc.batch_id

        INNER JOIN crops c
            ON c.id = hb.crop_id

        WHERE qc.batch_id = ?

        LIMIT 1
    ");

    $stmt->execute([$batchId]);

    $check = $stmt->fetch();

    if (!$check) {
        return null;
    }

    $moisture = (float) $check['moisture'];

    $check['moisture'] =
        rtrim(
            rtrim(
                number_format(
                    $moisture,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        ) . '%';

    return $check;
}