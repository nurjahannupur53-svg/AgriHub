<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header('Location: notification.php');
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Find Notification
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            link,
            is_read
        FROM notifications
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $notification = $stmt->fetch();

    if (!$notification) {

        $pdo->rollBack();

        header(
            'Location: notification.php?error=notfound'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Notification As Read
    |--------------------------------------------------------------------------
    */

    if (!(bool) $notification['is_read']) {

        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = ?
        ");

        $stmt->execute([$id]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Internal Project Link
    |--------------------------------------------------------------------------
    */

    $link = trim($notification['link'] ?? '');

    if (
        $link === '' ||
        strpos($link, '://') !== false ||
        strpos($link, '//') === 0 ||
        strpos($link, "\r") !== false ||
        strpos($link, "\n") !== false
    ) {
        $link = 'notification.php';
    }


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Redirect To Notification Target
    |--------------------------------------------------------------------------
    */

    header('Location: ' . $link);
    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: notification.php?error=database'
    );

    exit;
}