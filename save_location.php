<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// อนุญาตให้ทั้ง GET และ POST
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // รับข้อมูลจาก JSON
        $json_input = file_get_contents('php://input');
        $input = json_decode($json_input, true);
        
        if (!$input) {
            throw new Exception('Invalid JSON data');
        }
        
        $latitude = isset($input['lat']) ? floatval($input['lat']) : null;
        $longitude = isset($input['lng']) ? floatval($input['lng']) : null;
        $accuracy = isset($input['accuracy']) ? floatval($input['accuracy']) : null;
        $timestamp = isset($input['timestamp']) ? $input['timestamp'] : date('c');
        $userAgent = isset($input['userAgent']) ? substr($input['userAgent'], 0, 200) : 'Unknown';
        $device = isset($input['device']) ? $input['device'] : 'Unknown';
        
        // ตรวจสอบข้อมูลที่จำเป็น
        if ($latitude === null || $longitude === null) {
            throw new Exception('Missing latitude or longitude');
        }
        
        // ตรวจสอบค่าพิกัด
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new Exception('Invalid coordinate values');
        }
        
        // ชื่อไฟล์สำหรับเก็บข้อมูล
        $csv_file = '../locations.csv';
        
        // ตรวจสอบว่าไฟล์มีอยู่หรือไม่
        $file_exists = file_exists($csv_file);
        
        // เปิดไฟล์เพื่อเขียน (append)
        $fp = fopen($csv_file, 'a');
        
        if ($fp === false) {
            throw new Exception('Cannot open data file');
        }
        
        // เขียน header ถ้าไฟล์ใหม่
        if (!$file_exists) {
            fputcsv($fp, [
                'Timestamp', 
                'Latitude', 
                'Longitude', 
                'Accuracy', 
                'IP_Address',
                'User_Agent',
                'Device_Type',
                'Received_At'
            ]);
        }
        
        // เขียนข้อมูล
        fputcsv($fp, [
            $timestamp,
            $latitude,
            $longitude,
            $accuracy,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $userAgent,
            $device,
            date('Y-m-d H:i:s')
        ]);
        
        fclose($fp);
        
        // ตอบกลับสำเร็จ
        $response['success'] = true;
        $response['message'] = 'Location saved successfully';
        $response['data'] = [
            'lat' => $latitude,
            'lng' => $longitude,
            'accuracy' => $accuracy,
            'saved_at' => date('Y-m-d H:i:s')
        ];
        
        // บันทึก log
        error_log("GPS Data Saved: {$latitude}, {$longitude} from {$_SERVER['REMOTE_ADDR']}");
        
    } else {
        throw new Exception('Method not allowed. Use POST.');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
    error_log("GPS API Error: " . $e->getMessage());
}

// ส่ง response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>