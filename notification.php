<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/notification_data.php';

$notifications = getAllNotifications();

$filter = trim($_GET['filter'] ?? 'all');

if (!in_array($filter, ['all', 'unread'], true)) {
    $filter = 'all';
}

$success = trim($_GET['success'] ?? '');

$unreadCount = getUnreadNotificationCount();

$pageTitle = 'Notifications';
$pageSubtitle =
    $unreadCount . ' unread notification' .
    ($unreadCount === 1 ? '' : 's');

require_once __DIR__ . '/includes/header.php';

?>

<?php if ($success === 'read'): ?>

    <div class="flash-message flash-success">
        Notification marked as read.
    </div>

<?php elseif ($success === 'all-read'): ?>

    <div class="flash-message flash-success">
        All notifications marked as read.
    </div>

<?php endif; ?>


<div class="notification-toolbar">

    <div class="notification-tabs">

        <a
            href="notification.php?filter=all"
            class="notification-tab <?= $filter === 'all' ? 'active' : '' ?>"
        >
            All
            <span><?= count($notifications) ?></span>
        </a>

        <a
            href="notification.php?filter=unread"
            class="notification-tab <?= $filter === 'unread' ? 'active' : '' ?>"
        >
            Unread
            <span><?= $unreadCount ?></span>
        </a>

    </div>


    <?php if ($unreadCount > 0): ?>

        <a
            href="notification_read.php?all=1"
            class="btn btn-light"
        >
            ✓ Mark All as Read
        </a>

    <?php endif; ?>

</div>


<div class="notification-list">

    <?php

    $visibleCount = 0;

    foreach ($notifications as $notification):

        if (
            $filter === 'unread' &&
            $notification['read']
        ) {
            continue;
        }

        $visibleCount++;

        $icon = '🔔';

        switch ($notification['type']) {

            case 'harvest':
                $icon = '🌾';
                break;

            case 'quality':
                $icon = '✓';
                break;

            case 'inventory':
                $icon = '▦';
                break;

            case 'order':
                $icon = '🛒';
                break;

            case 'delivery':
                $icon = '🚚';
                break;

            case 'equipment':
                $icon = '⚙';
                break;

            case 'consultation':
                $icon = '☏';
                break;
        }

    ?>

        <div
            class="notification-item <?= !$notification['read'] ? 'unread' : '' ?>"
        >

            <div class="notification-icon <?= htmlspecialchars($notification['type']) ?>">
                <?= $icon ?>
            </div>


            <div class="notification-content">

                <div class="notification-title-row">

                    <div>

                        <h3>
                            <?= htmlspecialchars($notification['title']) ?>
                        </h3>

                        <?php if (!$notification['read']): ?>

                            <span class="notification-new-badge">
                                NEW
                            </span>

                        <?php endif; ?>

                    </div>

                    <span class="notification-time">
                        <?= htmlspecialchars($notification['time']) ?>
                    </span>

                </div>


                <p>
                    <?= htmlspecialchars($notification['message']) ?>
                </p>


                <div class="notification-actions">

                    <a
                        href="notification_open.php?id=<?= urlencode($notification['id']) ?>"
                        class="action-link view"
                    >
                        View Details
                    </a>


                    <?php if (!$notification['read']): ?>

                        <a
                            href="notification_read.php?id=<?= urlencode($notification['id']) ?>"
                            class="action-link notification-read-link"
                        >
                            Mark as Read
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    <?php endforeach; ?>


    <?php if ($visibleCount === 0): ?>

        <div class="notification-empty">

            <div class="notification-empty-icon">
                ✓
            </div>

            <h3>You're all caught up</h3>

            <p>
                There are no unread notifications right now.
            </p>

            <a
                href="notification.php?filter=all"
                class="btn btn-light"
            >
                View All Notifications
            </a>

        </div>

    <?php endif; ?>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>