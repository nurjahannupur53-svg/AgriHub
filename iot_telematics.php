<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/iot_data.php';

$devices = getAllIoTDevices();
$events = getIoTEventLog();

$selectedVehicle = trim($_GET['vehicle'] ?? '');

if ($selectedVehicle !== '' && !findIoTByVehicle($selectedVehicle)) {
    $selectedVehicle = '';
}

$pageTitle = 'IoT Telematics';
$pageSubtitle = 'Live vehicle monitoring and sensor telemetry';

require_once __DIR__ . '/includes/header.php';

?>

<div class="iot-live-banner">

    <div class="iot-live-left">

        <span class="iot-live-dot"></span>

        <div>
            <strong>Live Telematics</strong>
            <small>
                Simulated sensor data refreshes every 3 seconds
            </small>
        </div>

    </div>

    <div class="iot-last-refresh">
        Last refresh:
        <strong id="iotRefreshTime">--:--:--</strong>
    </div>

</div>


<div class="iot-summary-grid">

    <div class="iot-summary-card">
        <span>Total Devices</span>
        <strong><?= count($devices) ?></strong>
        <small>Connected IoT units</small>
    </div>

    <div class="iot-summary-card">
        <span>Vehicles Moving</span>
        <strong id="movingVehicleCount">1</strong>
        <small>Engine on & moving</small>
    </div>

    <div class="iot-summary-card">
        <span>Engines On</span>
        <strong id="engineOnCount">1</strong>
        <small>Currently running</small>
    </div>

    <div class="iot-summary-card">
        <span>Active Deliveries</span>
        <strong>1</strong>
        <small>Live delivery tracking</small>
    </div>

</div>


<div class="iot-device-grid">

    <?php foreach ($devices as $device): ?>

        <div
            class="iot-device-card <?= $selectedVehicle === $device['vehicle_id'] ? 'iot-device-selected' : '' ?>"
            data-iot-device
            data-active="<?= $device['active'] ? '1' : '0' ?>"
            data-vehicle="<?= htmlspecialchars($device['vehicle_id']) ?>"
        >

            <div class="iot-device-header">

                <div>

                    <div class="iot-device-title">

                        <h3>
                            <?= htmlspecialchars($device['registration']) ?>
                        </h3>

                        <span class="iot-device-state <?= $device['active'] ? 'online' : 'offline' ?>">
                            <?= $device['active'] ? 'LIVE' : 'IDLE' ?>
                        </span>

                    </div>

                    <p>
                        <?= htmlspecialchars($device['vehicle_type']) ?>
                        ·
                        <?= htmlspecialchars($device['id']) ?>
                    </p>

                </div>

                <a
                    href="vehicle_details.php?id=<?= urlencode($device['vehicle_id']) ?>"
                    class="action-link view"
                >
                    Vehicle
                </a>

            </div>


            <div class="iot-sensor-grid">

                <div class="iot-sensor">

                    <span>Speed</span>

                    <strong>
                        <span
                            class="iot-speed"
                            data-base="<?= (float) $device['speed'] ?>"
                        >
                            <?= (float) $device['speed'] ?>
                        </span>
                        <small>km/h</small>
                    </strong>

                </div>


                <div class="iot-sensor">

                    <span>Temperature</span>

                    <strong>
                        <span
                            class="iot-temperature"
                            data-base="<?= (float) $device['temperature'] ?>"
                        >
                            <?= number_format($device['temperature'], 1) ?>
                        </span>
                        <small>°C</small>
                    </strong>

                </div>


                <div class="iot-sensor">

                    <span>Humidity</span>

                    <strong>
                        <span
                            class="iot-humidity"
                            data-base="<?= (int) $device['humidity'] ?>"
                        >
                            <?= (int) $device['humidity'] ?>
                        </span>
                        <small>%</small>
                    </strong>

                </div>


                <div class="iot-sensor">

                    <span>Engine</span>

                    <strong
                        class="iot-engine <?= $device['engine'] === 'On' ? 'engine-on' : '' ?>"
                    >
                        <?= htmlspecialchars($device['engine']) ?>
                    </strong>

                </div>

            </div>


            <div class="iot-location-box">

                <div>

                    <span>GPS Location</span>

                    <strong>
                        <span
                            class="iot-latitude"
                            data-base="<?= (float) $device['latitude'] ?>"
                        >
                            <?= number_format($device['latitude'], 4) ?>
                        </span>°N,
                        <span
                            class="iot-longitude"
                            data-base="<?= (float) $device['longitude'] ?>"
                        >
                            <?= number_format($device['longitude'], 4) ?>
                        </span>°E
                    </strong>

                </div>

                <div>

                    <span>Last Recorded</span>

                    <strong class="iot-card-update">
                        <?= htmlspecialchars($device['last_update']) ?>
                    </strong>

                </div>

            </div>


            <div class="iot-delivery-info">

                <?php if ($device['delivery_id'] !== ''): ?>

                    <div>

                        <span>Linked Delivery</span>

                        <a
                            href="delivery_details.php?id=<?= urlencode($device['delivery_id']) ?>"
                        >
                            <?= htmlspecialchars($device['delivery_id']) ?>
                        </a>

                    </div>

                    <div>
                        <span>Destination</span>
                        <strong>
                            <?= htmlspecialchars($device['destination']) ?>
                        </strong>
                    </div>

                <?php else: ?>

                    <div>
                        <span>Delivery</span>
                        <strong>No active delivery</strong>
                    </div>

                <?php endif; ?>

            </div>

        </div>

    <?php endforeach; ?>

</div>


<div class="app-card iot-event-card">

    <div class="card-header">

        <div>
            <h3>Telematics Event Log</h3>
            <span class="card-subtitle">
                Recent vehicle and sensor activity
            </span>
        </div>

        <span class="iot-event-live">
            ● LIVE
        </span>

    </div>


    <div class="table-responsive">

        <table class="app-table">

            <thead>

            <tr>
                <th>Time</th>
                <th>Vehicle</th>
                <th>Event</th>
                <th>Message</th>
            </tr>

            </thead>

            <tbody id="iotEventBody">

            <?php foreach ($events as $event): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($event['time']) ?>
                    </td>

                    <td>
                        <strong>
                            <?= htmlspecialchars($event['vehicle']) ?>
                        </strong>
                    </td>

                    <td>

                        <span class="iot-event-type <?= htmlspecialchars($event['level']) ?>">
                            <?= htmlspecialchars($event['type']) ?>
                        </span>

                    </td>

                    <td>
                        <?= htmlspecialchars($event['message']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const cards =
        document.querySelectorAll("[data-iot-device]");

    const refreshTime =
        document.getElementById("iotRefreshTime");


    function randomBetween(min, max) {
        return Math.random() * (max - min) + min;
    }


    function updateClock() {

        const now = new Date();

        refreshTime.textContent =
            now.toLocaleTimeString();
    }


    function updateTelemetry() {

        let moving = 0;
        let engineOn = 0;

        cards.forEach(function (card) {

            const active =
                card.dataset.active === "1";

            const speed =
                card.querySelector(".iot-speed");

            const temperature =
                card.querySelector(".iot-temperature");

            const humidity =
                card.querySelector(".iot-humidity");

            const latitude =
                card.querySelector(".iot-latitude");

            const longitude =
                card.querySelector(".iot-longitude");

            const cardUpdate =
                card.querySelector(".iot-card-update");


            const baseSpeed =
                parseFloat(speed.dataset.base);

            const baseTemp =
                parseFloat(temperature.dataset.base);

            const baseHumidity =
                parseFloat(humidity.dataset.base);

            const baseLat =
                parseFloat(latitude.dataset.base);

            const baseLng =
                parseFloat(longitude.dataset.base);


            if (active) {

                /*
                 * Moving vehicle simulation
                 * approx. 62 - 66 km/h
                 */
                const newSpeed =
                    Math.max(
                        0,
                        baseSpeed + randomBetween(-2, 2)
                    );

                speed.textContent =
                    newSpeed.toFixed(0);

                temperature.textContent =
                    (
                        baseTemp +
                        randomBetween(-0.2, 0.2)
                    ).toFixed(1);

                humidity.textContent =
                    Math.round(
                        baseHumidity +
                        randomBetween(-1, 1)
                    );

                latitude.textContent =
                    (
                        baseLat +
                        randomBetween(-0.002, 0.002)
                    ).toFixed(4);

                longitude.textContent =
                    (
                        baseLng +
                        randomBetween(-0.002, 0.002)
                    ).toFixed(4);

                moving++;
                engineOn++;

            } else {

                /*
                 * Stationary vehicles:
                 * speed remains zero,
                 * sensor values fluctuate slightly.
                 */

                speed.textContent = "0";

                temperature.textContent =
                    (
                        baseTemp +
                        randomBetween(-0.15, 0.15)
                    ).toFixed(1);

                humidity.textContent =
                    Math.round(
                        baseHumidity +
                        randomBetween(-1, 1)
                    );

                latitude.textContent =
                    baseLat.toFixed(4);

                longitude.textContent =
                    baseLng.toFixed(4);
            }


            if (cardUpdate) {

                cardUpdate.textContent =
                    new Date().toLocaleString();

            }

        });


        document.getElementById(
            "movingVehicleCount"
        ).textContent = moving;


        document.getElementById(
            "engineOnCount"
        ).textContent = engineOn;


        updateClock();
    }


    /*
     * First load
     */
    updateClock();


    /*
     * Refresh simulation every 3 seconds
     */
    setInterval(
        updateTelemetry,
        3000
    );

});
</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>