<?php

/**
 * OEE Main API Endpoint
 * Handle semua operasi CRUD untuk data OEE
 */

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include dependencies
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/OEEData.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get database connection
$database = Database::getInstance();
$db = $database->getConnection();
$oeeData = new OEEData($db);

// Get HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($oeeData);
            break;

        case 'POST':
            handlePostRequest($oeeData);
            break;

        case 'PUT':
            handlePutRequest($oeeData);
            break;

        case 'DELETE':
            handleDeleteRequest($oeeData);
            break;

        default:
            errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    errorResponse($e->getMessage(), 500);
}

/**
 * Handle GET requests
 */
function handleGetRequest($oeeData)
{
    // Check if specific ID requested
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $oeeData->id = $_GET['id'];

        if ($oeeData->readOne()) {
            $data = [
                'id' => $oeeData->id,
                'tanggal' => $oeeData->tanggal,
                'shift' => $oeeData->shift,
                'status_mesin' => $oeeData->status_mesin,
                'waktu_mesin_on' => $oeeData->waktu_mesin_on,
                'waktu_mesin_off' => $oeeData->waktu_mesin_off,
                'downtime_mesin' => $oeeData->downtime_mesin,
                'waktu_operasi_er' => $oeeData->waktu_operasi_er,
                'jumlah_green_tire' => $oeeData->jumlah_green_tire,
                'cycle_time_mesin' => $oeeData->cycle_time_mesin,
                'kecepatan_produksi' => $oeeData->kecepatan_produksi,
                'target_produksi' => $oeeData->target_produksi,
                'jumlah_reject' => $oeeData->jumlah_reject,
                'persentase_reject' => $oeeData->persentase_reject,
                'jumlah_produk_ok' => $oeeData->jumlah_produk_ok,
                'alarm_mesin' => $oeeData->alarm_mesin,
                'mode_mesin' => $oeeData->mode_mesin,
                'created_at' => $oeeData->created_at,
                'updated_at' => $oeeData->updated_at
            ];
            jsonResponse($data);
        } else {
            errorResponse('Data not found', 404);
        }
    }
    // Check if statistics requested
    else if (isset($_GET['statistics']) && $_GET['statistics'] == 'true') {
        $date = $_GET['date'] ?? date('Y-m-d');
        $shift = $_GET['shift'] ?? null;

        $stats = $oeeData->getStatistics($date, $shift);
        jsonResponse($stats);
    }
    // Check if OEE calculation requested
    else if (isset($_GET['oee_calculation']) && $_GET['oee_calculation'] == 'true') {
        $date = $_GET['date'] ?? date('Y-m-d');
        $shift = $_GET['shift'] ?? null;

        $oee = $oeeData->calculateOEE($date, $shift);
        jsonResponse($oee);
    }
    // Check if trend data requested
    else if (isset($_GET['trend']) && $_GET['trend'] == 'true') {
        $days = $_GET['days'] ?? 7;
        $trendData = $oeeData->getTrendData($days);
        jsonResponse($trendData);
    }
    // Get all records with filters
    else {
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'shift' => $_GET['shift'] ?? null,
            'status_mesin' => $_GET['status_mesin'] ?? null,
            'page' => $_GET['page'] ?? 1,
            'limit' => $_GET['limit'] ?? 100
        ];

        $stmt = $oeeData->read($filters);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $data_arr = [];
            $data_arr['records'] = [];
            $data_arr['pagination'] = [
                'page' => (int)$filters['page'],
                'limit' => (int)$filters['limit'],
                'total_records' => $num
            ];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data_item = [
                    "id" => $id,
                    "tanggal" => $tanggal,
                    "shift" => $shift,
                    "status_mesin" => $status_mesin,
                    "waktu_mesin_on" => $waktu_mesin_on,
                    "waktu_mesin_off" => $waktu_mesin_off,
                    "downtime_mesin" => $downtime_mesin,
                    "waktu_operasi_er" => $waktu_operasi_er,
                    "jumlah_green_tire" => $jumlah_green_tire,
                    "cycle_time_mesin" => $cycle_time_mesin,
                    "kecepatan_produksi" => $kecepatan_produksi,
                    "target_produksi" => $target_produksi,
                    "jumlah_reject" => $jumlah_reject,
                    "persentase_reject" => $persentase_reject,
                    "jumlah_produk_ok" => $jumlah_produk_ok,
                    "alarm_mesin" => $alarm_mesin,
                    "mode_mesin" => $mode_mesin,
                    "created_at" => $created_at,
                    "updated_at" => $updated_at
                ];

                $data_arr['records'][] = $data_item;
            }

            jsonResponse($data_arr);
        } else {
            jsonResponse(['records' => [], 'message' => 'No data found']);
        }
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($oeeData)
{
    $data = json_decode(file_get_contents("php://input"));

    // Validate required fields
    if (
        empty($data->tanggal) ||
        empty($data->shift) ||
        empty($data->status_mesin)
    ) {
        errorResponse('Missing required fields: tanggal, shift, status_mesin', 400);
    }

    // Set properties
    $oeeData->tanggal = $data->tanggal;
    $oeeData->shift = $data->shift;
    $oeeData->status_mesin = $data->status_mesin;
    $oeeData->waktu_mesin_on = $data->waktu_mesin_on ?? null;
    $oeeData->waktu_mesin_off = $data->waktu_mesin_off ?? null;
    $oeeData->downtime_mesin = $data->downtime_mesin ?? 0;
    $oeeData->waktu_operasi_er = $data->waktu_operasi_er ?? 0;
    $oeeData->jumlah_green_tire = $data->jumlah_green_tire ?? 0;
    $oeeData->cycle_time_mesin = $data->cycle_time_mesin ?? 0;
    $oeeData->kecepatan_produksi = $data->kecepatan_produksi ?? 0;
    $oeeData->target_produksi = $data->target_produksi ?? 0;
    $oeeData->jumlah_reject = $data->jumlah_reject ?? 0;
    $oeeData->persentase_reject = $data->persentase_reject ?? 0;
    $oeeData->jumlah_produk_ok = $data->jumlah_produk_ok ?? 0;
    $oeeData->alarm_mesin = $data->alarm_mesin ?? '';
    $oeeData->mode_mesin = $data->mode_mesin ?? 'Auto';

    // Create record
    $id = $oeeData->create();

    if ($id) {
        jsonResponse([
            'message' => 'Data created successfully',
            'id' => $id
        ], 201);
    } else {
        errorResponse('Unable to create data', 503);
    }
}

/**
 * Handle PUT requests
 */
function handlePutRequest($oeeData)
{
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->id)) {
        errorResponse('ID is required', 400);
    }

    $oeeData->id = $data->id;

    // Check if record exists
    if (!$oeeData->readOne()) {
        errorResponse('Data not found', 404);
    }

    // Update properties (only if provided)
    if (isset($data->tanggal)) $oeeData->tanggal = $data->tanggal;
    if (isset($data->shift)) $oeeData->shift = $data->shift;
    if (isset($data->status_mesin)) $oeeData->status_mesin = $data->status_mesin;
    if (isset($data->waktu_mesin_on)) $oeeData->waktu_mesin_on = $data->waktu_mesin_on;
    if (isset($data->waktu_mesin_off)) $oeeData->waktu_mesin_off = $data->waktu_mesin_off;
    if (isset($data->downtime_mesin)) $oeeData->downtime_mesin = $data->downtime_mesin;
    if (isset($data->waktu_operasi_er)) $oeeData->waktu_operasi_er = $data->waktu_operasi_er;
    if (isset($data->jumlah_green_tire)) $oeeData->jumlah_green_tire = $data->jumlah_green_tire;
    if (isset($data->cycle_time_mesin)) $oeeData->cycle_time_mesin = $data->cycle_time_mesin;
    if (isset($data->kecepatan_produksi)) $oeeData->kecepatan_produksi = $data->kecepatan_produksi;
    if (isset($data->target_produksi)) $oeeData->target_produksi = $data->target_produksi;
    if (isset($data->jumlah_reject)) $oeeData->jumlah_reject = $data->jumlah_reject;
    if (isset($data->persentase_reject)) $oeeData->persentase_reject = $data->persentase_reject;
    if (isset($data->jumlah_produk_ok)) $oeeData->jumlah_produk_ok = $data->jumlah_produk_ok;
    if (isset($data->alarm_mesin)) $oeeData->alarm_mesin = $data->alarm_mesin;
    if (isset($data->mode_mesin)) $oeeData->mode_mesin = $data->mode_mesin;

    if ($oeeData->update()) {
        jsonResponse(['message' => 'Data updated successfully']);
    } else {
        errorResponse('Unable to update data', 503);
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest($oeeData)
{
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->id)) {
        errorResponse('ID is required', 400);
    }

    $oeeData->id = $data->id;

    // Check if record exists
    if (!$oeeData->readOne()) {
        errorResponse('Data not found', 404);
    }

    if ($oeeData->delete()) {
        jsonResponse(['message' => 'Data deleted successfully']);
    } else {
        errorResponse('Unable to delete data', 503);
    }
}
