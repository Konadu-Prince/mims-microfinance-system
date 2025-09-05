<?php

namespace MIMS\Core\Security;

use MIMS\Core\Database\DatabaseConnection;

/**
 * Rate Limiter
 * Implements rate limiting for security
 */
class RateLimiter
{
    private array $limits = [];

    /**
     * Check if request is within rate limit
     */
    public function checkLimit(string $identifier, int $limit = 100, int $window = 3600): bool
    {
        $key = "rate_limit:{$identifier}";
        $current = $this->getCurrentCount($key, $window);
        
        if ($current >= $limit) {
            return false;
        }
        
        $this->incrementCount($key, $window);
        return true;
    }

    /**
     * Record failed attempt
     */
    public function recordFailedAttempt(string $identifier): void
    {
        $key = "failed_attempts:{$identifier}";
        $this->incrementCount($key, 900); // 15 minutes
    }

    /**
     * Reset rate limit for identifier
     */
    public function resetLimit(string $identifier): void
    {
        $key = "rate_limit:{$identifier}";
        $this->clearCount($key);
    }

    /**
     * Get current count for key
     */
    private function getCurrentCount(string $key, int $window): int
    {
        if (isset($this->limits[$key])) {
            $data = $this->limits[$key];
            if (time() - $data['timestamp'] < $window) {
                return $data['count'];
            }
        }
        return 0;
    }

    /**
     * Increment count for key
     */
    private function incrementCount(string $key, int $window): void
    {
        if (isset($this->limits[$key])) {
            $data = $this->limits[$key];
            if (time() - $data['timestamp'] < $window) {
                $this->limits[$key]['count']++;
            } else {
                $this->limits[$key] = ['count' => 1, 'timestamp' => time()];
            }
        } else {
            $this->limits[$key] = ['count' => 1, 'timestamp' => time()];
        }
    }

    /**
     * Clear count for key
     */
    private function clearCount(string $key): void
    {
        unset($this->limits[$key]);
    }
}
