# 🏭 OEE Monitoring API

> REST API untuk monitoring Overall Equipment Effectiveness (OEE) menggunakan PHP Native dan MySQL

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-%3E%3D5.7-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

---

## 📖 Deskripsi

**OEE Monitoring API** adalah REST API yang dibangun dengan PHP Native untuk monitoring dan tracking data produksi mesin secara real-time. API ini mendukung import data dari Excel/CSV, perhitungan OEE otomatis, dan menyediakan dashboard monitoring yang lengkap.

### Fitur Utama:

- ✅ **CRUD Operations** - Create, Read, Update, Delete data OEE
- ✅ **Import Excel/CSV** - Import data produksi dari file CSV
- ✅ **Dashboard Monitoring** - Real-time monitoring dan statistik
- ✅ **OEE Calculation** - Perhitungan otomatis Availability, Performance, Quality, dan OEE
- ✅ **Trend Analysis** - Analisis trend produksi dan OEE
- ✅ **Filter & Pagination** - Filter data berdasarkan tanggal, shift, status
- ✅ **RESTful Design** - Mengikuti best practices REST API
- ✅ **CORS Support** - Siap diakses dari frontend modern

---

## 🎯 Use Case

API ini cocok untuk:

- Monitoring produksi mesin manufaktur
- Tracking downtime dan alarm mesin
- Analisis performa produksi
- Dashboard monitoring real-time
- Reporting dan analytics

---

## 🚀 Quick Start

### Prerequisites

Pastikan sudah terinstall:

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache Web Server dengan mod_rewrite
- XAMPP/WAMP/LAMP (recommended)

### Installation

1. **Clone atau Download Project**

   ```bash
   cd C:\xampp\htdocs
   # Copy folder OEE_Monitoring_API ke sini
   ```

2. **Enable Apache mod_rewrite**

   - Edit file `httpd.conf`
   - Pastikan line ini tidak di-comment:
     ```apache
     LoadModule rewrite_module modules/mod_rewrite.so
     ```
   - Restart Apache

3. **Setup Database**

   ```bash
   # Buka phpMyAdmin atau MySQL client
   # Import file: Database/oee_database.sql
   ```

4. **Configure Database Connection**

   Edit `Config/database.php`:

   ```php
   private $host = "localhost";
   private $db_name = "oee_monitoring";
   private $username = "root";
   private $password = "";
   ```

5. **Test API**
   ```bash
   # Buka browser dan akses:
   http://localhost/OEE_Monitoring_API/API/oee.php
   ```

---

## 📁 Struktur Project

```
OEE_Monitoring_API/
│
├── API/
│   ├── oee.php              # Main API endpoint (CRUD)
│   ├── dashboard.php        # Dashboard & monitoring endpoint
│   └── import_excel.php     # Import CSV endpoint
│
├── Config/
│   ├── database.php         # Database connection class
│   └── helpers.php          # Validation & response helpers
│
├── Models/
│   └── OEEData.php          # OEE Data model (business logic)
│
├── Database/
│   └── oee_database.sql     # Database schema & sample data
│
├── Documentation/
│   └── API_DOCUMENTATION.md # Complete API documentation
│
├── .htaccess                # Apache rewrite rules
└── README.md                # This file
```

---

## 🔌 API Endpoints

### Base URL

```
http://localhost/OEE_Monitoring_API/API/
```

### Main Endpoints

| Method | Endpoint                        | Description                            |
| ------ | ------------------------------- | -------------------------------------- |
| GET    | `/oee.php`                      | Get all data with filters & pagination |
| GET    | `/oee.php?id={id}`              | Get single data by ID                  |
| POST   | `/oee.php`                      | Create new data                        |
| PUT    | `/oee.php`                      | Update existing data                   |
| DELETE | `/oee.php`                      | Delete data                            |
| GET    | `/oee.php?statistics=true`      | Get statistics                         |
| GET    | `/oee.php?oee_calculation=true` | Get OEE calculation                    |
| GET    | `/dashboard.php?summary=true`   | Get dashboard summary                  |
| GET    | `/dashboard.php?realtime=true`  | Get real-time data                     |
| GET    | `/dashboard.php?trend=true`     | Get production trend                   |
| GET    | `/dashboard.php?oee_trend=true` | Get OEE trend                          |
| POST   | `/import_excel.php`             | Import data from CSV                   |

---

## 📊 Example Usage

### 1. Get All Data

```bash
GET /API/oee.php?start_date=2024-01-01&end_date=2024-01-31&page=1&limit=50
```

**Response:**

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
        "jumlah_green_tire": 1200,
        "target_produksi": 1300,
        ...
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 50,
      "total_records": 120
    }
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

### 2. Create New Data

```bash
POST /API/oee.php
Content-Type: application/json

{
  "tanggal": "2024-01-15",
  "shift": "Shift 1",
  "status_mesin": "ON",
  "jumlah_green_tire": 1200,
  "target_produksi": 1300
}
```

### 3. Get OEE Calculation

```bash
GET /API/oee.php?oee_calculation=true&date=2024-01-15
```

**Response:**

```json
{
  "success": true,
  "data": {
    "availability": 90.62,
    "performance": 92.31,
    "quality": 99.03,
    "oee": 82.85
  }
}
```

### 4. Import CSV

```bash
POST /API/import_excel.php
Content-Type: multipart/form-data

file: [your_csv_file.csv]
```

---

## 📝 Data Model

### OEE Metrics

Berdasarkan gambar metrik yang kamu kirim, API ini tracking:

| No  | Metrik                       | Jenis Data | Aspek OEE    |
| --- | ---------------------------- | ---------- | ------------ |
| 1   | Status Mesin (ON/OFF)        | Historis   | Availability |
| 2   | Downtime Mesin               | Historis   | Availability |
| 3   | Waktu Operasi Efektif        | Historis   | Availability |
| 4   | Jumlah Green Tire Diproduksi | Historis   | Quality      |
| 5   | Cycle Time Mesin             | Monitoring | Performance  |
| 6   | Kecepatan Produksi           | Monitoring | Performance  |
| 7   | Target Produksi              | Kontrol    | Performance  |
| 8   | Jumlah Reject Green Tire     | Historis   | Quality      |
| 9   | Persentase Reject            | Historis   | Quality      |
| 10  | Jumlah Produk OK             | Historis   | Quality      |
| 11  | Alarm Mesin                  | Monitoring | Availability |
| 12  | Mode Mesin (Auto/Manual)     | Monitoring | Availability |

### OEE Formula

```
OEE = Availability × Performance × Quality

Where:
- Availability = (Waktu Operasi - Downtime) / Waktu Operasi
- Performance = Actual Production / Target Production
- Quality = (Total Production - Reject) / Total Production
```

---

## 🗄️ Database Schema

### Main Tables

1. **oee_data** - Main historical data
2. **downtime_details** - Detailed downtime breakdown
3. **alarm_details** - Machine alarm details
4. **production_targets** - Production targets & achievement

Lihat `Database/oee_database.sql` untuk schema lengkap.

---

## 🧪 Testing

### Using cURL

**Get Data:**

```bash
curl "http://localhost/OEE_Monitoring_API/API/oee.php?page=1&limit=10"
```

**Create Data:**

```bash
curl -X POST http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d '{"tanggal":"2024-01-15","shift":"Shift 1","status_mesin":"ON"}'
```

### Using Postman

1. Import collection dari Documentation folder
2. Set base URL sebagai environment variable
3. Test semua endpoints

### Sample CSV File

Lihat `Documentation/sample_import.csv` untuk contoh format import.

---

## 📚 Full Documentation

Untuk dokumentasi lengkap, lihat:

- [API Documentation](Documentation/API_DOCUMENTATION.md)

Dokumentasi mencakup:

- Semua endpoint details
- Request/Response examples
- Error handling
- Query parameters
- Validation rules

---

## 🔒 Security Notes

⚠️ **IMPORTANT untuk Production:**

1. **Enable Authentication**

   - Tambahkan JWT atau API Key authentication
   - Jangan expose API tanpa auth di production

2. **Input Validation**

   - Validasi semua input dari user
   - Gunakan prepared statements (sudah implemented)

3. **HTTPS**

   - Gunakan HTTPS di production
   - Jangan kirim sensitive data via HTTP

4. **Database Security**

   - Ganti default password MySQL
   - Gunakan least privilege principle
   - Backup database secara berkala

5. **Error Messages**
   - Jangan expose detailed error di production
   - Log error ke file, bukan ke response

---

## 🛠️ Troubleshooting

### API tidak bisa diakses

- Pastikan Apache dan MySQL running
- Cek mod_rewrite sudah enabled
- Cek .htaccess file exists
- Cek error log: `C:\xampp\apache\logs\error.log`

### Database connection error

- Cek credentials di `Config/database.php`
- Pastikan MySQL service running
- Cek database `oee_monitoring` sudah dibuat

### Import CSV error

- Pastikan format CSV sesuai contoh
- Cek delimiter (comma)
- Cek encoding file (UTF-8)

---

## 📈 Roadmap

Future improvements:

- [ ] JWT Authentication
- [ ] Excel (.xlsx) import support
- [ ] Export to PDF report
- [ ] WebSocket real-time updates
- [ ] GraphQL API
- [ ] Docker support
- [ ] Unit tests

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the project
2. Create feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👤 Author

**Your Name**

- Email: your-email@example.com
- GitHub: [@yourusername](https://github.com/yourusername)

---

## 🙏 Acknowledgments

- OEE calculation based on industry standards
- RESTful design principles
- PHP best practices

---

## 📞 Support

Jika ada pertanyaan atau butuh bantuan:

- Create an issue di GitHub
- Email: your-email@example.com
- Documentation: [API Docs](Documentation/API_DOCUMENTATION.md)

---

**Built with ❤️ using PHP Native & MySQL**

Last Updated: December 18, 2025
"# oee_monitoring" 
