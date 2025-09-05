<?php

namespace MIMS\Services;

use MIMS\Repositories\AccountRepository;
use MIMS\Models\Account;
use MIMS\Core\Validation\Validator;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;

/**
 * Account Service
 * Implements business logic for account operations
 */
class AccountService
{
    private AccountRepository $accountRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->accountRepository = new AccountRepository();
        $this->validator = new Validator();
    }

    /**
     * Create new account
     */
    public function createAccount(array $accountData): Account
    {
        // Validate input data
        $this->validateAccountData($accountData);

        // Check if customer exists
        if (!$this->accountRepository->customerExists($accountData['customer_id'])) {
            throw new BusinessLogicException('Customer not found');
        }

        // Check if customer already has this account type
        if ($this->accountRepository->customerHasAccountType($accountData['customer_id'], $accountData['account_type'])) {
            throw new BusinessLogicException('Customer already has this account type');
        }

        // Generate account number if not provided
        if (empty($accountData['account_number'])) {
            $accountData['account_number'] = $this->accountRepository->generateAccountNumber();
        }

        // Create account model
        $account = Account::fromArray($accountData);
        $account->setCreatedAt(date('Y-m-d H:i:s'));
        $account->setUpdatedAt(date('Y-m-d H:i:s'));

        // Save to database
        if (!$this->accountRepository->save($account)) {
            throw new BusinessLogicException('Failed to create account');
        }

        return $account;
    }

    /**
     * Get account by account number
     */
    public function getAccount(string $accountNumber): Account
    {
        $account = $this->accountRepository->findByAccountNumber($accountNumber);
        if (!$account) {
            throw new BusinessLogicException('Account not found');
        }

        return $account;
    }

    /**
     * Get accounts by customer
     */
    public function getCustomerAccounts(int $customerId): array
    {
        return $this->accountRepository->findByCustomerId($customerId);
    }

    /**
     * Update account balance
     */
    public function updateBalance(string $accountNumber, float $amount, string $transactionType): bool
    {
        $account = $this->getAccount($accountNumber);
        
        if ($transactionType === 'deposit') {
            $newBalance = $account->getBalance() + $amount;
        } elseif ($transactionType === 'withdrawal') {
            if ($account->getBalance() < $amount) {
                throw new BusinessLogicException('Insufficient funds');
            }
            $newBalance = $account->getBalance() - $amount;
        } else {
            throw new ValidationException('Invalid transaction type');
        }

        $account->setBalance($newBalance);
        $account->setUpdatedAt(date('Y-m-d H:i:s'));

        return $this->accountRepository->save($account);
    }

    /**
     * Close account
     */
    public function closeAccount(string $accountNumber): bool
    {
        $account = $this->getAccount($accountNumber);
        
        if ($account->getBalance() > 0) {
            throw new BusinessLogicException('Account must have zero balance to be closed');
        }

        $account->setStatus('closed');
        $account->setUpdatedAt(date('Y-m-d H:i:s'));

        return $this->accountRepository->save($account);
    }

    /**
     * Get account statistics
     */
    public function getAccountStatistics(): array
    {
        return $this->accountRepository->getAccountStats();
    }

    /**
     * Validate account data
     */
    private function validateAccountData(array $data): void
    {
        $rules = [
            'customer_id' => 'required|integer|min:1',
            'account_type' => 'required|integer|min:1',
            'initial_balance' => 'required|numeric|min:0'
        ];

        $errors = $this->validator->validate($data, $rules);
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
