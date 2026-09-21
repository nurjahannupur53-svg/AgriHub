<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';

$displayName = $_SESSION['full_name'] ?? 'Admin User';
$displayRole = $_SESSION['role'] ?? 'Administrator';


/*
|--------------------------------------------------------------------------
| Profile Initials
|--------------------------------------------------------------------------
*/

$nameParts = preg_split(
    '/\s+/',
    trim($displayName)
);

$initials = '';

foreach (
    array_slice($nameParts, 0, 2)
    as $part
) {
    if ($part !== '') {
        $initials .= strtoupper(
            substr($part, 0, 1)
        );
    }
}

if ($initials === '') {
    $initials = 'AU';
}


/*
|--------------------------------------------------------------------------
| Notification Count
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/notification_data.php';

$headerUnreadCount =
    getUnreadNotificationCount();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?> | AgriHub
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body class="app-body">

<div class="app-layout">


    <?php
    include __DIR__ . '/sidebar.php';
    ?>


    <main class="main-area">


        <!-- ==============================================
             TOP BAR
        =============================================== -->

        <header class="topbar">


            <!-- MOBILE MENU -->

            <div class="mobile-menu-area">

                <button
                    type="button"
                    class="mobile-menu-btn"
                    id="mobileMenuButton"
                    aria-label="Open menu"
                >
                    ☰
                </button>

            </div>


            <!-- GLOBAL SEARCH -->

            <div class="topbar-search">

                <span class="search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    id="globalSearch"
                    placeholder="Search farmers, crops, orders..."
                    autocomplete="off"
                >

            </div>


            <!-- TOP BAR ACTIONS -->

            <div class="topbar-actions">


                <!-- NOTIFICATIONS -->

                <a
                    href="notification.php"
                    class="topbar-notification"
                    title="Notifications"
                    aria-label="Notifications"
                >

                    <span class="topbar-bell">
                        🔔
                    </span>


                    <?php if ($headerUnreadCount > 0): ?>

                        <span class="topbar-notification-count">

                            <?= $headerUnreadCount > 9
                                ? '9+'
                                : (int) $headerUnreadCount ?>

                        </span>

                    <?php endif; ?>

                </a>


                <!-- PROFILE MENU -->

                <div class="profile-menu">

                    <button
                        type="button"
                        class="profile-button"
                        id="profileMenuButton"
                        aria-label="Open profile menu"
                    >

                        <span class="profile-avatar">

                            <?= htmlspecialchars($initials) ?>

                        </span>


                        <span class="profile-text">

                            <strong>

                                <?= htmlspecialchars(
                                    $displayName
                                ) ?>

                            </strong>

                            <small>

                                <?= htmlspecialchars(
                                    $displayRole
                                ) ?>

                            </small>

                        </span>


                        <span class="profile-chevron">
                            ▾
                        </span>

                    </button>


                    <!-- PROFILE DROPDOWN -->

                    <div
                        class="profile-dropdown"
                        id="profileDropdown"
                    >

                        <a href="profile.php">
                            Profile / Settings
                        </a>

                        <a href="notification.php">
                            Notifications
                        </a>


                        <div class="dropdown-divider"></div>


                        <a
                            href="auth/logout.php"
                            class="dropdown-logout"
                        >
                            Logout
                        </a>

                    </div>

                </div>

            </div>

        </header>


        <!-- ==============================================
             PAGE CONTENT
        =============================================== -->

        <section class="page-content">


            <!-- PAGE HEADING -->

            <div class="page-heading">

                <div>

                    <h1>
                        <?= htmlspecialchars($pageTitle) ?>
                    </h1>


                    <?php if ($pageSubtitle !== ''): ?>

                        <p>

                            <?= htmlspecialchars(
                                $pageSubtitle
                            ) ?>

                        </p>

                    <?php endif; ?>

                </div>

            </div>