<?php

require_once __DIR__ . '/../config/db.php';


function getAdminProfile()
{
    global $pdo;

    $profile = [
        'name' => 'Admin User',
        'role' => 'Administrator',
        'email' => '',
        'phone' => '',
        'location' => 'AgriHub HQ',
        'joined' => ''
    ];


    /*
    |--------------------------------------------------------------------------
    | Logged-in User Required
    |--------------------------------------------------------------------------
    */

    if (empty($_SESSION['user_id'])) {
        return $profile;
    }


    /*
    |--------------------------------------------------------------------------
    | Load Profile Directly From Database
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            full_name,
            email,
            phone,
            location,
            role,
            created_at
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $_SESSION['user_id']
    ]);

    $user = $stmt->fetch();

    if (!$user) {
        return $profile;
    }


    $profile['name'] =
        $user['full_name'] ?? 'Admin User';

    $profile['email'] =
        $user['email'] ?? '';

    $profile['phone'] =
        $user['phone'] ?? '';

    $profile['location'] =
        $user['location'] ?? 'AgriHub HQ';

    $profile['role'] =
        $user['role'] ?? 'Administrator';


    /*
    |--------------------------------------------------------------------------
    | Member Since = Original Registration Date
    |--------------------------------------------------------------------------
    */

    if (!empty($user['created_at'])) {

        $profile['joined'] = date(
            'Y-m-d',
            strtotime($user['created_at'])
        );
    }


    return $profile;
}