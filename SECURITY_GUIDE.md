# 🔒 MIMS Security Guide

## Overview

This document outlines the security measures, best practices, and implementation guidelines for the MIMS Microfinance Information Management System.

## 🔐 **Authentication & Authorization**

### **Password Security**
- Minimum 8 characters
- Must contain uppercase, lowercase, numbers, and special characters
- Password hashing using PHP's `password_hash()` with PASSWORD_DEFAULT
- Password history to prevent reuse of last 5 passwords

### **Session Management**
```php
// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
```

### **Two-Factor Authentication (2FA)**
- TOTP (Time-based One-Time Password) support
- SMS-based verification
- Backup codes for account recovery

## 🛡️ **Input Validation & Sanitization**

### **SQL Injection Prevention**
```php
// Use prepared statements
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
```

### **XSS Prevention**
```php
// Sanitize output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// For JSON output
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
```

### **CSRF Protection**
```php
// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

## 🔒 **Data Encryption**

### **Database Encryption**
- Encrypt sensitive fields at rest
- Use AES-256 encryption for PII data
- Implement field-level encryption for financial data

```php
// Encryption function
function encryptData($data, $key) {
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// Decryption function
function decryptData($encryptedData, $key) {
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}
```

### **File Upload Security**
```php
// Secure file upload
function validateFileUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large');
    }
    
    // Scan for malware
    if (function_exists('clamav_scan_file')) {
        $result = clamav_scan_file($file['tmp_name']);
        if ($result !== CL_CLEAN) {
            throw new Exception('File contains malware');
        }
    }
}
```

## 🌐 **Network Security**

### **HTTPS Configuration**
```apache
# Apache .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### **Rate Limiting**
```php
// Rate limiting implementation
class RateLimiter {
    private $redis;
    
    public function checkLimit($identifier, $limit = 100, $window = 3600) {
        $key = "rate_limit:" . $identifier;
        $current = $this->redis->incr($key);
        
        if ($current === 1) {
            $this->redis->expire($key, $window);
        }
        
        return $current <= $limit;
    }
}
```

## 📊 **Audit Logging**

### **Security Event Logging**
```php
// Audit log function
function logSecurityEvent($event, $details = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'user_id' => $_SESSION['user_id'] ?? null,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'details' => $details
    ];
    
    error_log(json_encode($logEntry), 3, 'logs/security.log');
}
```

### **Events to Log**
- Login attempts (successful and failed)
- Password changes
- Account modifications
- Data exports
- Administrative actions
- API access
- File uploads

## 🔍 **Security Monitoring**

### **Intrusion Detection**
```php
// Failed login monitoring
function monitorFailedLogins($username, $ip) {
    $key = "failed_logins:" . $ip . ":" . $username;
    $attempts = $this->redis->incr($key);
    
    if ($attempts >= 5) {
        // Block IP for 15 minutes
        $this->redis->setex("blocked_ip:" . $ip, 900, 1);
        logSecurityEvent('IP_BLOCKED', ['ip' => $ip, 'username' => $username]);
    }
}
```

### **Anomaly Detection**
- Unusual login patterns
- Large data exports
- Multiple failed transactions
- Suspicious API usage

## 🛠️ **Security Configuration**

### **PHP Security Settings**
```ini
; php.ini security settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

### **Database Security**
```sql
-- Create dedicated database user
CREATE USER 'mims_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON mims.* TO 'mims_app'@'localhost';
FLUSH PRIVILEGES;

-- Enable SSL for database connections
ALTER USER 'mims_app'@'localhost' REQUIRE SSL;
```

## 🔐 **API Security**

### **API Key Management**
```php
// API key validation
function validateApiKey($apiKey) {
    $stmt = $conn->prepare("SELECT * FROM api_keys WHERE key_hash = ? AND active = 1");
    $keyHash = hash('sha256', $apiKey);
    $stmt->bind_param("s", $keyHash);
    $stmt->execute();
    
    return $stmt->get_result()->num_rows > 0;
}
```

### **Request Signing**
```php
// HMAC request signing
function signRequest($data, $secret) {
    return hash_hmac('sha256', $data, $secret);
}

function validateRequest($data, $signature, $secret) {
    $expectedSignature = signRequest($data, $secret);
    return hash_equals($expectedSignature, $signature);
}
```

## 🚨 **Incident Response**

### **Security Incident Checklist**
1. **Immediate Response**
   - Isolate affected systems
   - Preserve evidence
   - Notify security team

2. **Investigation**
   - Analyze logs
   - Identify attack vector
   - Assess damage

3. **Recovery**
   - Patch vulnerabilities
   - Reset compromised credentials
   - Restore from clean backups

4. **Post-Incident**
   - Document lessons learned
   - Update security measures
   - Notify stakeholders

### **Emergency Contacts**
- **Security Team:** security@your-domain.com
- **System Administrator:** admin@your-domain.com
- **Legal Team:** legal@your-domain.com

## 📋 **Security Checklist**

### **Pre-Deployment**
- [ ] All passwords are strong and unique
- [ ] HTTPS is properly configured
- [ ] Security headers are implemented
- [ ] Input validation is in place
- [ ] SQL injection protection is active
- [ ] XSS protection is enabled
- [ ] CSRF protection is implemented
- [ ] File upload validation is working
- [ ] Rate limiting is configured
- [ ] Audit logging is enabled

### **Post-Deployment**
- [ ] Regular security updates
- [ ] Monitor security logs
- [ ] Conduct penetration testing
- [ ] Review access controls
- [ ] Backup security procedures
- [ ] Incident response plan tested

## 🔄 **Security Updates**

### **Regular Maintenance**
- **Daily:** Review security logs
- **Weekly:** Update dependencies
- **Monthly:** Security patch review
- **Quarterly:** Penetration testing
- **Annually:** Security audit

### **Dependency Management**
```bash
# Check for security vulnerabilities
composer audit

# Update dependencies
composer update

# Check PHP security advisories
php -m | grep -E "(openssl|curl|gd|mbstring)"
```

## 📚 **Resources**

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MySQL Security Guidelines](https://dev.mysql.com/doc/refman/8.0/en/security.html)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)

---

**Remember: Security is an ongoing process, not a one-time implementation.**
