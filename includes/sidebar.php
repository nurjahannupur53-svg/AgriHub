<?php
$currentPage = basename($_SERVER['PHP_SELF']);

function activePage($pages)
{
    global $currentPage;
    return in_array($currentPage, $pages) ? 'active' : '';
}
?>

<aside class="sidebar">

    <a href="dashboard.php" class="sidebar-brand">
        <div class="sidebar-brand-icon">🌾</div>

        <div>
            <h2>AgriHub</h2>
            <span>Supply Chain</span>
        </div>
    </a>

    <nav class="sidebar-menu">

        <a href="dashboard.php"
           class="<?= activePage(['dashboard.php']) ?>">
            <span class="menu-icon">▦</span>
            <span>Dashboard</span>
        </a>

        <a href="farmers.php"
           class="<?= activePage([
    'farmers.php',
    'farmer_add.php',
    'farmer_details.php',
    'farmer_edit.php'
]) ?>">
            <span class="menu-icon">♙</span>
            <span>Farmers</span>
        </a>

        <a href="crops.php"
           class="<?= activePage([
    'crops.php',
    'crop_add.php',
    'crop_details.php',
    'crop_edit.php'
]) ?>">
            <span class="menu-icon">♧</span>
            <span>Crops</span>
        </a>

        <a href="harvest_batches.php"
           class="<?= activePage([
    'harvest_batches.php',
    'batch_add.php',
    'batch_details.php'
]) ?>">
            <span class="menu-icon">□</span>
            <span>Harvest Batches</span>
        </a>

        <a href="quality_checks.php"
           class="<?= activePage([
    'quality_checks.php',
    'qc_add.php'
]) ?>">
            <span class="menu-icon">✓</span>
            <span>Quality Checks</span>
        </a>

        <a href="inventory.php"
           class="<?= activePage([
    'inventory.php',
    'inventory_details.php',
    'inventory_update.php'
]) ?>">
            <span class="menu-icon">▣</span>
            <span>Inventory</span>
        </a>

        <a href="demand_forecast.php"
           class="<?= activePage(['demand_forecast.php']) ?>">
            <span class="menu-icon">⌁</span>
            <span>Demand Forecast</span>
        </a>

        <a href="price_trends.php"
           class="<?= activePage(['price_trends.php']) ?>">
            <span class="menu-icon">$</span>
            <span>Price Trends</span>
        </a>

        <a href="market_orders.php"
           class="<?= activePage([
    'market_orders.php',
    'market_order_details.php',
    'market_order_add.php'
]) ?>">
            <span class="menu-icon">🛒</span>
            <span>Market Orders</span>
        </a>

        <a href="super_shop_orders.php"
           class="<?= activePage([
    'super_shop_orders.php',
    'super_shop_order_details.php',
    'super_shop_order_update.php'
]) ?>">
            <span class="menu-icon">▤</span>
            <span>Super Shop Orders</span>
        </a>

        <a href="equipment_booking.php"
           class="<?= activePage([
    'equipment_booking.php',
    'equipment_details.php',
    'equipment_update.php'
]) ?>">
            <span class="menu-icon">⚙</span>
            <span>Equipment Booking</span>
        </a>

        <a href="consultation.php"
           class="<?= activePage([
    'consultation.php',
    'consultation_details.php',
    'consultation_update.php'
]) ?>">
            <span class="menu-icon">◌</span>
            <span>Consultation</span>
        </a>

        <a href="deliveries.php"
           class="<?= activePage([
    'deliveries.php',
    'delivery_details.php',
    'delivery_update.php'
]) ?>">
            <span class="menu-icon">▰</span>
            <span>Deliveries</span>
        </a>

        <a href="vehicles.php"
           class="<?= activePage([
    'vehicles.php',
    'vehicle_details.php',
    'vehicle_edit.php'
]) ?>">
            <span class="menu-icon">▱</span>
            <span>Vehicles</span>
        </a>

        <a href="iot_telematics.php"
           class="<?= activePage(['iot_telematics.php']) ?>">
            <span class="menu-icon">⌁</span>
            <span>IoT Telematics</span>
        </a>

    </nav>

    <div class="sidebar-bottom">

        <a href="notification.php"
   class="<?= activePage([
       'notification.php',
       'notification_open.php',
       'notification_read.php'
   ]) ?>">
    <span class="menu-icon">♢</span>
    <span>Notifications</span>
</a>

        <a href="profile.php"
           class="<?= activePage([
    'profile.php',
    'profile_edit.php',
    'change_password.php'
]) ?>">
            <span class="menu-icon">○</span>
            <span>Profile / Settings</span>
        </a>

        <a href="auth/logout.php" class="logout-link">
            <span class="menu-icon">↪</span>
            <span>Logout</span>
        </a>

    </div>

</aside>