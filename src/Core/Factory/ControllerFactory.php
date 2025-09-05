<?php

namespace MIMS\Core\Factory;

use MIMS\Controllers\CustomerController;
use MIMS\Controllers\AccountController;
use MIMS\Controllers\TransactionController;
use MIMS\Controllers\LoanController;
use MIMS\Controllers\UserController;

/**
 * Controller Factory
 * Implements Factory pattern for controller creation
 */
class ControllerFactory
{
    private static array $instances = [];

    /**
     * Get Customer Controller
     */
    public static function getCustomerController(): CustomerController
    {
        if (!isset(self::$instances['customer'])) {
            self::$instances['customer'] = new CustomerController();
        }
        return self::$instances['customer'];
    }

    /**
     * Get Account Controller
     */
    public static function getAccountController(): AccountController
    {
        if (!isset(self::$instances['account'])) {
            self::$instances['account'] = new AccountController();
        }
        return self::$instances['account'];
    }

    /**
     * Get Transaction Controller
     */
    public static function getTransactionController(): TransactionController
    {
        if (!isset(self::$instances['transaction'])) {
            self::$instances['transaction'] = new TransactionController();
        }
        return self::$instances['transaction'];
    }

    /**
     * Get Loan Controller
     */
    public static function getLoanController(): LoanController
    {
        if (!isset(self::$instances['loan'])) {
            self::$instances['loan'] = new LoanController();
        }
        return self::$instances['loan'];
    }

    /**
     * Get User Controller
     */
    public static function getUserController(): UserController
    {
        if (!isset(self::$instances['user'])) {
            self::$instances['user'] = new UserController();
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
