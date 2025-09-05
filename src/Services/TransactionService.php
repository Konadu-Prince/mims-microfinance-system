<?php

namespace MIMS\Services;

use MIMS\Repositories\TransactionRepository;
use MIMS\Repositories\AccountRepository;
use MIMS\Models\Transaction;
use MIMS\Core\Validation\Validator;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;

/**
 * Transaction Service
 * Implements business logic for transaction operations
 */
class TransactionService
{
    private TransactionRepository $transactionRepository;
    private AccountRepository $accountRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->transactionRepository = new TransactionRepository();
        $this->accountRepository = new AccountRepository();
        $this->validator = new Validator();
    }

    /**
     * Process transaction
     */
    public function processTransaction(array $transactionData): Transaction
    {
        // Validate input data
        $this->validateTransactionData($transactionData);

        // Get account
        $account = $this->accountRepository->findByAccountNumber($transactionData['account_number']);
        if (!$account) {
            throw new BusinessLogicException('Account not found');
        }

        // Check account status
        if ($account->getStatus() !== 'active') {
            throw new BusinessLogicException('Account is not active');
        }

        // Process based on transaction type
        $transactionType = $transactionData['transaction_type'];
        $amount = (float) $transactionData['amount'];

        if ($transactionType === 'withdrawal') {
            $this->validateWithdrawal($account, $amount);
        }

        // Generate transaction number
        $transactionData['transaction_number'] = $this->transactionRepository->generateTransactionNumber();

        // Create transaction model
        $transaction = Transaction::fromArray($transactionData);
        $transaction->setCreatedAt(date('Y-m-d H:i:s'));

        // Start database transaction
        $this->transactionRepository->beginTransaction();

        try {
            // Save transaction
            if (!$this->transactionRepository->save($transaction)) {
                throw new BusinessLogicException('Failed to save transaction');
            }

            // Update account balance
            $this->updateAccountBalance($account, $amount, $transactionType);

            // Commit transaction
            $this->transactionRepository->commit();

            return $transaction;

        } catch (Exception $e) {
            // Rollback on error
            $this->transactionRepository->rollback();
            throw $e;
        }
    }

    /**
     * Get transaction by transaction number
     */
    public function getTransaction(string $transactionNumber): Transaction
    {
        $transaction = $this->transactionRepository->findByTransactionNumber($transactionNumber);
        if (!$transaction) {
            throw new BusinessLogicException('Transaction not found');
        }

        return $transaction;
    }

    /**
     * Get account transactions
     */
    public function getAccountTransactions(string $accountNumber, int $limit = 50): array
    {
        return $this->transactionRepository->findByAccountNumber($accountNumber, $limit);
    }

    /**
     * Get customer transactions
     */
    public function getCustomerTransactions(int $customerId, int $limit = 50): array
    {
        return $this->transactionRepository->findByCustomerId($customerId, $limit);
    }

    /**
     * Reverse transaction
     */
    public function reverseTransaction(string $transactionNumber): bool
    {
        $transaction = $this->getTransaction($transactionNumber);
        
        if ($transaction->getStatus() !== 'completed') {
            throw new BusinessLogicException('Only completed transactions can be reversed');
        }

        // Get account
        $account = $this->accountRepository->findByAccountNumber($transaction->getAccountNumber());
        if (!$account) {
            throw new BusinessLogicException('Account not found');
        }

        // Start database transaction
        $this->transactionRepository->beginTransaction();

        try {
            // Create reversal transaction
            $reversalData = [
                'account_number' => $transaction->getAccountNumber(),
                'customer_id' => $transaction->getCustomerId(),
                'amount' => $transaction->getAmount(),
                'transaction_type' => $transaction->getTransactionType() === 'deposit' ? 'withdrawal' : 'deposit',
                'description' => 'Reversal of transaction ' . $transactionNumber,
                'reference_transaction' => $transactionNumber
            ];

            $reversalTransaction = Transaction::fromArray($reversalData);
            $reversalTransaction->setCreatedAt(date('Y-m-d H:i:s'));

            if (!$this->transactionRepository->save($reversalTransaction)) {
                throw new BusinessLogicException('Failed to create reversal transaction');
            }

            // Update account balance
            $reversalType = $transaction->getTransactionType() === 'deposit' ? 'withdrawal' : 'deposit';
            $this->updateAccountBalance($account, $transaction->getAmount(), $reversalType);

            // Mark original transaction as reversed
            $transaction->setStatus('reversed');
            $transaction->setUpdatedAt(date('Y-m-d H:i:s'));
            $this->transactionRepository->save($transaction);

            // Commit transaction
            $this->transactionRepository->commit();

            return true;

        } catch (Exception $e) {
            // Rollback on error
            $this->transactionRepository->rollback();
            throw $e;
        }
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStatistics(): array
    {
        return $this->transactionRepository->getTransactionStats();
    }

    /**
     * Validate transaction data
     */
    private function validateTransactionData(array $data): void
    {
        $rules = [
            'account_number' => 'required|string',
            'customer_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:deposit,withdrawal,transfer',
            'description' => 'required|string|max:255'
        ];

        $errors = $this->validator->validate($data, $rules);
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    /**
     * Validate withdrawal
     */
    private function validateWithdrawal(Account $account, float $amount): void
    {
        if ($account->getBalance() < $amount) {
            throw new BusinessLogicException('Insufficient funds');
        }

        // Check daily withdrawal limit
        $dailyWithdrawals = $this->transactionRepository->getDailyWithdrawals($account->getAccountNumber());
        $totalDailyWithdrawals = array_sum(array_column($dailyWithdrawals, 'amount'));
        
        $dailyLimit = 10000; // Configurable limit
        if ($totalDailyWithdrawals + $amount > $dailyLimit) {
            throw new BusinessLogicException('Daily withdrawal limit exceeded');
        }
    }

    /**
     * Update account balance
     */
    private function updateAccountBalance(Account $account, float $amount, string $transactionType): void
    {
        if ($transactionType === 'deposit') {
            $newBalance = $account->getBalance() + $amount;
        } elseif ($transactionType === 'withdrawal') {
            $newBalance = $account->getBalance() - $amount;
        } else {
            throw new ValidationException('Invalid transaction type');
        }

        $account->setBalance($newBalance);
        $account->setUpdatedAt(date('Y-m-d H:i:s'));

        if (!$this->accountRepository->save($account)) {
            throw new BusinessLogicException('Failed to update account balance');
        }
    }
}
