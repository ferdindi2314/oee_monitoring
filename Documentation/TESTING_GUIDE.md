# 🧪 TESTING GUIDE

## 📋 Prerequisites

Sebelum testing, pastikan:

- ✅ XAMPP/WAMP sudah running
- ✅ Apache dan MySQL service aktif
- ✅ Database `oee_monitoring` sudah diimport
- ✅ File project ada di `C:\xampp\htdocs\OEE_Monitoring_API`
- ✅ mod_rewrite Apache enabled

---

## 🔧 Setup Testing Environment

### 1. Import Database

```sql
-- Via phpMyAdmin:
-- 1. Buka http://localhost/phpmyadmin
-- 2. Klik "Import"
-- 3. Pilih file: Database/oee_database.sql
-- 4. Klik "Go"

-- Via MySQL Command Line:
mysql -u root -p < Database/oee_database.sql
```

### 2. Verify Database

```sql
-- Check database exists
SHOW DATABASES LIKE 'oee_monitoring';

-- Check tables
USE oee_monitoring;
SHOW TABLES;

-- Check sample data
SELECT COUNT(*) FROM oee_data;
```

### 3. Test Apache Configuration

```bash
# Check mod_rewrite is loaded
# Edit httpd.conf and make sure this line is NOT commented:
LoadModule rewrite_module modules/mod_rewrite.so

# Restart Apache
```

---

## 🌐 Browser Testing

### Test 1: Basic API Access

**URL:** `http://localhost/OEE_Monitoring_API/API/oee.php`

**Expected Response:**

```json
{
  "success": true,
  "data": {
    "records": [...],
    "pagination": {...}
  },
  "timestamp": "2025-12-18 10:30:00"
}
```

**If Error:**

- Check Apache running
- Check file path correct
- Check database connection in Config/database.php

---

### Test 2: Get Single Data

**URL:** `http://localhost/OEE_Monitoring_API/API/oee.php?id=1`

**Expected Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "tanggal": "2024-01-15",
    "shift": "Shift 1",
    ...
  }
}
```

---

### Test 3: Get Statistics

**URL:** `http://localhost/OEE_Monitoring_API/API/oee.php?statistics=true&date=2024-01-15`

**Expected Response:**

```json
{
  "success": true,
  "data": {
    "total_records": 2,
    "total_production": 2450,
    "total_reject": 20,
    ...
  }
}
```

---

### Test 4: Calculate OEE

**URL:** `http://localhost/OEE_Monitoring_API/API/oee.php?oee_calculation=true&date=2024-01-15`

**Expected Response:**

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

---

### Test 5: Dashboard Summary

**URL:** `http://localhost/OEE_Monitoring_API/API/dashboard.php?summary=true`

**Expected Response:**

```json
{
  "success": true,
  "data": {
    "date": "2025-12-18",
    "statistics": {...},
    "oee_calculation": {...},
    "shift_data": [...],
    "alarm_summary": [...]
  }
}
```

---

## 📮 Postman Testing

### Import Collection

1. Open Postman
2. Click "Import"
3. Select file: `Documentation/OEE_Monitoring_API.postman_collection.json`
4. Collection will be imported with all endpoints

### Set Environment Variables

1. Click Environments
2. Create new environment: "OEE Monitoring Local"
3. Add variable:
   - Variable: `base_url`
   - Value: `http://localhost/OEE_Monitoring_API/API`
4. Save and select environment

### Test Endpoints

#### 1. GET All Data

- Select: `OEE Data > Get All Data`
- Click "Send"
- Check status: 200 OK
- Verify response has `records` array

#### 2. Create New Data

- Select: `OEE Data > Create New Data`
- Edit Body if needed
- Click "Send"
- Check status: 201 Created
- Save the returned `id` for next tests

#### 3. Update Data

- Select: `OEE Data > Update Data`
- Change `id` to the saved id from create
- Edit some fields
- Click "Send"
- Check status: 200 OK

#### 4. Delete Data

- Select: `OEE Data > Delete Data`
- Change `id` to test id
- Click "Send"
- Check status: 200 OK

#### 5. Import CSV

- Select: `Import > Import CSV File`
- In Body > form-data:
  - Key: `file`
  - Type: File
  - Value: Select `Documentation/sample_import.csv`
- Click "Send"
- Check response shows import summary

---

## 💻 cURL Testing

### Test 1: GET Request

```bash
curl -X GET "http://localhost/OEE_Monitoring_API/API/oee.php?page=1&limit=10"
```

**Expected:** JSON response with data

---

### Test 2: GET with Filters

```bash
curl -X GET "http://localhost/OEE_Monitoring_API/API/oee.php?start_date=2024-01-01&end_date=2024-01-31&shift=Shift%201"
```

---

### Test 3: POST Create

```bash
curl -X POST http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d "{\"tanggal\":\"2024-01-20\",\"shift\":\"Shift 1\",\"status_mesin\":\"ON\",\"jumlah_green_tire\":1200,\"target_produksi\":1300}"
```

**Expected:** Response with new ID

---

### Test 4: PUT Update

```bash
curl -X PUT http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d "{\"id\":1,\"jumlah_green_tire\":1250}"
```

---

### Test 5: DELETE

```bash
curl -X DELETE http://localhost/OEE_Monitoring_API/API/oee.php \
  -H "Content-Type: application/json" \
  -d "{\"id\":1}"
```

---

### Test 6: Import CSV

```bash
curl -X POST http://localhost/OEE_Monitoring_API/API/import_excel.php \
  -F "file=@Documentation/sample_import.csv"
```

---

### Test 7: Statistics

```bash
curl -X GET "http://localhost/OEE_Monitoring_API/API/oee.php?statistics=true&date=2024-01-15"
```

---

### Test 8: OEE Calculation

```bash
curl -X GET "http://localhost/OEE_Monitoring_API/API/oee.php?oee_calculation=true&date=2024-01-15"
```

---

### Test 9: Dashboard Summary

```bash
curl -X GET "http://localhost/OEE_Monitoring_API/API/dashboard.php?summary=true"
```

---

### Test 10: Real-time Data

```bash
curl -X GET "http://localhost/OEE_Monitoring_API/API/dashboard.php?realtime=true"
```

---

## 🐛 Common Issues & Solutions

### Issue 1: 404 Not Found

**Symptoms:**

```json
{
  "success": false,
  "error": "Not Found"
}
```

**Solutions:**

1. Check Apache running
2. Verify file path: `C:\xampp\htdocs\OEE_Monitoring_API\`
3. Check .htaccess file exists
4. Enable mod_rewrite in httpd.conf
5. Restart Apache

---

### Issue 2: Database Connection Error

**Symptoms:**

```json
{
  "success": false,
  "error": "Database connection failed"
}
```

**Solutions:**

1. Check MySQL running
2. Verify credentials in `Config/database.php`
3. Check database `oee_monitoring` exists
4. Test connection:
   ```php
   php -r "new PDO('mysql:host=localhost;dbname=oee_monitoring', 'root', '');"
   ```

---

### Issue 3: Invalid JSON Response

**Symptoms:**

- HTML output instead of JSON
- PHP errors visible

**Solutions:**

1. Check PHP error log: `C:\xampp\php\logs\php_error_log`
2. Fix syntax errors in PHP files
3. Disable `display_errors` in production

---

### Issue 4: CORS Error

**Symptoms:**

```
Access to XMLHttpRequest has been blocked by CORS policy
```

**Solutions:**

1. Check headers in API files
2. Verify .htaccess CORS headers
3. Clear browser cache

---

### Issue 5: Import CSV Failed

**Symptoms:**

```json
{
  "success": false,
  "error": "No file uploaded"
}
```

**Solutions:**

1. Check `upload_max_filesize` in php.ini
2. Check `post_max_size` in php.ini
3. Verify file permissions
4. Check CSV format matches sample

---

## ✅ Test Checklist

### Basic Tests

- [ ] Apache & MySQL running
- [ ] Database imported successfully
- [ ] GET all data works
- [ ] GET single data works
- [ ] Pagination works
- [ ] Filtering works

### CRUD Tests

- [ ] POST create data works
- [ ] PUT update data works
- [ ] DELETE data works
- [ ] Error handling works (404, 400)

### Advanced Tests

- [ ] Statistics calculation correct
- [ ] OEE calculation correct
- [ ] Trend data works
- [ ] Dashboard summary works
- [ ] Real-time data works

### Import Tests

- [ ] CSV import successful
- [ ] Import report shows correct summary
- [ ] Failed rows reported correctly
- [ ] Empty rows skipped

### Performance Tests

- [ ] Response time < 1 second
- [ ] Large dataset pagination works
- [ ] Concurrent requests handled

---

## 📊 Expected Test Results

### Sample Data Count

After importing `oee_database.sql`:

- Total records in `oee_data`: 3
- Date range: 2024-01-15 to 2024-01-16

After importing `sample_import.csv`:

- Additional records: 10
- Total: 13

### OEE Calculation (2024-01-15)

Expected values (approximate):

- Availability: 88-92%
- Performance: 90-95%
- Quality: 98-100%
- OEE: 80-88%

---

## 🎓 Testing Best Practices

1. **Start Simple:** Test GET before POST/PUT/DELETE
2. **Check Status Codes:** Always verify HTTP status
3. **Validate Response:** Check JSON structure matches docs
4. **Test Edge Cases:**
   - Empty data
   - Invalid ID
   - Invalid dates
   - Missing required fields
5. **Clean Up:** Delete test data after testing
6. **Use Postman:** Save successful requests for future reference

---

## 📝 Test Report Template

```
TEST REPORT
===========
Date: [Date]
Tester: [Name]

Environment:
- OS: Windows
- PHP Version: [version]
- MySQL Version: [version]
- Browser: [browser]

Tests Executed:
1. GET All Data - ✅ PASS / ❌ FAIL
   Response Time: [ms]
   Notes: [any notes]

2. POST Create Data - ✅ PASS / ❌ FAIL
   Response Time: [ms]
   Notes: [any notes]

[... continue for all tests ...]

Issues Found:
1. [Issue description]
2. [Issue description]

Overall Status: ✅ PASS / ⚠️ PARTIAL / ❌ FAIL
```

---

## 🚀 Next Steps After Testing

1. Fix any issues found
2. Document any changes
3. Retest failed cases
4. Update documentation if needed
5. Prepare for deployment

---

**Happy Testing! 🎉**

For questions or issues, see:

- [API Documentation](API_DOCUMENTATION.md)
- [API Endpoints](API_ENDPOINTS.md)
- [Delivery Checklist](DELIVERY_CHECKLIST.md)
