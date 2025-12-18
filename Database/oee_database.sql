-- ============================================
-- OEE MONITORING SYSTEM DATABASE
-- ============================================

CREATE DATABASE IF NOT EXISTS oee_monitoring;
USE oee_monitoring;

-- ============================================
-- TABLE: oee_data (Data Historis Utama)
-- ============================================
CREATE TABLE oee_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    shift VARCHAR(10) NOT NULL,
    status_mesin ENUM('ON', 'OFF') NOT NULL,
    waktu_mesin_on TIME,
    waktu_mesin_off TIME,
    downtime_mesin DECIMAL(10,2) COMMENT 'dalam menit',
    waktu_operasi_er DECIMAL(10,2) COMMENT 'dalam menit',
    jumlah_green_tire INT,
    cycle_time_mesin DECIMAL(10,2) COMMENT 'dalam menit',
    kecepatan_produksi DECIMAL(10,2) COMMENT 'unit/jam',
    target_produksi INT,
    jumlah_reject INT,
    persentase_reject DECIMAL(5,2),
    jumlah_produk_ok INT,
    alarm_mesin TEXT,
    mode_mesin ENUM('Auto', 'Manual'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tanggal (tanggal),
    INDEX idx_status (status_mesin),
    INDEX idx_shift (shift)
);

-- ============================================
-- TABLE: downtime_details (Detail Downtime)
-- ============================================
CREATE TABLE downtime_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    oee_data_id INT,
    start_time DATETIME,
    end_time DATETIME,
    duration DECIMAL(10,2) COMMENT 'dalam menit',
    reason TEXT,
    category ENUM('Mechanical', 'Electrical', 'Material', 'Operator', 'Other'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (oee_data_id) REFERENCES oee_data(id) ON DELETE CASCADE,
    INDEX idx_oee_id (oee_data_id),
    INDEX idx_category (category)
);

-- ============================================
-- TABLE: alarm_details (Detail Alarm)
-- ============================================
CREATE TABLE alarm_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    oee_data_id INT,
    alarm_time DATETIME,
    alarm_type ENUM('sensor', 'tekanan', 'misalignment', 'temperature', 'other'),
    alarm_code VARCHAR(50),
    description TEXT,
    status ENUM('active', 'resolved', 'acknowledged'),
    resolved_time DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (oee_data_id) REFERENCES oee_data(id) ON DELETE CASCADE,
    INDEX idx_oee_id (oee_data_id),
    INDEX idx_status (status),
    INDEX idx_type (alarm_type)
);

-- ============================================
-- TABLE: production_targets (Target Produksi)
-- ============================================
CREATE TABLE production_targets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE,
    shift VARCHAR(10),
    target_produksi INT,
    actual_produksi INT,
    deviation INT,
    achievement_percentage DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_date_shift (tanggal, shift),
    INDEX idx_tanggal (tanggal)
);

-- ============================================
-- SAMPLE DATA
-- ============================================
INSERT INTO oee_data (
    tanggal, shift, status_mesin, 
    waktu_mesin_on, waktu_mesin_off,
    downtime_mesin, waktu_operasi_er,
    jumlah_green_tire, cycle_time_mesin,
    kecepatan_produksi, target_produksi,
    jumlah_reject, persentase_reject,
    jumlah_produk_ok, alarm_mesin, mode_mesin
) VALUES 
('2024-01-15', 'Shift 1', 'ON', '08:00:00', '16:00:00', 
 45.5, 435.5, 1200, 2.5, 150.75, 1300, 
 12, 1.0, 1188, 'Sensor error pada jam 10:30', 'Auto'),
 
('2024-01-15', 'Shift 2', 'ON', '16:00:00', '00:00:00', 
 30.0, 450.0, 1250, 2.4, 155.0, 1300, 
 8, 0.64, 1242, '', 'Auto'),
 
('2024-01-16', 'Shift 1', 'ON', '08:00:00', '16:00:00', 
 60.0, 420.0, 1150, 2.6, 145.0, 1300, 
 15, 1.3, 1135, 'Pressure warning at 14:00', 'Manual');

-- ============================================
-- VIEW: oee_summary (View untuk Dashboard)
-- ============================================
CREATE VIEW oee_summary AS
SELECT 
    tanggal,
    shift,
    SUM(jumlah_green_tire) as total_production,
    SUM(jumlah_reject) as total_reject,
    AVG(persentase_reject) as avg_reject_rate,
    AVG(downtime_mesin) as avg_downtime,
    AVG(kecepatan_produksi) as avg_speed,
    SUM(CASE WHEN status_mesin = 'ON' THEN 1 ELSE 0 END) as on_count,
    COUNT(*) as total_records
FROM oee_data
GROUP BY tanggal, shift;