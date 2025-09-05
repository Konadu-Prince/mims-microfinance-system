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

// Start session
session_start();

// Import required classes
use MIMS\Controllers\CustomerController;
use MIMS\Core\Factory\ControllerFactory;

try {
    // Get the action from POST or GET
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // Get controller instance
    $controller = ControllerFactory::getCustomerController();
    
    // Route the request based on action
    switch ($action) {
        case 'create':
            $controller->create();
            break;
            
        case 'update':
            $controller->update();
            break;
            
        case 'delete':
            $controller->delete();
            break;
            
        case 'ajax':
            $controller->ajax();
            break;
            
        default:
            // Default to create for backward compatibility
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->create();
            } else {
                header('Location: ../addcustomer.php');
                exit;
            }
    }
    
} catch (Exception $e) {
    error_log("Customer handler error: " . $e->getMessage());
    $_SESSION['error_message'] = 'An unexpected error occurred. Please try again.';
    header('Location: ../addcustomer.php');
    exit;
}
