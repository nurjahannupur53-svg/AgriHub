/* =========================================================
   AGRIHUB
   Agriculture Supply Chain Management System
   Master Database Setup
========================================================= */

CREATE DATABASE IF NOT EXISTS agrihub
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE agrihub;


/* =========================================================
   1. USERS
========================================================= */

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    location VARCHAR(150) NOT NULL DEFAULT 'AgriHub HQ',
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Admin',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY unique_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   2. FARMERS
========================================================= */

CREATE TABLE IF NOT EXISTS farmers (
    id VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(100) NOT NULL,
    land DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    email VARCHAR(150) DEFAULT NULL,
    nid VARCHAR(50) DEFAULT NULL,
    joined DATE DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY unique_farmer_email (email),
    UNIQUE KEY unique_farmer_nid (nid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   3. CROPS
========================================================= */

CREATE TABLE IF NOT EXISTS crops (
    id VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    farmer_id VARCHAR(10) NOT NULL,
    land DECIMAL(10,2) NOT NULL DEFAULT 0,
    planting_date DATE DEFAULT NULL,
    harvest_date DATE DEFAULT NULL,

    status ENUM(
        'Growing',
        'Harvested',
        'Pending'
    ) NOT NULL DEFAULT 'Pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_crop_farmer (farmer_id),

    CONSTRAINT fk_crop_farmer
        FOREIGN KEY (farmer_id)
        REFERENCES farmers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   4. HARVEST BATCHES
========================================================= */

CREATE TABLE IF NOT EXISTS harvest_batches (
    id VARCHAR(10) NOT NULL,
    crop_id VARCHAR(10) NOT NULL,
    harvest_date DATE NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    location VARCHAR(150) NOT NULL,

    qc_status ENUM(
        'Pending',
        'Approved',
        'Rejected'
    ) NOT NULL DEFAULT 'Pending',

    inventory_status ENUM(
        'Awaiting QC',
        'In Inventory',
        'Sold',
        'Rejected'
    ) NOT NULL DEFAULT 'Awaiting QC',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_batch_crop (crop_id),

    CONSTRAINT fk_batch_crop
        FOREIGN KEY (crop_id)
        REFERENCES crops(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_batch_quantity
        CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   5. QUALITY CHECKS
========================================================= */

CREATE TABLE IF NOT EXISTS quality_checks (
    id VARCHAR(10) NOT NULL,
    batch_id VARCHAR(10) NOT NULL,
    inspector VARCHAR(120) NOT NULL,
    inspection_date DATE NOT NULL,
    grade VARCHAR(10) NOT NULL,
    moisture DECIMAL(5,2) NOT NULL,

    result ENUM(
        'Approved',
        'Rejected'
    ) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_qc_batch (batch_id),

    KEY idx_qc_batch (batch_id),

    CONSTRAINT fk_qc_batch
        FOREIGN KEY (batch_id)
        REFERENCES harvest_batches(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_qc_moisture
        CHECK (
            moisture >= 0
            AND moisture <= 100
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   6. INVENTORY
========================================================= */

CREATE TABLE IF NOT EXISTS inventory (
    id VARCHAR(10) NOT NULL,
    batch_id VARCHAR(10) NOT NULL,

    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    available DECIMAL(10,2) NOT NULL DEFAULT 0,
    reserved DECIMAL(10,2) NOT NULL DEFAULT 0,

    location VARCHAR(150) NOT NULL,

    status ENUM(
        'Available',
        'Partially Reserved',
        'Out of Stock'
    ) NOT NULL DEFAULT 'Available',

    threshold DECIMAL(10,2) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_inventory_batch (batch_id),

    KEY idx_inventory_batch (batch_id),

    CONSTRAINT fk_inventory_batch
        FOREIGN KEY (batch_id)
        REFERENCES harvest_batches(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_inventory_total
        CHECK (total >= 0),

    CONSTRAINT chk_inventory_available
        CHECK (available >= 0),

    CONSTRAINT chk_inventory_reserved
        CHECK (reserved >= 0),

    CONSTRAINT chk_inventory_threshold
        CHECK (threshold >= 0),

    CONSTRAINT chk_inventory_balance
        CHECK (available + reserved <= total)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   7. MARKET ORDERS
========================================================= */

CREATE TABLE IF NOT EXISTS market_orders (
    id VARCHAR(10) NOT NULL,
    inventory_id VARCHAR(10) NOT NULL,
    buyer VARCHAR(150) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    order_date DATE NOT NULL,
    delivery_date DATE DEFAULT NULL,

    payment ENUM(
        'Paid',
        'Partial',
        'Unpaid'
    ) NOT NULL DEFAULT 'Unpaid',

    status ENUM(
        'Pending',
        'Processing',
        'In Transit',
        'Delivered',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_market_order_inventory (inventory_id),

    CONSTRAINT fk_market_order_inventory
        FOREIGN KEY (inventory_id)
        REFERENCES inventory(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_market_order_quantity
        CHECK (quantity > 0),

    CONSTRAINT chk_market_order_amount
        CHECK (amount >= 0),

    CONSTRAINT chk_market_order_dates
        CHECK (
            delivery_date IS NULL
            OR delivery_date >= order_date
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   8. VEHICLES
========================================================= */

CREATE TABLE IF NOT EXISTS vehicles (
    id VARCHAR(10) NOT NULL,
    registration VARCHAR(50) NOT NULL,
    type VARCHAR(100) NOT NULL,
    capacity DECIMAL(10,2) NOT NULL,

    driver VARCHAR(120) NOT NULL DEFAULT 'Unassigned',

    phone VARCHAR(20) DEFAULT NULL,

    status ENUM(
        'Available',
        'Active',
        'In Transit',
        'Maintenance'
    ) NOT NULL DEFAULT 'Available',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_vehicle_registration (registration),

    CONSTRAINT chk_vehicle_capacity
        CHECK (capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   9. DELIVERIES
========================================================= */

CREATE TABLE IF NOT EXISTS deliveries (
    id VARCHAR(10) NOT NULL,
    order_id VARCHAR(10) NOT NULL,
    vehicle_id VARCHAR(10) NOT NULL,
    delivery_date DATE NOT NULL,

    status ENUM(
        'Pending',
        'In Transit',
        'Delivered',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_delivery_order (order_id),

    KEY idx_delivery_vehicle (vehicle_id),

    CONSTRAINT fk_delivery_order
        FOREIGN KEY (order_id)
        REFERENCES market_orders(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_delivery_vehicle
        FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   10. SUPER SHOP ORDERS
========================================================= */

CREATE TABLE IF NOT EXISTS super_shop_orders (
    id VARCHAR(10) NOT NULL,
    shop VARCHAR(150) NOT NULL,
    product VARCHAR(150) NOT NULL,
    crop_id VARCHAR(10) NOT NULL,
    quantity INT UNSIGNED NOT NULL,

    unit VARCHAR(30) NOT NULL DEFAULT 'packs',

    order_date DATE NOT NULL,
    delivery_date DATE DEFAULT NULL,

    status ENUM(
        'Pending',
        'Processing',
        'Delivered',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_super_shop_crop (crop_id),

    CONSTRAINT fk_super_shop_crop
        FOREIGN KEY (crop_id)
        REFERENCES crops(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_super_shop_quantity
        CHECK (quantity > 0),

    CONSTRAINT chk_super_shop_dates
        CHECK (
            delivery_date IS NULL
            OR delivery_date >= order_date
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   11. DEMAND FORECASTS
========================================================= */

CREATE TABLE IF NOT EXISTS demand_forecasts (
    id VARCHAR(10) NOT NULL,
    crop_id VARCHAR(10) NOT NULL,

    current_demand DECIMAL(12,2) NOT NULL DEFAULT 0,
    predicted_demand DECIMAL(12,2) NOT NULL DEFAULT 0,

    confidence DECIMAL(5,2) NOT NULL DEFAULT 0,

    region VARCHAR(150) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_forecast_crop (crop_id),

    KEY idx_forecast_crop (crop_id),

    CONSTRAINT fk_forecast_crop
        FOREIGN KEY (crop_id)
        REFERENCES crops(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_forecast_current
        CHECK (current_demand >= 0),

    CONSTRAINT chk_forecast_predicted
        CHECK (predicted_demand >= 0),

    CONSTRAINT chk_forecast_confidence
        CHECK (
            confidence >= 0
            AND confidence <= 100
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   12. EQUIPMENT BOOKINGS
========================================================= */

CREATE TABLE IF NOT EXISTS equipment_bookings (
    id VARCHAR(10) NOT NULL,
    equipment VARCHAR(150) NOT NULL,
    purpose VARCHAR(150) NOT NULL,
    farmer_id VARCHAR(10) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    status ENUM(
        'Pending',
        'Active',
        'Returned',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_equipment_booking_farmer (farmer_id),

    CONSTRAINT fk_equipment_booking_farmer
        FOREIGN KEY (farmer_id)
        REFERENCES farmers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_equipment_booking_dates
        CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   13. CONSULTATIONS
========================================================= */

CREATE TABLE IF NOT EXISTS consultations (
    id VARCHAR(10) NOT NULL,
    farmer_id VARCHAR(10) NOT NULL,
    expert VARCHAR(150) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    consultation_date DATE NOT NULL,

    status ENUM(
        'Scheduled',
        'Completed',
        'Cancelled'
    ) NOT NULL DEFAULT 'Scheduled',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_consultation_farmer (farmer_id),
    KEY idx_consultation_date (consultation_date),

    CONSTRAINT fk_consultation_farmer
        FOREIGN KEY (farmer_id)
        REFERENCES farmers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   14. IOT DEVICES
========================================================= */

CREATE TABLE IF NOT EXISTS iot_devices (
    id VARCHAR(10) NOT NULL,
    vehicle_id VARCHAR(10) NOT NULL,

    active TINYINT(1) NOT NULL DEFAULT 0,

    speed DECIMAL(8,2) NOT NULL DEFAULT 0,
    temperature DECIMAL(6,2) DEFAULT NULL,
    humidity DECIMAL(5,2) DEFAULT NULL,

    engine ENUM(
        'On',
        'Off'
    ) NOT NULL DEFAULT 'Off',

    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,

    delivery_id VARCHAR(10) DEFAULT NULL,

    last_update DATETIME NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_iot_vehicle (vehicle_id),

    KEY idx_iot_delivery (delivery_id),

    CONSTRAINT fk_iot_vehicle
        FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_iot_delivery
        FOREIGN KEY (delivery_id)
        REFERENCES deliveries(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_iot_speed
        CHECK (speed >= 0),

    CONSTRAINT chk_iot_humidity
        CHECK (
            humidity IS NULL
            OR (
                humidity >= 0
                AND humidity <= 100
            )
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   15. IOT EVENT LOGS
========================================================= */

CREATE TABLE IF NOT EXISTS iot_event_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    device_id VARCHAR(10) NOT NULL,
    event_time DATETIME NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    message VARCHAR(500) NOT NULL,

    level ENUM(
        'info',
        'success',
        'warning',
        'danger'
    ) NOT NULL DEFAULT 'info',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_iot_event_device (device_id),
    KEY idx_iot_event_time (event_time),

    CONSTRAINT fk_iot_event_device
        FOREIGN KEY (device_id)
        REFERENCES iot_devices(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   16. NOTIFICATIONS
========================================================= */

CREATE TABLE IF NOT EXISTS notifications (
    id VARCHAR(10) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message VARCHAR(500) NOT NULL,

    type ENUM(
        'harvest',
        'quality',
        'inventory',
        'order',
        'delivery',
        'equipment',
        'consultation',
        'system'
    ) NOT NULL DEFAULT 'system',

    is_read TINYINT(1) NOT NULL DEFAULT 0,

    link VARCHAR(255) DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_notification_read (is_read),
    KEY idx_notification_type (type),
    KEY idx_notification_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   17. PRICE TRENDS
========================================================= */

CREATE TABLE IF NOT EXISTS price_trends (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    crop_id VARCHAR(10) NOT NULL,
    price_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,

    unit VARCHAR(30) NOT NULL DEFAULT '৳/kg',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY unique_crop_price_date (
        crop_id,
        price_date
    ),

    KEY idx_price_crop (crop_id),
    KEY idx_price_date (price_date),

    CONSTRAINT fk_price_crop
        FOREIGN KEY (crop_id)
        REFERENCES crops(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_price_positive
        CHECK (price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* =========================================================
   SEED DATA
========================================================= */


/* =========================================================
   FARMERS
========================================================= */

INSERT IGNORE INTO farmers
(
    id,
    name,
    phone,
    location,
    land,
    status,
    email,
    nid,
    joined
)
VALUES

(
    'F001',
    'Rahim Uddin',
    '01711-234567',
    'Mymensingh',
    12.50,
    'active',
    'rahim@agri.bd',
    'NID1234567890',
    '2022-03-15'
),

(
    'F002',
    'Sufia Begum',
    '01812-345678',
    'Rajshahi',
    8.20,
    'active',
    'sufia@agri.bd',
    'NID2345678901',
    '2022-06-12'
),

(
    'F003',
    'Karim Hossain',
    '01913-456789',
    'Khulna',
    20.00,
    'active',
    'karim@agri.bd',
    'NID3456789012',
    '2022-09-08'
),

(
    'F004',
    'Nasrin Akter',
    '01614-567890',
    'Sylhet',
    5.80,
    'inactive',
    'nasrin@agri.bd',
    'NID4567890123',
    '2023-01-17'
),

(
    'F005',
    'Jalal Ahmed',
    '01515-678901',
    'Chittagong',
    15.30,
    'active',
    'jalal@agri.bd',
    'NID5678901234',
    '2023-04-21'
),

(
    'F006',
    'Fatema Khatun',
    '01716-789012',
    'Dhaka',
    3.10,
    'active',
    'fatema@agri.bd',
    'NID6789012345',
    '2023-08-10'
);


/* =========================================================
   CROPS
========================================================= */

INSERT IGNORE INTO crops
(
    id,
    name,
    category,
    farmer_id,
    land,
    planting_date,
    harvest_date,
    status
)
VALUES

(
    'C001',
    'Aman Rice',
    'Grain',
    'F001',
    5.00,
    '2024-07-15',
    '2024-11-30',
    'Growing'
),

(
    'C002',
    'Mustard',
    'Oilseed',
    'F001',
    3.50,
    '2024-11-01',
    '2025-02-15',
    'Harvested'
),

(
    'C003',
    'Wheat',
    'Grain',
    'F002',
    8.20,
    '2024-11-20',
    '2025-03-10',
    'Growing'
),

(
    'C004',
    'Jute',
    'Fiber',
    'F003',
    10.00,
    '2024-04-01',
    '2024-08-30',
    'Harvested'
),

(
    'C005',
    'Potato',
    'Vegetable',
    'F003',
    5.00,
    '2024-10-15',
    '2025-01-20',
    'Growing'
),

(
    'C006',
    'Tomato',
    'Vegetable',
    'F004',
    2.50,
    '2024-09-20',
    '2024-12-20',
    'Pending'
),

(
    'C007',
    'Maize',
    'Grain',
    'F005',
    8.00,
    '2024-03-10',
    '2024-07-20',
    'Harvested'
),

(
    'C008',
    'Lentil',
    'Legume',
    'F005',
    4.00,
    '2024-10-25',
    '2025-02-28',
    'Growing'
),

(
    'C009',
    'Brinjal',
    'Vegetable',
    'F006',
    3.10,
    '2024-08-15',
    '2024-11-15',
    'Harvested'
);


/* =========================================================
   HARVEST BATCHES
========================================================= */

INSERT IGNORE INTO harvest_batches
(
    id,
    crop_id,
    harvest_date,
    quantity,
    location,
    qc_status,
    inventory_status
)
VALUES

(
    'HB001',
    'C001',
    '2024-11-28',
    7.20,
    'Mymensingh Warehouse',
    'Approved',
    'In Inventory'
),

(
    'HB002',
    'C002',
    '2025-02-14',
    2.00,
    'Field',
    'Pending',
    'Awaiting QC'
),

(
    'HB003',
    'C003',
    '2025-03-08',
    11.80,
    'Rajshahi Warehouse',
    'Approved',
    'In Inventory'
),

(
    'HB004',
    'C004',
    '2024-08-28',
    14.50,
    'Khulna Warehouse',
    'Approved',
    'Sold'
),

(
    'HB005',
    'C005',
    '2025-01-18',
    38.50,
    'Field',
    'Pending',
    'Awaiting QC'
),

(
    'HB006',
    'C007',
    '2024-07-18',
    19.00,
    'Chittagong Depot',
    'Rejected',
    'Rejected'
),

(
    'HB007',
    'C008',
    '2025-02-26',
    3.10,
    'Dhaka Warehouse',
    'Approved',
    'In Inventory'
),

(
    'HB008',
    'C009',
    '2024-11-13',
    11.50,
    'Dhaka Warehouse',
    'Approved',
    'In Inventory'
);


/* =========================================================
   QUALITY CHECKS
========================================================= */

INSERT IGNORE INTO quality_checks
(
    id,
    batch_id,
    inspector,
    inspection_date,
    grade,
    moisture,
    result
)
VALUES

(
    'QC001',
    'HB001',
    'Dr. Mizanur Rahman',
    '2024-11-30',
    'A',
    12.50,
    'Approved'
),

(
    'QC002',
    'HB003',
    'Eng. Taslima Akter',
    '2025-03-10',
    'B+',
    13.20,
    'Approved'
),

(
    'QC003',
    'HB004',
    'Dr. Mizanur Rahman',
    '2024-09-01',
    'A+',
    10.80,
    'Approved'
),

(
    'QC004',
    'HB006',
    'Eng. Taslima Akter',
    '2024-07-20',
    'C',
    18.90,
    'Rejected'
),

(
    'QC005',
    'HB007',
    'Dr. Kamal Pasha',
    '2025-02-28',
    'A',
    11.20,
    'Approved'
),

(
    'QC006',
    'HB008',
    'Dr. Kamal Pasha',
    '2024-11-15',
    'B',
    92.00,
    'Approved'
);


/* =========================================================
   INVENTORY
========================================================= */

INSERT IGNORE INTO inventory
(
    id,
    batch_id,
    total,
    available,
    reserved,
    location,
    status,
    threshold
)
VALUES

(
    'INV001',
    'HB001',
    7.20,
    5.50,
    1.70,
    'Mymensingh Central Warehouse',
    'Available',
    6.00
),

(
    'INV002',
    'HB003',
    11.80,
    8.00,
    3.80,
    'Rajshahi Agro Store',
    'Partially Reserved',
    5.00
),

(
    'INV003',
    'HB004',
    14.50,
    0.00,
    0.00,
    'Khulna Export Hub',
    'Out of Stock',
    2.00
),

(
    'INV004',
    'HB007',
    3.10,
    2.50,
    0.60,
    'Dhaka Central Warehouse',
    'Available',
    3.00
),

(
    'INV005',
    'HB008',
    11.50,
    11.50,
    0.00,
    'Dhaka Central Warehouse',
    'Available',
    3.00
);


/* =========================================================
   MARKET ORDERS
========================================================= */

INSERT IGNORE INTO market_orders
(
    id,
    inventory_id,
    buyer,
    quantity,
    amount,
    order_date,
    delivery_date,
    payment,
    status
)
VALUES

(
    'ORD001',
    'INV001',
    'Dhaka Fresh Market',
    2.00,
    57000,
    '2024-12-02',
    '2024-12-10',
    'Paid',
    'Delivered'
),

(
    'ORD002',
    'INV002',
    'Rahman Traders',
    3.80,
    121600,
    '2025-03-12',
    '2025-03-20',
    'Partial',
    'In Transit'
),

(
    'ORD003',
    'INV004',
    'BD Export Corp',
    0.60,
    57000,
    '2025-03-05',
    '2025-03-15',
    'Unpaid',
    'Pending'
),

(
    'ORD004',
    'INV005',
    'Sylhet Agri Hub',
    5.00,
    90000,
    '2024-11-17',
    '2024-11-22',
    'Paid',
    'Delivered'
),

(
    'ORD005',
    'INV001',
    'Meghna Food Ltd',
    1.50,
    43500,
    '2025-01-10',
    '2025-01-18',
    'Unpaid',
    'Processing'
);


/* =========================================================
   VEHICLES
========================================================= */

INSERT IGNORE INTO vehicles
(
    id,
    registration,
    type,
    capacity,
    driver,
    phone,
    status
)
VALUES

(
    'VEH001',
    'DHA-GA-1234',
    'Refrigerated Truck',
    10.00,
    'Abdul Karim',
    '01711-223344',
    'Active'
),

(
    'VEH002',
    'CTG-BA-5678',
    'Cargo Van',
    3.00,
    'Ruhul Amin',
    '01812-334455',
    'In Transit'
),

(
    'VEH003',
    'KHU-CA-9012',
    'Flatbed Truck',
    15.00,
    'Mokhles Mia',
    '01913-445566',
    'Maintenance'
),

(
    'VEH004',
    'DHA-GA-3456',
    'Pickup Truck',
    2.00,
    'Unassigned',
    NULL,
    'Available'
);


/* =========================================================
   DELIVERIES
========================================================= */

INSERT IGNORE INTO deliveries
(
    id,
    order_id,
    vehicle_id,
    delivery_date,
    status
)
VALUES

(
    'DEL001',
    'ORD001',
    'VEH001',
    '2024-12-10',
    'Delivered'
),

(
    'DEL002',
    'ORD002',
    'VEH002',
    '2025-03-20',
    'In Transit'
),

(
    'DEL003',
    'ORD004',
    'VEH003',
    '2024-11-22',
    'Delivered'
);


/* =========================================================
   SUPER SHOP ORDERS
========================================================= */

INSERT IGNORE INTO super_shop_orders
(
    id,
    shop,
    product,
    crop_id,
    quantity,
    unit,
    order_date,
    delivery_date,
    status
)
VALUES

(
    'SS001',
    'Shwapno Superstore',
    'Aman Rice (5kg pack)',
    'C001',
    500,
    'packs',
    '2024-12-05',
    '2024-12-12',
    'Delivered'
),

(
    'SS002',
    'Agora Retail',
    'Lentil (2kg pack)',
    'C008',
    300,
    'packs',
    '2025-03-06',
    '2025-03-16',
    'Processing'
),

(
    'SS003',
    'Meena Bazar',
    'Brinjal (1kg pack)',
    'C009',
    800,
    'packs',
    '2024-11-18',
    '2024-11-23',
    'Delivered'
),

(
    'SS004',
    'Unimart',
    'Wheat Flour (2kg)',
    'C003',
    200,
    'packs',
    '2025-03-14',
    '2025-03-22',
    'Pending'
);


/* =========================================================
   DEMAND FORECASTS
========================================================= */

INSERT IGNORE INTO demand_forecasts
(
    id,
    crop_id,
    current_demand,
    predicted_demand,
    confidence,
    region
)
VALUES

(
    'DF001',
    'C001',
    850,
    920,
    87,
    'Dhaka / Chittagong'
),

(
    'DF002',
    'C003',
    1200,
    1350,
    91,
    'National'
),

(
    'DF003',
    'C005',
    3200,
    3500,
    78,
    'Dhaka / Sylhet'
),

(
    'DF004',
    'C008',
    420,
    480,
    85,
    'Dhaka'
),

(
    'DF005',
    'C007',
    680,
    720,
    82,
    'Chittagong / Khulna'
);


/* =========================================================
   EQUIPMENT BOOKINGS
========================================================= */

INSERT IGNORE INTO equipment_bookings
(
    id,
    equipment,
    purpose,
    farmer_id,
    start_date,
    end_date,
    status
)
VALUES

(
    'EB001',
    'Combine Harvester',
    'Harvesting',
    'F001',
    '2024-11-25',
    '2024-11-28',
    'Returned'
),

(
    'EB002',
    'Tractor (MF 240)',
    'Tillage',
    'F002',
    '2025-03-05',
    '2025-03-08',
    'Active'
),

(
    'EB003',
    'Drip Irrigation Set',
    'Irrigation',
    'F004',
    '2024-09-15',
    '2024-12-15',
    'Active'
),

(
    'EB004',
    'Sprayer Machine',
    'Pest Control',
    'F005',
    '2025-01-20',
    '2025-01-22',
    'Returned'
);


/* =========================================================
   CONSULTATIONS
========================================================= */

INSERT IGNORE INTO consultations
(
    id,
    farmer_id,
    expert,
    topic,
    consultation_date,
    status
)
VALUES

(
    'CON001',
    'F001',
    'Dr. Farid Ahmed',
    'Rice Blast Disease Control',
    '2024-10-15',
    'Completed'
),

(
    'CON002',
    'F002',
    'Agr. Nasima Khanom',
    'Wheat Irrigation Schedule',
    '2024-12-01',
    'Completed'
),

(
    'CON003',
    'F003',
    'Dr. Farid Ahmed',
    'Potato Late Blight Management',
    '2025-01-05',
    'Scheduled'
),

(
    'CON004',
    'F005',
    'Agr. Nasima Khanom',
    'Maize Fertilizer Application',
    '2024-06-10',
    'Completed'
),

(
    'CON005',
    'F005',
    'Dr. Kamal Hasan',
    'Post-harvest Storage Techniques',
    '2024-08-20',
    'Completed'
),

(
    'CON006',
    'F006',
    'Dr. Kamal Hasan',
    'Brinjal Pest Management',
    '2024-09-10',
    'Completed'
);


/* =========================================================
   IOT DEVICES
========================================================= */

INSERT IGNORE INTO iot_devices
(
    id,
    vehicle_id,
    active,
    speed,
    temperature,
    humidity,
    engine,
    latitude,
    longitude,
    delivery_id,
    last_update
)
VALUES

(
    'IOT001',
    'VEH001',
    0,
    0,
    4.70,
    78,
    'Off',
    23.8103000,
    90.4125000,
    'DEL001',
    '2025-03-11 08:30:00'
),

(
    'IOT002',
    'VEH002',
    1,
    64,
    22.30,
    65,
    'On',
    24.1234000,
    88.6789000,
    'DEL002',
    '2025-03-11 09:15:00'
),

(
    'IOT003',
    'VEH003',
    0,
    0,
    29.00,
    82,
    'Off',
    22.8456000,
    89.5432000,
    'DEL003',
    '2025-03-11 07:00:00'
),

(
    'IOT004',
    'VEH004',
    0,
    0,
    25.50,
    70,
    'Off',
    23.7901000,
    90.3987000,
    NULL,
    '2025-03-11 06:00:00'
);


/* =========================================================
   IOT EVENT LOGS
========================================================= */

INSERT INTO iot_event_logs
(
    device_id,
    event_time,
    event_type,
    message,
    level
)
SELECT
    'IOT002',
    '2025-03-11 09:15:00',
    'Location Update',
    'Vehicle location updated during DEL002.',
    'info'
WHERE NOT EXISTS (
    SELECT 1
    FROM iot_event_logs
    WHERE device_id = 'IOT002'
      AND event_time = '2025-03-11 09:15:00'
      AND event_type = 'Location Update'
);


INSERT INTO iot_event_logs
(
    device_id,
    event_time,
    event_type,
    message,
    level
)
SELECT
    'IOT002',
    '2025-03-11 09:12:00',
    'Speed',
    'Vehicle travelling at 64 km/h.',
    'success'
WHERE NOT EXISTS (
    SELECT 1
    FROM iot_event_logs
    WHERE device_id = 'IOT002'
      AND event_time = '2025-03-11 09:12:00'
      AND event_type = 'Speed'
);


INSERT INTO iot_event_logs
(
    device_id,
    event_time,
    event_type,
    message,
    level
)
SELECT
    'IOT001',
    '2025-03-11 08:30:00',
    'Engine',
    'Engine turned off after delivery completion.',
    'info'
WHERE NOT EXISTS (
    SELECT 1
    FROM iot_event_logs
    WHERE device_id = 'IOT001'
      AND event_time = '2025-03-11 08:30:00'
      AND event_type = 'Engine'
);


INSERT INTO iot_event_logs
(
    device_id,
    event_time,
    event_type,
    message,
    level
)
SELECT
    'IOT001',
    '2025-03-11 08:28:00',
    'Temperature',
    'Cargo temperature stable at 4.7°C.',
    'success'
WHERE NOT EXISTS (
    SELECT 1
    FROM iot_event_logs
    WHERE device_id = 'IOT001'
      AND event_time = '2025-03-11 08:28:00'
      AND event_type = 'Temperature'
);


INSERT INTO iot_event_logs
(
    device_id,
    event_time,
    event_type,
    message,
    level
)
SELECT
    'IOT003',
    '2025-03-11 07:00:00',
    'Maintenance',
    'Vehicle currently unavailable for dispatch.',
    'warning'
WHERE NOT EXISTS (
    SELECT 1
    FROM iot_event_logs
    WHERE device_id = 'IOT003'
      AND event_time = '2025-03-11 07:00:00'
      AND event_type = 'Maintenance'
);


INSERT INTO iot_event_logs
(
    device_id,
    event_time,
    event_type,
    message,
    level
)
SELECT
    'IOT004',
    '2025-03-11 06:00:00',
    'Vehicle Ready',
    'Pickup truck available for assignment.',
    'success'
WHERE NOT EXISTS (
    SELECT 1
    FROM iot_event_logs
    WHERE device_id = 'IOT004'
      AND event_time = '2025-03-11 06:00:00'
      AND event_type = 'Vehicle Ready'
);


/* =========================================================
   NOTIFICATIONS
========================================================= */

INSERT IGNORE INTO notifications
(
    id,
    title,
    message,
    type,
    is_read,
    link,
    created_at
)
VALUES

(
    'NOT001',
    'New Harvest Batch Ready',
    'HB005 Potato batch (38.5 ton) is awaiting quality check.',
    'harvest',
    0,
    'batch_details.php?id=HB005',
    '2025-03-11 08:00:00'
),

(
    'NOT002',
    'Quality Check Approved',
    'HB007 Lentil has been approved with Grade A.',
    'quality',
    0,
    'quality_checks.php?batch=HB007',
    '2025-03-11 05:00:00'
),

(
    'NOT003',
    'Low Stock Alert',
    'INV001 Aman Rice inventory is below threshold.',
    'inventory',
    0,
    'inventory_details.php?id=INV001',
    '2025-03-10 10:00:00'
),

(
    'NOT004',
    'New Market Order',
    'ORD005 from Meghna Food Ltd for 1.5 ton Aman Rice worth ৳43,500.',
    'order',
    1,
    'market_order_details.php?id=ORD005',
    '2025-03-09 10:00:00'
),

(
    'NOT005',
    'Delivery In Transit',
    'DEL002 for Rahman Traders is currently in transit with driver Ruhul Amin.',
    'delivery',
    1,
    'delivery_details.php?id=DEL002',
    '2025-03-08 10:00:00'
),

(
    'NOT006',
    'Equipment Booking',
    'Tractor (MF 240) is booked for Sufia Begum from March 5 to March 8, 2025.',
    'equipment',
    1,
    'equipment_details.php?id=EB002',
    '2025-03-06 10:00:00'
),

(
    'NOT007',
    'Consultation Scheduled',
    'Dr. Farid Ahmed consultation with Karim Hossain for Potato Late Blight Management.',
    'consultation',
    1,
    'consultation_details.php?id=CON003',
    '2025-03-04 10:00:00'
);


/* =========================================================
   PRICE TRENDS
========================================================= */

/* Aman Rice */

INSERT IGNORE INTO price_trends
(crop_id, price_date, price, unit)
VALUES
('C001', '2024-09-01', 26.50, '৳/kg'),
('C001', '2024-10-01', 27.00, '৳/kg'),
('C001', '2024-11-01', 27.50, '৳/kg'),
('C001', '2024-12-01', 28.50, '৳/kg'),
('C001', '2025-01-01', 28.00, '৳/kg'),
('C001', '2025-02-01', 28.70, '৳/kg'),
('C001', '2025-03-01', 29.00, '৳/kg');


/* Wheat */

INSERT IGNORE INTO price_trends
(crop_id, price_date, price, unit)
VALUES
('C003', '2024-09-01', 30.50, '৳/kg'),
('C003', '2024-10-01', 30.80, '৳/kg'),
('C003', '2024-11-01', 31.00, '৳/kg'),
('C003', '2024-12-01', 31.40, '৳/kg'),
('C003', '2025-01-01', 31.20, '৳/kg'),
('C003', '2025-02-01', 31.80, '৳/kg'),
('C003', '2025-03-01', 32.00, '৳/kg');


/* Lentil */

INSERT IGNORE INTO price_trends
(crop_id, price_date, price, unit)
VALUES
('C008', '2024-09-01', 90.00, '৳/kg'),
('C008', '2024-10-01', 91.00, '৳/kg'),
('C008', '2024-11-01', 92.50, '৳/kg'),
('C008', '2024-12-01', 93.00, '৳/kg'),
('C008', '2025-01-01', 92.80, '৳/kg'),
('C008', '2025-02-01', 94.00, '৳/kg'),
('C008', '2025-03-01', 95.00, '৳/kg');


/* Maize */

INSERT IGNORE INTO price_trends
(crop_id, price_date, price, unit)
VALUES
('C007', '2024-09-01', 26.50, '৳/kg'),
('C007', '2024-10-01', 26.80, '৳/kg'),
('C007', '2024-11-01', 27.20, '৳/kg'),
('C007', '2024-12-01', 27.00, '৳/kg'),
('C007', '2025-01-01', 26.70, '৳/kg'),
('C007', '2025-02-01', 26.90, '৳/kg'),
('C007', '2025-03-01', 27.00, '৳/kg');


/* Potato */

INSERT IGNORE INTO price_trends
(crop_id, price_date, price, unit)
VALUES
('C005', '2024-09-01', 23.00, '৳/kg'),
('C005', '2024-10-01', 24.00, '৳/kg'),
('C005', '2024-11-01', 25.00, '৳/kg'),
('C005', '2024-12-01', 24.50, '৳/kg'),
('C005', '2025-01-01', 23.50, '৳/kg'),
('C005', '2025-02-01', 22.80, '৳/kg'),
('C005', '2025-03-01', 22.00, '৳/kg');


/* =========================================================
   END OF AGRIHUB DATABASE SETUP
========================================================= */