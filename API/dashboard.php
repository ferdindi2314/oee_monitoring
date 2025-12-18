<?php

/**
 * Dashboard API
 * Special endpoints for dashboard data
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/OEEData.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = Database::getInstance();
$db = $database->getConnection();
$oeeData = new OEEData($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        // Get dashboard summary
        if (isset($_GET['summary']) && $_GET['summary'] == 'true') {
            $date = $_GET['date'] ?? date('Y-m-d');

            $summary = [
                'date' => $date,
                'statistics' => $oeeData->getStatistics($date),
                'oee_calculation' => $oeeData->calculateOEE($date),
                'shift_data' => getShiftData($db, $date),
                'alarm_summary' => getAlarmSummary($db, $date)
            ];

            jsonResponse($summary);
        }

        // Get real-time data
        else if (isset($_GET['realtime']) && $_GET['realtime'] == 'true') {
            $realtimeData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'current_shift' => getCurrentShift(),
                'machine_status' => getLatestMachineStatus($db),
                'production_today' => getProductionToday($db),
                'downtime_today' => getDowntimeToday($db),
                'alarms_active' => getActiveAlarms($db)
            ];

            jsonResponse($realtimeData);
        }

        // Get production trend
        else if (isset($_GET['trend']) && $_GET['trend'] == 'true') {
            $days = $_GET['days'] ?? 30;
            $trendData = $oeeData->getTrendData($days);

            // Format for chart
            $formattedData = [
                'labels' => [],
                'production' => [],
                'reject' => [],
                'downtime' => []
            ];

            foreach ($trendData as $row) {
                $formattedData['labels'][] = $row['tanggal'];
                $formattedData['production'][] = $row['total_production'];
                $formattedData['reject'][] = $row['total_reject'];
                $formattedData['downtime'][] = $row['avg_downtime'];
            }

            jsonResponse($formattedData);
        }

        // Get OEE trend
        else if (isset($_GET['oee_trend']) && $_GET['oee_trend'] == 'true') {
            $days = $_GET['days'] ?? 30;
            $oeeTrend = getOEETrend($db, $days);
            jsonResponse($oeeTrend);
        } else {
            errorResponse('Invalid dashboard endpoint', 400);
        }
    } catch (Exception $e) {
        errorResponse($e->getMessage(), 500);
    }
} else {
    errorResponse('Method not allowed', 405);
}

/**
 * Helper functions for dashboard
 */
function getShiftData($db, $date)
{
    $query = "SELECT 
                shift,
                SUM(jumlah_green_tire) as production,
                SUM(jumlah_reject) as reject,
                AVG(downtime_mesin) as downtime,
                AVG(kecepatan_produksi) as speed
              FROM oee_data 
              WHERE tanggal = :date
              GROUP BY shift
              ORDER BY shift";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':date', $date);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAlarmSummary($db, $date)
{
    $query = "SELECT 
                alarm_type,
                COUNT(*) as count,
                GROUP_CONCAT(DISTINCT description SEPARATOR '; ') as examples
              FROM alarm_details ad
              JOIN oee_data od ON ad.oee_data_id = od.id
              WHERE od.tanggal = :date
              GROUP BY alarm_type
              ORDER BY count DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':date', $date);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCurrentShift()
{
    $hour = date('H');
    if ($hour >= 6 && $hour < 14) {
        return 'Shift 1';
    } elseif ($hour >= 14 && $hour < 22) {
        return 'Shift 2';
    } else {
        return 'Shift 3';
    }
}

function getLatestMachineStatus($db)
{
    $query = "SELECT status_mesin, mode_mesin, alarm_mesin 
              FROM oee_data 
              ORDER BY created_at DESC 
              LIMIT 1";

    $stmt = $db->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProductionToday($db)
{
    $query = "SELECT 
                SUM(jumlah_green_tire) as total,
                SUM(jumlah_produk_ok) as ok,
                SUM(jumlah_reject) as reject
              FROM oee_data 
              WHERE tanggal = CURDATE()";

    $stmt = $db->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getDowntimeToday($db)
{
    $query = "SELECT 
                SUM(downtime_mesin) as total_downtime,
                COUNT(DISTINCT CASE WHEN downtime_mesin > 0 THEN id END) as downtime_events
              FROM oee_data 
              WHERE tanggal = CURDATE()";

    $stmt = $db->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getActiveAlarms($db)
{
    $query = "SELECT COUNT(*) as active_alarms 
              FROM alarm_details 
              WHERE status = 'active' 
              AND DATE(alarm_time) = CURDATE()";

    $stmt = $db->query($query);
    return $stmt->fetchColumn();
}

function getOEETrend($db, $days)
{
    $query = "SELECT 
                tanggal,
                (1 - AVG(downtime_mesin) / 480) * 100 as availability,
                (AVG(jumlah_green_tire) / NULLIF(AVG(target_produksi), 0)) * 100 as performance,
                (1 - (SUM(jumlah_reject) / NULLIF(SUM(jumlah_green_tire), 0))) * 100 as quality
              FROM oee_data 
              WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
              GROUP BY tanggal
              ORDER BY tanggal ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':days', $days, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [
        'labels' => [],
        'availability' => [],
        'performance' => [],
        'quality' => [],
        'oee' => []
    ];

    foreach ($data as $row) {
        $result['labels'][] = $row['tanggal'];
        $result['availability'][] = round($row['availability'], 2);
        $result['performance'][] = round($row['performance'], 2);
        $result['quality'][] = round($row['quality'], 2);

        $oee = ($row['availability'] / 100) * ($row['performance'] / 100) * ($row['quality'] / 100) * 100;
        $result['oee'][] = round($oee, 2);
    }

    return $result;
}
