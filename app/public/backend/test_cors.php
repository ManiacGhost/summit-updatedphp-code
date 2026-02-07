<?php
// Simple test script to verify CORS headers are working
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'CORS test successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'unknown'
]);
?>
