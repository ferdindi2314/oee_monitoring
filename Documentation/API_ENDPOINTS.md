# 📋 API ENDPOINTS QUICK REFERENCE

**Base URL:** `http://localhost/OEE_Monitoring_API/API/`

---

## 🔵 OEE DATA ENDPOINTS

### 1. GET All Data (with filters)

```
GET /oee.php
```

**Query Parameters:**

- `start_date` - Filter tanggal mulai (YYYY-MM-DD)
- `end_date` - Filter tanggal akhir (YYYY-MM-DD)
- `shift` - Filter shift (Shift 1/2/3)
- `status_mesin` - Filter status (ON/OFF)
- `page` - Page number (default: 1)
- `limit` - Records per page (default: 100)

**Example:**

```
GET /oee.php?start_date=2024-01-01&end_date=2024-01-31&shift=Shift%201&page=1&limit=50
```

---

### 2. GET Single Data by ID

```
GET /oee.php?id={id}
```

**Example:**

```
GET /oee.php?id=1
```

---

### 3. POST Create New Data

```
POST /oee.php
Content-Type: application/json

Body:
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
  "alarm_mesin": "Sensor error",
  "mode_mesin": "Auto"
}
```

**Required Fields:**

- `tanggal` (DATE)
- `shift` (STRING)
- `status_mesin` (STRING)

---

### 4. PUT Update Data

```
PUT /oee.php
Content-Type: application/json

Body:
{
  "id": 1,
  "jumlah_green_tire": 1250,
  "jumlah_produk_ok": 1238
}
```

---

### 5. DELETE Data

```
DELETE /oee.php
Content-Type: application/json

Body:
{
  "id": 1
}
```

---

## 📊 STATISTICS & CALCULATION ENDPOINTS

### 6. GET Statistics

```
GET /oee.php?statistics=true&date=2024-01-15&shift=Shift%201
```

**Response:**

```json
{
  "total_records": 3,
  "total_production": 3600,
  "total_reject": 35,
  "avg_reject_percentage": "0.97",
  "avg_downtime": "45.17",
  "target_achievement": "92.31"
}
```

---

### 7. GET OEE Calculation

```
GET /oee.php?oee_calculation=true&date=2024-01-15
```

**Response:**

```json
{
  "availability": 90.62,
  "performance": 92.31,
  "quality": 99.03,
  "oee": 82.85
}
```

---

### 8. GET Trend Data

```
GET /oee.php?trend=true&days=7
```

**Response:**

```json
{
  "tanggal": ["2024-01-15", "2024-01-16", ...],
  "total_production": [3600, 3700, ...],
  "total_reject": [35, 30, ...],
  "avg_downtime": [45.5, 38.2, ...]
}
```

---

## 📈 DASHBOARD ENDPOINTS

### 9. GET Dashboard Summary

```
GET /dashboard.php?summary=true&date=2024-01-15
```

**Response:**

```json
{
  "date": "2024-01-15",
  "statistics": {...},
  "oee_calculation": {...},
  "shift_data": [...],
  "alarm_summary": [...]
}
```

---

### 10. GET Real-time Data

```
GET /dashboard.php?realtime=true
```

**Response:**

```json
{
  "timestamp": "2025-12-18 10:30:00",
  "current_shift": "Shift 2",
  "machine_status": {...},
  "production_today": {...},
  "downtime_today": {...},
  "alarms_active": 0
}
```

---

### 11. GET Production Trend

```
GET /dashboard.php?trend=true&days=30
```

**Response:**

```json
{
  "labels": ["2024-01-15", ...],
  "production": [3600, ...],
  "reject": [35, ...],
  "downtime": [45.5, ...]
}
```

---

### 12. GET OEE Trend

```
GET /dashboard.php?oee_trend=true&days=30
```

**Response:**

```json
{
  "labels": ["2024-01-15", ...],
  "availability": [90.62, ...],
  "performance": [92.31, ...],
  "quality": [99.03, ...],
  "oee": [82.85, ...]
}
```

---

## 📤 IMPORT ENDPOINT

### 13. POST Import CSV

```
POST /import_excel.php
Content-Type: multipart/form-data

Form Data:
- file: [your_csv_file.csv]
```

**CSV Format:**

```csv
tanggal,shift,status_mesin,waktu_mesin_on,waktu_mesin_off,downtime_mesin,waktu_operasi_er,jumlah_green_tire,cycle_time_mesin,kecepatan_produksi,target_produksi,jumlah_reject,persentase_reject,jumlah_produk_ok,alarm_mesin,mode_mesin
2024-01-15,Shift 1,ON,08:00:00,16:00:00,45.5,435.5,1200,2.5,150.75,1300,12,1.0,1188,Sensor error,Auto
```

**Response:**

```json
{
  "message": "Import completed",
  "summary": {
    "total_processed": 100,
    "imported": 95,
    "failed": 5,
    "success_rate": 95.0
  }
}
```

---

## 📝 RESPONSE FORMAT

### Success Response

```json
{
  "success": true,
  "data": {...},
  "timestamp": "2025-12-18 10:30:00"
}
```

### Error Response

```json
{
  "success": false,
  "error": "Error message",
  "timestamp": "2025-12-18 10:30:00"
}
```

---

## 🔐 HTTP STATUS CODES

| Code | Description                    |
| ---- | ------------------------------ |
| 200  | OK - Request successful        |
| 201  | Created - Resource created     |
| 400  | Bad Request - Invalid input    |
| 404  | Not Found - Resource not found |
| 405  | Method Not Allowed             |
| 500  | Internal Server Error          |
| 503  | Service Unavailable            |

---

## 🧪 TESTING WITH CURL

### Get Data

```bash
curl "http://localhost/OEE_Monitoring_API/API/oee.php?page=1&limit=10"
```

### Create Data

```bash
curl -X POST http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d '{"tanggal":"2024-01-15","shift":"Shift 1","status_mesin":"ON"}'
```

### Update Data

```bash
curl -X PUT http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d '{"id":1,"jumlah_green_tire":1250}'
```

### Delete Data

```bash
curl -X DELETE http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d '{"id":1}'
```

### Import CSV

```bash
curl -X POST http://localhost/OEE_Monitoring_API/API/import_excel.php \
  -F "file=@sample_import.csv"
```

---

## 📚 NOTES

- Semua response dalam format JSON
- Date format: `YYYY-MM-DD`
- Time format: `HH:MM:SS`
- DateTime format: `YYYY-MM-DD HH:MM:SS`
- CORS enabled untuk semua endpoint
- Pagination default: page=1, limit=100

---

**For detailed documentation, see:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

**Last Updated:** December 18, 2025
