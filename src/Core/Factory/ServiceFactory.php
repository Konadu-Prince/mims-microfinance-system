<?php

namespace MIMS\Core\Factory;

use MIMS\Services\CustomerService;
use MIMS\Services\AccountService;
use MIMS\Services\TransactionService;
use MIMS\Services\LoanService;
use MIMS\Services\UserService;

/**
 * Service Factory
 * Implements Factory pattern for service creation
 */
class ServiceFactory
{
    private static array $instances = [];

    /**
     * Get Customer Service
     */
    public static function getCustomerService(): CustomerService
    {
        if (!isset(self::$instances['customer'])) {
            self::$instances['customer'] = new CustomerService();
        }
        return self::$instances['customer'];
    }

    /**
     * Get Account Service
     */
    public static function getAccountService(): AccountService
    {
        if (!isset(self::$instances['account'])) {
            self::$instances['account'] = new AccountService();
        }
        return self::$instances['account'];
    }

    /**
     * Get Transaction Service
     */
    public static function getTransactionService(): TransactionService
    {
        if (!isset(self::$instances['transaction'])) {
            self::$instances['transaction'] = new TransactionService();
        }
        return self::$instances['transaction'];
    }

    /**
     * Get Loan Service
     */
    public static function getLoanService(): LoanService
    {
        if (!isset(self::$instances['loan'])) {
            self::$instances['loan'] = new LoanService();
        }
        return self::$instances['loan'];
    }

    /**
     * Get User Service
     */
    public static function getUserService(): UserService
    {
        if (!isset(self::$instances['user'])) {
            self::$instances['user'] = new UserService();
        }
        return self::$instances['user'];
    }

    /**
     * Clear all instances (for testing)
     */
    public static function clearInstances(): void
    {
        self::$instances = [];
    }
}
