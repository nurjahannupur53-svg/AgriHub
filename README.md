# 🌾 AgriHub — Agriculture Supply Chain Management System

AgriHub is a web-based Agriculture Supply Chain Management System (SCMS) designed to manage and monitor agricultural activities from farmer registration to final product delivery.

The system connects farmers, crops, harvest batches, quality control, inventory, market orders, transportation, equipment bookings, consultations, demand forecasting, price trends, notifications, and IoT-based vehicle monitoring in one platform.

## 🚀 Core Workflow

```text
Farmer
   ↓
Crop
   ↓
Harvest Batch
   ↓
Quality Check
   ↓
Inventory
   ↓
Market Order
   ↓
Vehicle & Delivery
```

## ✨ Features

- Secure user registration and login
- Admin dashboard with database-driven statistics
- Dynamic crop production and market price charts
- Farmer management
- Crop management
- Harvest batch tracking
- Quality inspection and approval
- Inventory and stock management
- Market order management
- Vehicle management
- Delivery tracking
- Super shop order management
- Demand forecasting
- Agricultural equipment booking
- Farmer consultation management
- IoT and telematics monitoring
- Historical crop price trends
- Notification system
- User profile management
- Secure password hashing and password change
- Responsive admin interface

## 🧩 Main Modules

### Farmers & Crops
Manage farmer profiles, agricultural land, crop information, planting dates, expected harvest dates, and crop status.

### Harvest Batches
Create and track harvested crop batches including quantity, harvest date, location, quality-control status, and inventory status.

### Quality Control
Inspect harvest batches, record inspector information, grade, moisture level, and approval/rejection results.

### Inventory
Manage approved agricultural stock, warehouse locations, available quantity, reserved quantity, stock thresholds, and stock status.

### Market Orders
Create buyer orders from available inventory and reserve stock automatically.

### Vehicles & Deliveries
Manage delivery vehicles and assign orders to vehicles for transportation and delivery tracking.

### Super Shop Orders
Manage packaged agricultural product orders for retail and super-shop customers.

### Demand Forecasting
Display crop demand forecasts, predicted demand, confidence levels, and regional information.

### Equipment Booking
Manage agricultural equipment bookings for farmers.

### Consultations
Schedule and manage consultations between farmers and agricultural experts.

### IoT & Telematics
Display simulated vehicle telemetry including speed, temperature, humidity, engine status, location, and event logs.

### Price Trends
Track historical agricultural commodity prices and visualize market price changes.

### Notifications
Display system notifications and maintain read/unread status.

## 🛠️ Technologies Used

- PHP
- MySQL
- PDO
- HTML5
- CSS3
- JavaScript
- SVG Charts
- XAMPP / Apache

## 🗄️ Database

The project uses a relational MySQL database named:

```text
agrihub
```

The repository includes:

```text
database.sql
```

This file contains the database schema and sample data required to initialize the project.

## ⚙️ Installation

### 1. Clone the repository

```bash
git clone YOUR_REPOSITORY_URL
```

Move the project into your XAMPP `htdocs` directory if necessary.

Example:

```text
C:\xampp\htdocs\AgriHub
```

### 2. Start XAMPP

Start:

- Apache
- MySQL

### 3. Create the database

Open phpMyAdmin and import:

```text
database.sql
```

The script creates the `agrihub` database and required tables.

### 4. Configure database connection

Inside:

```text
config/
```

copy:

```text
db.example.php
```

and rename the copy to:

```text
db.php
```

Then configure your local MySQL credentials:

```php
$host = "localhost";
$dbname = "agrihub";
$username = "root";
$password = "";
```

`config/db.php` is intentionally excluded from Git tracking so private database credentials are not committed.

### 5. Open the application

Open:

```text
http://localhost/AgriHub/
```

Register a user account and log in to access the system.

## 🔐 Security

The project includes several security and data-integrity practices:

- PHP password hashing
- Password verification
- PDO prepared statements
- Session-based authentication
- Input validation
- Relational foreign-key constraints
- Database check constraints
- Restricted deletion of referenced records
- Local database credentials excluded from Git

## 📁 Project Structure

```text
AgriHub/
│
├── assets/
│   ├── css/
│   └── js/
│
├── auth/
│
├── config/
│   └── db.example.php
│
├── includes/
│
├── dashboard.php
├── farmers.php
├── crops.php
├── harvest_batches.php
├── quality_checks.php
├── inventory.php
├── market_orders.php
├── vehicles.php
├── deliveries.php
├── super_shop_orders.php
├── demand_forecast.php
├── equipment_booking.php
├── consultation.php
├── iot_telematics.php
├── price_trends.php
├── notification.php
├── profile.php
├── database.sql
├── .gitignore
└── README.md
```

## 📊 Data Relationships

AgriHub uses relational database relationships to maintain supply-chain traceability.

Examples:

```text
Farmer → Crops
Crop → Harvest Batches
Harvest Batch → Quality Check
Harvest Batch → Inventory
Inventory → Market Orders
Market Order → Delivery
Vehicle → Delivery
Vehicle → IoT Device
```

Foreign-key constraints are used to help maintain data integrity between related records.

## 🎯 Project Purpose

The goal of AgriHub is to demonstrate how a centralized information system can improve visibility and management across an agricultural supply chain.

It was developed as an academic/software-development project and can be extended with additional production-level features in the future.

## 🔮 Possible Future Improvements

- Role-based access control
- CSRF protection
- Automatic event-based notifications
- REST API
- Advanced analytics and reporting
- Equipment master inventory
- Soft delete / archive functionality
- Real IoT device integration
- Advanced demand forecasting models
- Cloud deployment

## 👨‍💻 Author

Developed by **Nurjahan Hoque Nupur *

---

⭐ If you find this project useful, consider starring the repository.