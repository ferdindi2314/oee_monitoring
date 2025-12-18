# 📚 OEE Monitoring API Documentation

**Version:** 1.0  
**Base URL:** `http://localhost/OEE_Monitoring_API/`  
**Author:** Your Name  
**Last Updated:** December 18, 2025

---

## 📋 Table of Contents

1. [Introduction](#introduction)
2. [Setup & Installation](#setup--installation)
3. [Database Setup](#database-setup)
4. [API Endpoints](#api-endpoints)
5. [Request & Response Examples](#request--response-examples)
6. [Error Handling](#error-handling)
7. [Testing](#testing)

---

## 🎯 Introduction

OEE (Overall Equipment Effectiveness) Monitoring API adalah REST API untuk monitoring dan tracking data produksi mesin. API ini mendukung:

- ✅ Import data dari file CSV/Excel
- ✅ CRUD operations untuk data OEE
- ✅ Dashboard monitoring real-time
- ✅ Perhitungan OEE otomatis
- ✅ Statistik dan trend analysis
- ✅ Filter data berdasarkan tanggal, shift, status

---

## 🚀 Setup & Installation

### Prerequisites

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache dengan mod_rewrite enabled
- XAMPP/WAMP/LAMP

### Installation Steps

1. **Clone atau copy project ke folder htdocs**

   ```bash
   cd C:\xampp\htdocs
   # Copy folder OEE_Monitoring_API
   ```

2. **Enable Apache mod_rewrite**

   - Buka file `httpd.conf`
   - Uncomment: `LoadModule rewrite_module modules/mod_rewrite.so`
   - Restart Apache

3. **Configure database**
   - Edit `Config/database.php` sesuai dengan setup MySQL kamu
   ```php
   private $host = "localhost";
   private $db_name = "oee_monitoring";
   private $username = "root";
   private $password = "";
   ```

---

## 🗄️ Database Setup

### Import Database

1. Buka phpMyAdmin atau MySQL client
2. Import file `Database/oee_database.sql`
3. Database `oee_monitoring` akan dibuat otomatis

### Database Structure

#### Table: `oee_data`

Main table untuk menyimpan data historis OEE

| Column             | Type        | Description               |
| ------------------ | ----------- | ------------------------- |
| id                 | INT         | Primary key               |
| tanggal            | DATE        | Tanggal produksi          |
| shift              | VARCHAR(10) | Shift kerja (Shift 1/2/3) |
| status_mesin       | ENUM        | ON/OFF                    |
| waktu_mesin_on     | TIME        | Waktu mesin ON            |
| waktu_mesin_off    | TIME        | Waktu mesin OFF           |
| downtime_mesin     | DECIMAL     | Downtime dalam menit      |
| waktu_operasi_er   | DECIMAL     | Waktu operasi efektif     |
| jumlah_green_tire  | INT         | Total produksi            |
| cycle_time_mesin   | DECIMAL     | Cycle time                |
| kecepatan_produksi | DECIMAL     | Kecepatan produksi        |
| target_produksi    | INT         | Target produksi           |
| jumlah_reject      | INT         | Jumlah reject             |
| persentase_reject  | DECIMAL     | Persentase reject         |
| jumlah_produk_ok   | INT         | Jumlah produk OK          |
| alarm_mesin        | TEXT        | Alarm message             |
| mode_mesin         | ENUM        | Auto/Manual               |

#### Table: `downtime_details`

Detail breakdown downtime

#### Table: `alarm_details`

Detail alarm mesin

#### Table: `production_targets`

Target dan achievement produksi

---

## 🔌 API Endpoints

### Base URL

```
http://localhost/OEE_Monitoring_API/API/
```

### Authentication

Saat ini API tidak menggunakan authentication. Untuk production, disarankan menambahkan JWT atau API Key.

---

## 📡 1. OEE Data Endpoints

### 1.1 Get All Data (dengan filter)

**Endpoint:** `GET /API/oee.php`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| start_date | DATE | No | Filter tanggal mulai (YYYY-MM-DD) |
| end_date | DATE | No | Filter tanggal akhir (YYYY-MM-DD) |
| shift | STRING | No | Filter shift (Shift 1/2/3) |
| status_mesin | STRING | No | Filter status (ON/OFF) |
| page | INT | No | Page number (default: 1) |
| limit | INT | No | Records per page (default: 100) |

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/oee.php?start_date=2024-01-01&end_date=2024-01-31&shift=Shift%201&page=1&limit=50
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "records": [
      {
        "id": 1,
        "tanggal": "2024-01-15",
        "shift": "Shift 1",
        "status_mesin": "ON",
        "waktu_mesin_on": "08:00:00",
        "waktu_mesin_off": "16:00:00",
        "downtime_mesin": "45.50",
        "waktu_operasi_er": "435.50",
        "jumlah_green_tire": 1200,
        "cycle_time_mesin": "2.50",
        "kecepatan_produksi": "150.75",
        "target_produksi": 1300,
        "jumlah_reject": 12,
        "persentase_reject": "1.00",
        "jumlah_produk_ok": 1188,
        "alarm_mesin": "Sensor error pada jam 10:30",
        "mode_mesin": "Auto",
        "created_at": "2024-01-15 08:00:00",
        "updated_at": "2024-01-15 16:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 50,
      "total_records": 1
    }
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

### 1.2 Get Single Data by ID

**Endpoint:** `GET /API/oee.php?id={id}`

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/oee.php?id=1
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "tanggal": "2024-01-15",
    "shift": "Shift 1",
    "status_mesin": "ON",
    ...
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

### 1.3 Create New Data

**Endpoint:** `POST /API/oee.php`

**Headers:**

```
Content-Type: application/json
```

**Request Body:**

```json
{
  "tanggal": "2024-01-15",
  "shift": "Shift 1",
  "status_mesin": "ON",
  "waktu_mesin_on": "08:00:00",
  "waktu_mesin_off": "16:00:00",
  "downtime_mesin": 45.5,
  "waktu_operasi_er": 435.5,
  "jumlah_green_tire": 1200,
  "cycle_time_mesin": 2.5,
  "kecepatan_produksi": 150.75,
  "target_produksi": 1300,
  "jumlah_reject": 12,
  "persentase_reject": 1.0,
  "jumlah_produk_ok": 1188,
  "alarm_mesin": "Sensor error pada jam 10:30",
  "mode_mesin": "Auto"
}
```

**Required Fields:**

- `tanggal` (DATE)
- `shift` (STRING: Shift 1/2/3)
- `status_mesin` (STRING: ON/OFF)

**Example Response:**

```json
{
  "success": true,
  "data": {
    "message": "Data created successfully",
    "id": 123
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

### 1.4 Update Data

**Endpoint:** `PUT /API/oee.php`

**Headers:**

```
Content-Type: application/json
```

**Request Body:**

```json
{
  "id": 1,
  "tanggal": "2024-01-15",
  "shift": "Shift 1",
  "status_mesin": "ON",
  ...
}
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "message": "Data updated successfully"
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

### 1.5 Delete Data

**Endpoint:** `DELETE /API/oee.php`

**Headers:**

```
Content-Type: application/json
```

**Request Body:**

```json
{
  "id": 1
}
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "message": "Data deleted successfully"
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

## 📊 2. Dashboard Endpoints

### 2.1 Get Summary Data

**Endpoint:** `GET /API/dashboard.php?summary=true`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | DATE | No | Tanggal (default: today) |

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/dashboard.php?summary=true&date=2024-01-15
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "date": "2024-01-15",
    "statistics": {
      "total_records": 3,
      "total_production": 3600,
      "total_reject": 35,
      "avg_reject_percentage": "0.97",
      "avg_downtime": "45.17",
      "avg_production_speed": "150.25",
      "machine_on_count": 3,
      "machine_off_count": 0,
      "avg_cycle_time": "2.50",
      "target_achievement": "92.31"
    },
    "oee_calculation": {
      "availability": 90.62,
      "performance": 92.31,
      "quality": 99.03,
      "oee": 82.85
    },
    "shift_data": [...],
    "alarm_summary": [...]
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

### 2.2 Get Real-time Data

**Endpoint:** `GET /API/dashboard.php?realtime=true`

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/dashboard.php?realtime=true
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "timestamp": "2025-12-18 10:30:00",
    "current_shift": "Shift 2",
    "machine_status": {
      "status_mesin": "ON",
      "mode_mesin": "Auto",
      "alarm_mesin": ""
    },
    "production_today": {
      "total": 2500,
      "ok": 2475,
      "reject": 25
    },
    "downtime_today": {
      "total_downtime": "120.50",
      "downtime_events": 5
    },
    "alarms_active": 0
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

### 2.3 Get Production Trend

**Endpoint:** `GET /API/dashboard.php?trend=true`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| days | INT | No | Jumlah hari (default: 30) |

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/dashboard.php?trend=true&days=7
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "labels": ["2024-01-15", "2024-01-16", "2024-01-17"],
    "production": [3600, 3700, 3550],
    "reject": [35, 30, 40],
    "downtime": [45.5, 38.2, 52.1]
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

### 2.4 Get OEE Trend

**Endpoint:** `GET /API/dashboard.php?oee_trend=true`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| days | INT | No | Jumlah hari (default: 30) |

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/dashboard.php?oee_trend=true&days=7
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "labels": ["2024-01-15", "2024-01-16", "2024-01-17"],
    "availability": [90.62, 92.08, 89.17],
    "performance": [92.31, 94.87, 91.03],
    "quality": [99.03, 99.19, 98.88],
    "oee": [82.85, 86.63, 80.18]
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

## 3. Statistics Endpoints

### 3.1 Get Statistics

**Endpoint:** `GET /API/oee.php?statistics=true`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | DATE | No | Tanggal (default: today) |
| shift | STRING | No | Filter shift |

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/oee.php?statistics=true&date=2024-01-15&shift=Shift%201
```

---

### 3.2 Get OEE Calculation

**Endpoint:** `GET /API/oee.php?oee_calculation=true`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | DATE | No | Tanggal (default: today) |
| shift | STRING | No | Filter shift |

**Example Request:**

```bash
GET http://localhost/OEE_Monitoring_API/API/oee.php?oee_calculation=true&date=2024-01-15
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "availability": 90.62,
    "performance": 92.31,
    "quality": 99.03,
    "oee": 82.85
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

## 📤 4. Import Excel/CSV Endpoint

### 4.1 Import Data from CSV

**Endpoint:** `POST /API/import_excel.php`

**Headers:**

```
Content-Type: multipart/form-data
```

**Form Data:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| file | FILE | Yes | CSV file to import |

**CSV Format:**

```csv
tanggal,shift,status_mesin,waktu_mesin_on,waktu_mesin_off,downtime_mesin,waktu_operasi_er,jumlah_green_tire,cycle_time_mesin,kecepatan_produksi,target_produksi,jumlah_reject,persentase_reject,jumlah_produk_ok,alarm_mesin,mode_mesin
2024-01-15,Shift 1,ON,08:00:00,16:00:00,45.5,435.5,1200,2.5,150.75,1300,12,1.0,1188,Sensor error,Auto
```

**Example using cURL:**

```bash
curl -X POST \
  -F "file=@data.csv" \
  http://localhost/OEE_Monitoring_API/API/import_excel.php
```

**Example using Postman:**

1. Method: POST
2. URL: `http://localhost/OEE_Monitoring_API/API/import_excel.php`
3. Body: form-data
4. Key: `file` (type: File)
5. Value: Select CSV file

**Example Response:**

```json
{
  "success": true,
  "data": {
    "message": "Import completed",
    "summary": {
      "total_processed": 100,
      "imported": 95,
      "failed": 5,
      "success_rate": 95.0
    },
    "details": [
      {
        "row": 1,
        "status": "success",
        "id": 123
      },
      {
        "row": 2,
        "status": "failed",
        "error": "Invalid date format"
      }
    ]
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

---

## ❌ Error Handling

### Error Response Format

```json
{
  "success": false,
  "error": "Error message description",
  "timestamp": "2025-12-18 10:30:00"
}
```

### HTTP Status Codes

| Code | Description                             |
| ---- | --------------------------------------- |
| 200  | OK - Request successful                 |
| 201  | Created - Resource created successfully |
| 400  | Bad Request - Invalid input             |
| 404  | Not Found - Resource not found          |
| 405  | Method Not Allowed                      |
| 500  | Internal Server Error                   |
| 503  | Service Unavailable                     |

### Common Errors

**400 Bad Request:**

```json
{
  "success": false,
  "error": "Missing required fields: tanggal, shift, status_mesin",
  "timestamp": "2025-12-18 10:30:00"
}
```

**404 Not Found:**

```json
{
  "success": false,
  "error": "Data not found",
  "timestamp": "2025-12-18 10:30:00"
}
```

**500 Internal Server Error:**

```json
{
  "success": false,
  "error": "Database connection failed. Please check configuration.",
  "timestamp": "2025-12-18 10:30:00"
}
```

---

## 🧪 Testing

### Using cURL

**1. Get All Data:**

```bash
curl -X GET "http://localhost/OEE_Monitoring_API/API/oee.php?page=1&limit=10"
```

**2. Create Data:**

```bash
curl -X POST http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d '{
    "tanggal": "2024-01-15",
    "shift": "Shift 1",
    "status_mesin": "ON",
    "jumlah_green_tire": 1200,
    "target_produksi": 1300
  }'
```

**3. Update Data:**

```bash
curl -X PUT http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "jumlah_green_tire": 1250
  }'
```

**4. Delete Data:**

```bash
curl -X DELETE http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1
  }'
```

### Using Postman

1. Import collection (coming soon)
2. Set environment variables
3. Run tests

---

## 📝 Notes

- Semua response dalam format JSON
- Timestamp menggunakan format: `YYYY-MM-DD HH:MM:SS`
- Date menggunakan format: `YYYY-MM-DD`
- Time menggunakan format: `HH:MM:SS`
- Untuk production, tambahkan authentication (JWT/API Key)
- Enable HTTPS untuk production environment
- Backup database secara berkala

---

## 🆘 Support

Jika ada pertanyaan atau issue, silakan hubungi:

- Email: your-email@example.com
- GitHub: https://github.com/yourusername

---

**Last Updated:** December 18, 2025  
**API Version:** 1.0
