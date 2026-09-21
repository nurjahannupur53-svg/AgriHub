<?php

require_once __DIR__ . '/../config/db.php';


/*
|--------------------------------------------------------------------------
| Get Price Trend Data
|--------------------------------------------------------------------------
*/

function getPriceTrendData()
{
    global $pdo;

    /*
    |--------------------------------------------------------------------------
    | Load All Price History
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT
            pt.crop_id,
            c.name AS crop,
            pt.price_date,
            pt.price,
            pt.unit

        FROM price_trends pt

        INNER JOIN crops c
            ON c.id = pt.crop_id

        ORDER BY
            c.name ASC,
            pt.price_date ASC
    ");

    $rows = $stmt->fetchAll();

    $data = [];


    /*
    |--------------------------------------------------------------------------
    | Group Prices By Crop
    |--------------------------------------------------------------------------
    */

    foreach ($rows as $row) {

        $crop = $row['crop'];

        if (!isset($data[$crop])) {

            $data[$crop] = [
                'unit' => $row['unit'],
                'current' => 0,
                'change' => 0,
                'prices' => []
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Format Date Like: Sep 2024
        |--------------------------------------------------------------------------
        */

        $label = date(
            'M Y',
            strtotime($row['price_date'])
        );


        $data[$crop]['prices'][$label] =
            (float) $row['price'];

        $data[$crop]['unit'] =
            $row['unit'];
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Current Price + Latest Monthly Change
    |--------------------------------------------------------------------------
    */

    foreach ($data as &$cropData) {

        $prices = array_values(
            $cropData['prices']
        );

        $priceCount = count($prices);


        if ($priceCount > 0) {

            $current =
                (float) $prices[$priceCount - 1];

            $cropData['current'] = $current;


            /*
            |--------------------------------------------------------------------------
            | Compare Latest Price With Previous Price
            |--------------------------------------------------------------------------
            */

            if ($priceCount >= 2) {

                $previous =
                    (float) $prices[$priceCount - 2];

                if ($previous > 0) {

                    $change =
                        (($current - $previous) / $previous)
                        * 100;

                    $cropData['change'] =
                        round($change, 1);
                }
            }
        }
    }

    unset($cropData);


    return $data;
}