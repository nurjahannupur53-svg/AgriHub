<?php

require_once __DIR__ . '/../config/db.php';


/*
|--------------------------------------------------------------------------
| Convert Database Date Into Relative Time
|--------------------------------------------------------------------------
*/

function notificationTimeAgo($dateTime)
{
    if (!$dateTime) {
        return '';
    }

    try {

        $created = new DateTime($dateTime);
        $now = new DateTime();

        $seconds = $now->getTimestamp() - $created->getTimestamp();

        if ($seconds < 0) {
            $seconds = 0;
        }

        if ($seconds < 60) {
            return 'Just now';
        }

        $minutes = (int) floor($seconds / 60);

        if ($minutes < 60) {
            return $minutes . ' minute' .
                ($minutes === 1 ? '' : 's') .
                ' ago';
        }

        $hours = (int) floor($minutes / 60);

        if ($hours < 24) {
            return $hours . ' hour' .
                ($hours === 1 ? '' : 's') .
                ' ago';
        }

        $days = (int) floor($hours / 24);

        if ($days < 7) {
            return $days . ' day' .
                ($days === 1 ? '' : 's') .
                ' ago';
        }

        $weeks = (int) floor($days / 7);

        if ($weeks < 5) {
            return $weeks . ' week' .
                ($weeks === 1 ? '' : 's') .
                ' ago';
        }

        $months = (int) floor($days / 30);

        if ($months < 12) {
            return $months . ' month' .
                ($months === 1 ? '' : 's') .
                ' ago';
        }

        $years = (int) floor($days / 365);

        return $years . ' year' .
            ($years === 1 ? '' : 's') .
            ' ago';

    } catch (Throwable $e) {

        return '';
    }
}


/*
|--------------------------------------------------------------------------
| Get All Notifications
|--------------------------------------------------------------------------
*/

function getAllNotifications()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT
            id,
            title,
            message,
            type,
            is_read,
            link,
            created_at
        FROM notifications
        ORDER BY created_at DESC, id DESC
    ");

    $notifications = $stmt->fetchAll();

    foreach ($notifications as &$notification) {

        /*
        |--------------------------------------------------------------------------
        | Keep Existing UI Key Names
        |--------------------------------------------------------------------------
        */

        $notification['read'] =
            (bool) $notification['is_read'];

        $notification['time'] =
            notificationTimeAgo(
                $notification['created_at']
            );
    }

    unset($notification);

    return $notifications;
}


/*
|--------------------------------------------------------------------------
| Find Notification By ID
|--------------------------------------------------------------------------
*/

function findNotificationById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            message,
            type,
            is_read,
            link,
            created_at
        FROM notifications
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $notification = $stmt->fetch();

    if (!$notification) {
        return null;
    }

    $notification['read'] =
        (bool) $notification['is_read'];

    $notification['time'] =
        notificationTimeAgo(
            $notification['created_at']
        );

    return $notification;
}


/*
|--------------------------------------------------------------------------
| Get Unread Notification Count
|--------------------------------------------------------------------------
*/

function getUnreadNotificationCount()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM notifications
        WHERE is_read = 0
    ");

    return (int) $stmt->fetchColumn();
}