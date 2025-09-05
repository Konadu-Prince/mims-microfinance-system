<?php

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment configuration
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use MIMS\Core\Router\ApiRouter;
use MIMS\Core\Exceptions\NotFoundException;
use MIMS\Core\Exceptions\MethodNotAllowedException;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;

try {
    // Create router and setup routes
    $router = new ApiRouter();
    $router->setupDefaultRoutes();
    
    // Dispatch request
    $router->dispatch();
    
} catch (NotFoundException $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'NOT_FOUND',
            'message' => $e->getMessage()
        ]
    ]);
    
} catch (MethodNotAllowedException $e) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => $e->getMessage()
        ]
    ]);
    
} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'VALIDATION_ERROR',
            'message' => $e->getMessage(),
            'details' => $e->getErrors()
        ]
    ]);
    
} catch (BusinessLogicException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'BUSINESS_LOGIC_ERROR',
            'message' => $e->getMessage()
        ]
    ]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INTERNAL_SERVER_ERROR',
            'message' => 'An unexpected error occurred'
        ]
    ]);
}
