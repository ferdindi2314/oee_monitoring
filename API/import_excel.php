<?php

/**
 * Excel Import API
 * Handle import data dari file Excel/CSV
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Check if file uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
            errorResponse('No file uploaded or upload error', 400);
        }

        $file = $_FILES['file']['tmp_name'];
        $fileType = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        $imported = 0;
        $failed = 0;
        $results = [];

        // Handle CSV file
        if ($fileType == 'csv') {
            $handle = fopen($file, "r");

            // Read header
            $header = fgetcsv($handle);

            // Map header to database fields
            $fieldMapping = [
                'tanggal' => 0,
                'shift' => 1,
                'status_mesin' => 2,
                'waktu_mesin_on' => 3,
                'waktu_mesin_off' => 4,
                'downtime_mesin' => 5,
                'waktu_operasi_er' => 6,
                'jumlah_green_tire' => 7,
                'cycle_time_mesin' => 8,
                'kecepatan_produksi' => 9,
                'target_produksi' => 10,
                'jumlah_reject' => 11,
                'persentase_reject' => 12,
                'jumlah_produk_ok' => 13,
                'alarm_mesin' => 14,
                'mode_mesin' => 15
            ];

            // Adjust mapping based on actual CSV header
            foreach ($header as $index => $column) {
                $column = strtolower(trim($column));
                foreach ($fieldMapping as $field => $currentIndex) {
                    if (strpos($column, $field) !== false) {
                        $fieldMapping[$field] = $index;
                        break;
                    }
                }
            }

            // Process rows
            while (($data = fgetcsv($handle)) !== FALSE) {
                try {
                    // Skip empty rows
                    if (count(array_filter($data)) == 0) {
                        continue;
                    }

                    $oeeData->tanggal = isset($data[$fieldMapping['tanggal']]) ? $data[$fieldMapping['tanggal']] : date('Y-m-d');
                    $oeeData->shift = isset($data[$fieldMapping['shift']]) ? $data[$fieldMapping['shift']] : 'Shift 1';
                    $oeeData->status_mesin = isset($data[$fieldMapping['status_mesin']]) ? $data[$fieldMapping['status_mesin']] : 'ON';
                    $oeeData->waktu_mesin_on = isset($data[$fieldMapping['waktu_mesin_on']]) ? $data[$fieldMapping['waktu_mesin_on']] : null;
                    $oeeData->waktu_mesin_off = isset($data[$fieldMapping['waktu_mesin_off']]) ? $data[$fieldMapping['waktu_mesin_off']] : null;
                    $oeeData->downtime_mesin = isset($data[$fieldMapping['downtime_mesin']]) ? floatval($data[$fieldMapping['downtime_mesin']]) : 0;
                    $oeeData->waktu_operasi_er = isset($data[$fieldMapping['waktu_operasi_er']]) ? floatval($data[$fieldMapping['waktu_operasi_er']]) : 0;
                    $oeeData->jumlah_green_tire = isset($data[$fieldMapping['jumlah_green_tire']]) ? intval($data[$fieldMapping['jumlah_green_tire']]) : 0;
                    $oeeData->cycle_time_mesin = isset($data[$fieldMapping['cycle_time_mesin']]) ? floatval($data[$fieldMapping['cycle_time_mesin']]) : 0;
                    $oeeData->kecepatan_produksi = isset($data[$fieldMapping['kecepatan_produksi']]) ? floatval($data[$fieldMapping['kecepatan_produksi']]) : 0;
                    $oeeData->target_produksi = isset($data[$fieldMapping['target_produksi']]) ? intval($data[$fieldMapping['target_produksi']]) : 0;
                    $oeeData->jumlah_reject = isset($data[$fieldMapping['jumlah_reject']]) ? intval($data[$fieldMapping['jumlah_reject']]) : 0;
                    $oeeData->persentase_reject = isset($data[$fieldMapping['persentase_reject']]) ? floatval($data[$fieldMapping['persentase_reject']]) : 0;
                    $oeeData->jumlah_produk_ok = isset($data[$fieldMapping['jumlah_produk_ok']]) ? intval($data[$fieldMapping['jumlah_produk_ok']]) : 0;
                    $oeeData->alarm_mesin = isset($data[$fieldMapping['alarm_mesin']]) ? $data[$fieldMapping['alarm_mesin']] : '';
                    $oeeData->mode_mesin = isset($data[$fieldMapping['mode_mesin']]) ? $data[$fieldMapping['mode_mesin']] : 'Auto';

                    $createdId = $oeeData->create();
                    if ($createdId) {
                        $imported++;
                        $results[] = [
                            'row' => $imported + $failed + 1,
                            'status' => 'success',
                            'id' => $createdId
                        ];
                    } else {
                        $failed++;
                        $results[] = [
                            'row' => $imported + $failed + 1,
                            'status' => 'failed',
                            'error' => 'Database error'
                        ];
                    }
                } catch (Exception $e) {
                    $failed++;
                    $results[] = [
                        'row' => $imported + $failed + 1,
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            fclose($handle);
        } else {
            errorResponse('Unsupported file type. Please upload CSV file.', 400);
        }

        jsonResponse([
            'message' => 'Import completed',
            'summary' => [
                'total_processed' => $imported + $failed,
                'imported' => $imported,
                'failed' => $failed,
                'success_rate' => ($imported + $failed) > 0 ? round(($imported / ($imported + $failed)) * 100, 2) : 0
            ],
            'details' => $results
        ]);
    } catch (Exception $e) {
        errorResponse('Import failed: ' . $e->getMessage(), 500);
    }
} else {
    errorResponse('Method not allowed', 405);
}
