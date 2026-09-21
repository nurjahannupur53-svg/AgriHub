<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';


/*
|--------------------------------------------------------------------------
| Mark All Notifications As Read
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['all']) &&
    $_GET['all'] === '1'
) {

    try {

        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE is_read = 0
        ");

        $stmt->execute();

        header(
            'Location: notification.php?success=all-read'
        );

        exit;

    } catch (Throwable $e) {

        header(
            'Location: notification.php?error=database'
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Mark One Notification As Read
|--------------------------------------------------------------------------
*/

$id = trim($_GET['id'] ?? '');

if ($id === '') {

    header('Location: notification.php');
    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Check Notification Exists
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM notifications
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $notification = $stmt->fetch();

    if (!$notification) {

        header(
            'Location: notification.php?error=notfound'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Mark As Read
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE id = ?
    ");

    $stmt->execute([$id]);


    header(
        'Location: notification.php?success=read'
    );

    exit;


} catch (Throwable $e) {

    header(
        'Location: notification.php?error=database'
    );

    exit;
}