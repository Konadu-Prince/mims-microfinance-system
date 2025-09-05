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

// Start secure session
session_start();

// Configure secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

use MIMS\Core\Database\DatabaseConnection;
use MIMS\Core\Security\PasswordHasher;
use MIMS\Core\Security\RateLimiter;
use MIMS\Core\Mail\MailService;

try {
    // Rate limiting
    $rateLimiter = new RateLimiter();
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if (!$rateLimiter->checkLimit($clientIp, 5, 900)) { // 5 attempts per 15 minutes
        $_SESSION['error_message'] = 'Too many login attempts. Please try again later.';
        header('Location: ../login.php');
        exit;
    }
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid request. Please try again.';
        header('Location: ../login.php');
        exit;
    }
    
    // Get and sanitize input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = 'Username and password are required.';
        header('Location: ../login.php');
        exit;
    }
    
    // Get database connection
    $db = DatabaseConnection::getInstance();
    $conn = $db->getConnection();
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND active = 1");
    $stmt->bindParam(':email', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    if ($user && PasswordHasher::verify($password, $user['password'])) {
        // Login successful
        
        // Reset rate limiting for this IP
        $rateLimiter->resetLimit($clientIp);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true); // 30 days
            
            // Store token in database
            $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
            $expiresAt = date('Y-m-d H:i:s', time() + (86400 * 30));
            $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expiresAt, PDO::PARAM_STR);
            $stmt->execute();
        }
        
        // Generate verification code for 2FA (if enabled)
        if ($user['two_factor_enabled']) {
            $verificationCode = rand(100000, 999999);
            $_SESSION['verification_code'] = $verificationCode;
            $_SESSION['verification_time'] = time();
            
            // Send verification code via email
            $mailService = new MailService();
            $mailService->sendVerificationCode($user['email'], $verificationCode);
            
            header('Location: ../verification.php');
            exit;
        }
        
        // Log successful login
        error_log("Successful login for user: {$username} from IP: {$clientIp}");
        
        // Redirect to dashboard
        header('Location: ../dashboard.php');
        exit;
        
    } else {
        // Login failed
        $rateLimiter->recordFailedAttempt($clientIp);
        
        // Log failed login attempt
        error_log("Failed login attempt for user: {$username} from IP: {$clientIp}");
        
        $_SESSION['error_message'] = 'Invalid username or password.';
        header('Location: ../login.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred during login. Please try again.';
    header('Location: ../login.php');
    exit;
}
