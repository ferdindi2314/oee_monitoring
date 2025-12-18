<?php

/**
 * Validation Helper Functions
 * Fungsi-fungsi untuk validasi data input
 */

class Validator
{

    /**
     * Validate date format (YYYY-MM-DD)
     */
    public static function validateDate($date)
    {
        if (empty($date)) {
            return false;
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate time format (HH:MM:SS)
     */
    public static function validateTime($time)
    {
        if (empty($time)) {
            return true; // Allow null time
        }

        $t = DateTime::createFromFormat('H:i:s', $time);
        return $t && $t->format('H:i:s') === $time;
    }

    /**
     * Validate shift value
     */
    public static function validateShift($shift)
    {
        $validShifts = ['Shift 1', 'Shift 2', 'Shift 3'];
        return in_array($shift, $validShifts);
    }

    /**
     * Validate machine status
     */
    public static function validateMachineStatus($status)
    {
        $validStatus = ['ON', 'OFF'];
        return in_array($status, $validStatus);
    }

    /**
     * Validate machine mode
     */
    public static function validateMachineMode($mode)
    {
        $validModes = ['Auto', 'Manual'];
        return in_array($mode, $validModes);
    }

    /**
     * Validate numeric value (positive)
     */
    public static function validatePositiveNumber($value, $allowZero = true)
    {
        if (!is_numeric($value)) {
            return false;
        }

        $num = floatval($value);
        return $allowZero ? ($num >= 0) : ($num > 0);
    }

    /**
     * Validate integer value
     */
    public static function validateInteger($value, $min = 0, $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }

        $num = intval($value);

        if ($num < $min) {
            return false;
        }

        if ($max !== null && $num > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate percentage (0-100)
     */
    public static function validatePercentage($value)
    {
        if (!is_numeric($value)) {
            return false;
        }

        $num = floatval($value);
        return $num >= 0 && $num <= 100;
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString($string)
    {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate OEE data object
     */
    public static function validateOEEData($data)
    {
        $errors = [];

        // Required fields
        if (empty($data->tanggal)) {
            $errors[] = 'Tanggal is required';
        } else if (!self::validateDate($data->tanggal)) {
            $errors[] = 'Invalid date format (use YYYY-MM-DD)';
        }

        if (empty($data->shift)) {
            $errors[] = 'Shift is required';
        } else if (!self::validateShift($data->shift)) {
            $errors[] = 'Invalid shift value (use: Shift 1, Shift 2, or Shift 3)';
        }

        if (empty($data->status_mesin)) {
            $errors[] = 'Status mesin is required';
        } else if (!self::validateMachineStatus($data->status_mesin)) {
            $errors[] = 'Invalid machine status (use: ON or OFF)';
        }

        // Optional fields validation
        if (isset($data->waktu_mesin_on) && !empty($data->waktu_mesin_on)) {
            if (!self::validateTime($data->waktu_mesin_on)) {
                $errors[] = 'Invalid waktu_mesin_on format (use HH:MM:SS)';
            }
        }

        if (isset($data->waktu_mesin_off) && !empty($data->waktu_mesin_off)) {
            if (!self::validateTime($data->waktu_mesin_off)) {
                $errors[] = 'Invalid waktu_mesin_off format (use HH:MM:SS)';
            }
        }

        if (isset($data->downtime_mesin)) {
            if (!self::validatePositiveNumber($data->downtime_mesin)) {
                $errors[] = 'Downtime must be a positive number';
            }
        }

        if (isset($data->jumlah_green_tire)) {
            if (!self::validateInteger($data->jumlah_green_tire, 0)) {
                $errors[] = 'Jumlah green tire must be a positive integer';
            }
        }

        if (isset($data->jumlah_reject)) {
            if (!self::validateInteger($data->jumlah_reject, 0)) {
                $errors[] = 'Jumlah reject must be a positive integer';
            }
        }

        if (isset($data->persentase_reject)) {
            if (!self::validatePercentage($data->persentase_reject)) {
                $errors[] = 'Persentase reject must be between 0-100';
            }
        }

        if (isset($data->mode_mesin) && !empty($data->mode_mesin)) {
            if (!self::validateMachineMode($data->mode_mesin)) {
                $errors[] = 'Invalid mode mesin (use: Auto or Manual)';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate query parameters for filtering
     */
    public static function validateFilterParams($params)
    {
        $errors = [];

        if (isset($params['start_date']) && !empty($params['start_date'])) {
            if (!self::validateDate($params['start_date'])) {
                $errors[] = 'Invalid start_date format';
            }
        }

        if (isset($params['end_date']) && !empty($params['end_date'])) {
            if (!self::validateDate($params['end_date'])) {
                $errors[] = 'Invalid end_date format';
            }
        }

        if (isset($params['page'])) {
            if (!self::validateInteger($params['page'], 1)) {
                $errors[] = 'Page must be >= 1';
            }
        }

        if (isset($params['limit'])) {
            if (!self::validateInteger($params['limit'], 1, 1000)) {
                $errors[] = 'Limit must be between 1-1000';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

/**
 * Response formatter functions
 */
class ResponseFormatter
{

    public static function success($data = null, $message = null, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');

        $response = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    public static function error($message, $errors = [], $status = 400)
    {
        http_response_code($status);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    public static function paginated($data, $page, $limit, $total)
    {
        $totalPages = ceil($total / $limit);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total_records' => (int)$total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
}
