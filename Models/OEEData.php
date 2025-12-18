<?php
/**
 * OEE Data Model
 * Handle semua operasi CRUD untuk data OEE
 */

class OEEData {
    private $conn;
    private $table = "oee_data";

    // Properties sesuai dengan field di tabel
    public $id;
    public $tanggal;
    public $shift;
    public $status_mesin;
    public $waktu_mesin_on;
    public $waktu_mesin_off;
    public $downtime_mesin;
    public $waktu_operasi_er;
    public $jumlah_green_tire;
    public $cycle_time_mesin;
    public $kecepatan_produksi;
    public $target_produksi;
    public $jumlah_reject;
    public $persentase_reject;
    public $jumlah_produk_ok;
    public $alarm_mesin;
    public $mode_mesin;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * CREATE - Tambah data OEE baru
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                SET tanggal = :tanggal,
                    shift = :shift,
                    status_mesin = :status_mesin,
                    waktu_mesin_on = :waktu_mesin_on,
                    waktu_mesin_off = :waktu_mesin_off,
                    downtime_mesin = :downtime_mesin,
                    waktu_operasi_er = :waktu_operasi_er,
                    jumlah_green_tire = :jumlah_green_tire,
                    cycle_time_mesin = :cycle_time_mesin,
                    kecepatan_produksi = :kecepatan_produksi,
                    target_produksi = :target_produksi,
                    jumlah_reject = :jumlah_reject,
                    persentase_reject = :persentase_reject,
                    jumlah_produk_ok = :jumlah_produk_ok,
                    alarm_mesin = :alarm_mesin,
                    mode_mesin = :mode_mesin";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->tanggal = htmlspecialchars(strip_tags($this->tanggal));
        $this->shift = htmlspecialchars(strip_tags($this->shift));
        $this->status_mesin = htmlspecialchars(strip_tags($this->status_mesin));
        
        // Bind parameters
        $stmt->bindParam(':tanggal', $this->tanggal);
        $stmt->bindParam(':shift', $this->shift);
        $stmt->bindParam(':status_mesin', $this->status_mesin);
        $stmt->bindParam(':waktu_mesin_on', $this->waktu_mesin_on);
        $stmt->bindParam(':waktu_mesin_off', $this->waktu_mesin_off);
        $stmt->bindParam(':downtime_mesin', $this->downtime_mesin);
        $stmt->bindParam(':waktu_operasi_er', $this->waktu_operasi_er);
        $stmt->bindParam(':jumlah_green_tire', $this->jumlah_green_tire);
        $stmt->bindParam(':cycle_time_mesin', $this->cycle_time_mesin);
        $stmt->bindParam(':kecepatan_produksi', $this->kecepatan_produksi);
        $stmt->bindParam(':target_produksi', $this->target_produksi);
        $stmt->bindParam(':jumlah_reject', $this->jumlah_reject);
        $stmt->bindParam(':persentase_reject', $this->persentase_reject);
        $stmt->bindParam(':jumlah_produk_ok', $this->jumlah_produk_ok);
        $stmt->bindParam(':alarm_mesin', $this->alarm_mesin);
        $stmt->bindParam(':mode_mesin', $this->mode_mesin);

        try {
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * READ - Ambil semua data dengan filter
     */
    public function read($filters = []) {
        $query = "SELECT * FROM " . $this->table;
        $conditions = [];
        $params = [];

        // Apply filters
        if (!empty($filters['start_date'])) {
            $conditions[] = "tanggal >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $conditions[] = "tanggal <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['shift'])) {
            $conditions[] = "shift = :shift";
            $params[':shift'] = $filters['shift'];
        }
        
        if (!empty($filters['status_mesin'])) {
            $conditions[] = "status_mesin = :status_mesin";
            $params[':status_mesin'] = $filters['status_mesin'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY tanggal DESC, created_at DESC";
        
        // Pagination
        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 100;
        $offset = ($page - 1) * $limit;
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }

    /**
     * READ ONE - Ambil data spesifik berdasarkan ID
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            // Map semua field
            $this->id = $row['id'];
            $this->tanggal = $row['tanggal'];
            $this->shift = $row['shift'];
            $this->status_mesin = $row['status_mesin'];
            $this->waktu_mesin_on = $row['waktu_mesin_on'];
            $this->waktu_mesin_off = $row['waktu_mesin_off'];
            $this->downtime_mesin = $row['downtime_mesin'];
            $this->waktu_operasi_er = $row['waktu_operasi_er'];
            $this->jumlah_green_tire = $row['jumlah_green_tire'];
            $this->cycle_time_mesin = $row['cycle_time_mesin'];
            $this->kecepatan_produksi = $row['kecepatan_produksi'];
            $this->target_produksi = $row['target_produksi'];
            $this->jumlah_reject = $row['jumlah_reject'];
            $this->persentase_reject = $row['persentase_reject'];
            $this->jumlah_produk_ok = $row['jumlah_produk_ok'];
            $this->alarm_mesin = $row['alarm_mesin'];
            $this->mode_mesin = $row['mode_mesin'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * UPDATE - Update data existing
     */
    public function update() {
        $query = "UPDATE " . $this->table . "
                SET tanggal = :tanggal,
                    shift = :shift,
                    status_mesin = :status_mesin,
                    waktu_mesin_on = :waktu_mesin_on,
                    waktu_mesin_off = :waktu_mesin_off,
                    downtime_mesin = :downtime_mesin,
                    waktu_operasi_er = :waktu_operasi_er,
                    jumlah_green_tire = :jumlah_green_tire,
                    cycle_time_mesin = :cycle_time_mesin,
                    kecepatan_produksi = :kecepatan_produksi,
                    target_produksi = :target_produksi,
                    jumlah_reject = :jumlah_reject,
                    persentase_reject = :persentase_reject,
                    jumlah_produk_ok = :jumlah_produk_ok,
                    alarm_mesin = :alarm_mesin,
                    mode_mesin = :mode_mesin
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->tanggal = htmlspecialchars(strip_tags($this->tanggal));
        $this->shift = htmlspecialchars(strip_tags($this->shift));
        $this->status_mesin = htmlspecialchars(strip_tags($this->status_mesin));

        // Bind parameters
        $stmt->bindParam(':tanggal', $this->tanggal);
        $stmt->bindParam(':shift', $this->shift);
        $stmt->bindParam(':status_mesin', $this->status_mesin);
        $stmt->bindParam(':waktu_mesin_on', $this->waktu_mesin_on);
        $stmt->bindParam(':waktu_mesin_off', $this->waktu_mesin_off);
        $stmt->bindParam(':downtime_mesin', $this->downtime_mesin);
        $stmt->bindParam(':waktu_operasi_er', $this->waktu_operasi_er);
        $stmt->bindParam(':jumlah_green_tire', $this->jumlah_green_tire);
        $stmt->bindParam(':cycle_time_mesin', $this->cycle_time_mesin);
        $stmt->bindParam(':kecepatan_produksi', $this->kecepatan_produksi);
        $stmt->bindParam(':target_produksi', $this->target_produksi);
        $stmt->bindParam(':jumlah_reject', $this->jumlah_reject);
        $stmt->bindParam(':persentase_reject', $this->persentase_reject);
        $stmt->bindParam(':jumlah_produk_ok', $this->jumlah_produk_ok);
        $stmt->bindParam(':alarm_mesin', $this->alarm_mesin);
        $stmt->bindParam(':mode_mesin', $this->mode_mesin);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE - Hapus data
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * GET STATISTICS - Untuk dashboard
     */
    public function getStatistics($date = null, $shift = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $query = "SELECT 
                    COUNT(*) as total_records,
                    SUM(jumlah_green_tire) as total_production,
                    SUM(jumlah_reject) as total_reject,
                    AVG(persentase_reject) as avg_reject_percentage,
                    AVG(downtime_mesin) as avg_downtime,
                    AVG(kecepatan_produksi) as avg_production_speed,
                    SUM(CASE WHEN status_mesin = 'ON' THEN 1 ELSE 0 END) as machine_on_count,
                    SUM(CASE WHEN status_mesin = 'OFF' THEN 1 ELSE 0 END) as machine_off_count,
                    AVG(cycle_time_mesin) as avg_cycle_time,
                    (SUM(jumlah_green_tire) / NULLIF(SUM(target_produksi), 0)) * 100 as target_achievement
                  FROM " . $this->table . " 
                  WHERE tanggal = :date";
        
        $params = [':date' => $date];
        
        if ($shift) {
            $query .= " AND shift = :shift";
            $params[':shift'] = $shift;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * GET OEE CALCULATION - Hitung OEE
     */
    public function calculateOEE($date, $shift = null) {
        $query = "SELECT 
                    -- Availability
                    (1 - SUM(downtime_mesin) / (COUNT(*) * 480)) as availability,
                    
                    -- Performance
                    (SUM(jumlah_green_tire) / NULLIF(SUM(target_produksi), 0)) as performance,
                    
                    -- Quality
                    (1 - (SUM(jumlah_reject) / NULLIF(SUM(jumlah_green_tire), 0))) as quality
                  FROM " . $this->table . " 
                  WHERE tanggal = :date";
        
        $params = [':date' => $date];
        
        if ($shift) {
            $query .= " AND shift = :shift";
            $params[':shift'] = $shift;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $availability = $result['availability'] ?? 0;
            $performance = $result['performance'] ?? 0;
            $quality = $result['quality'] ?? 0;
            
            $oee = ($availability * $performance * $quality) * 100;
            
            return [
                'availability' => round($availability * 100, 2),
                'performance' => round($performance * 100, 2),
                'quality' => round($quality * 100, 2),
                'oee' => round($oee, 2)
            ];
        }
        
        return null;
    }

    /**
     * GET TREND DATA - Untuk chart
     */
    public function getTrendData($days = 7) {
        $query = "SELECT 
                    tanggal,
                    SUM(jumlah_green_tire) as total_production,
                    SUM(jumlah_reject) as total_reject,
                    AVG(kecepatan_produksi) as avg_speed,
                    AVG(downtime_mesin) as avg_downtime
                  FROM " . $this->table . " 
                  WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                  GROUP BY tanggal
                  ORDER BY tanggal ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>