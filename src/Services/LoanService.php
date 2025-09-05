<?php

namespace MIMS\Services;

use MIMS\Repositories\LoanRepository;
use MIMS\Repositories\CustomerRepository;
use MIMS\Models\Loan;
use MIMS\Core\Validation\Validator;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;

/**
 * Loan Service
 * Implements business logic for loan operations
 */
class LoanService
{
    private LoanRepository $loanRepository;
    private CustomerRepository $customerRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->loanRepository = new LoanRepository();
        $this->customerRepository = new CustomerRepository();
        $this->validator = new Validator();
    }

    /**
     * Create loan application
     */
    public function createLoanApplication(array $loanData): Loan
    {
        // Validate input data
        $this->validateLoanData($loanData);

        // Check if customer exists
        $customer = $this->customerRepository->findByCustomerNumber($loanData['customer_number']);
        if (!$customer) {
            throw new BusinessLogicException('Customer not found');
        }

        // Check if customer has active loans
        $activeLoans = $this->loanRepository->getActiveLoansByCustomer($loanData['customer_number']);
        if (!empty($activeLoans)) {
            throw new BusinessLogicException('Customer has active loans');
        }

        // Calculate loan terms
        $loanData = $this->calculateLoanTerms($loanData);

        // Generate loan number
        $loanData['loan_number'] = $this->loanRepository->generateLoanNumber();

        // Create loan model
        $loan = Loan::fromArray($loanData);
        $loan->setStatus('pending');
        $loan->setCreatedAt(date('Y-m-d H:i:s'));
        $loan->setUpdatedAt(date('Y-m-d H:i:s'));

        // Save to database
        if (!$this->loanRepository->save($loan)) {
            throw new BusinessLogicException('Failed to create loan application');
        }

        return $loan;
    }

    /**
     * Approve loan
     */
    public function approveLoan(string $loanNumber, array $approvalData): Loan
    {
        $loan = $this->loanRepository->findByLoanNumber($loanNumber);
        if (!$loan) {
            throw new BusinessLogicException('Loan not found');
        }

        if ($loan->getStatus() !== 'pending') {
            throw new BusinessLogicException('Only pending loans can be approved');
        }

        // Update loan with approval data
        $loan->setStatus('approved');
        $loan->setApprovedAmount($approvalData['approved_amount'] ?? $loan->getRequestedAmount());
        $loan->setApprovedBy($approvalData['approved_by'] ?? 'system');
        $loan->setApprovedAt(date('Y-m-d H:i:s'));
        $loan->setUpdatedAt(date('Y-m-d H:i:s'));

        // Recalculate terms with approved amount
        $loanData = $loan->toArray();
        $loanData['requested_amount'] = $loan->getApprovedAmount();
        $loanData = $this->calculateLoanTerms($loanData);
        
        $loan->setMonthlyPayment($loanData['monthly_payment']);
        $loan->setTotalInterest($loanData['total_interest']);
        $loan->setTotalAmount($loanData['total_amount']);

        // Save to database
        if (!$this->loanRepository->save($loan)) {
            throw new BusinessLogicException('Failed to approve loan');
        }

        return $loan;
    }

    /**
     * Reject loan
     */
    public function rejectLoan(string $loanNumber, string $reason): Loan
    {
        $loan = $this->loanRepository->findByLoanNumber($loanNumber);
        if (!$loan) {
            throw new BusinessLogicException('Loan not found');
        }

        if ($loan->getStatus() !== 'pending') {
            throw new BusinessLogicException('Only pending loans can be rejected');
        }

        $loan->setStatus('rejected');
        $loan->setRejectionReason($reason);
        $loan->setRejectedAt(date('Y-m-d H:i:s'));
        $loan->setUpdatedAt(date('Y-m-d H:i:s'));

        if (!$this->loanRepository->save($loan)) {
            throw new BusinessLogicException('Failed to reject loan');
        }

        return $loan;
    }

    /**
     * Disburse loan
     */
    public function disburseLoan(string $loanNumber): Loan
    {
        $loan = $this->loanRepository->findByLoanNumber($loanNumber);
        if (!$loan) {
            throw new BusinessLogicException('Loan not found');
        }

        if ($loan->getStatus() !== 'approved') {
            throw new BusinessLogicException('Only approved loans can be disbursed');
        }

        $loan->setStatus('disbursed');
        $loan->setDisbursedAt(date('Y-m-d H:i:s'));
        $loan->setUpdatedAt(date('Y-m-d H:i:s'));

        if (!$this->loanRepository->save($loan)) {
            throw new BusinessLogicException('Failed to disburse loan');
        }

        return $loan;
    }

    /**
     * Get loan by loan number
     */
    public function getLoan(string $loanNumber): Loan
    {
        $loan = $this->loanRepository->findByLoanNumber($loanNumber);
        if (!$loan) {
            throw new BusinessLogicException('Loan not found');
        }

        return $loan;
    }

    /**
     * Get customer loans
     */
    public function getCustomerLoans(string $customerNumber): array
    {
        return $this->loanRepository->findByCustomerNumber($customerNumber);
    }

    /**
     * Get pending loans
     */
    public function getPendingLoans(): array
    {
        return $this->loanRepository->findByStatus('pending');
    }

    /**
     * Get loan statistics
     */
    public function getLoanStatistics(): array
    {
        return $this->loanRepository->getLoanStats();
    }

    /**
     * Calculate loan terms
     */
    private function calculateLoanTerms(array $loanData): array
    {
        $principal = (float) $loanData['requested_amount'];
        $interestRate = (float) $loanData['interest_rate'];
        $termMonths = (int) $loanData['term_months'];

        // Calculate monthly interest rate
        $monthlyRate = $interestRate / 100 / 12;

        // Calculate monthly payment using PMT formula
        if ($monthlyRate > 0) {
            $monthlyPayment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / 
                             (pow(1 + $monthlyRate, $termMonths) - 1);
        } else {
            $monthlyPayment = $principal / $termMonths;
        }

        $totalAmount = $monthlyPayment * $termMonths;
        $totalInterest = $totalAmount - $principal;

        $loanData['monthly_payment'] = round($monthlyPayment, 2);
        $loanData['total_interest'] = round($totalInterest, 2);
        $loanData['total_amount'] = round($totalAmount, 2);

        return $loanData;
    }

    /**
     * Validate loan data
     */
    private function validateLoanData(array $data): void
    {
        $rules = [
            'customer_number' => 'required|string',
            'requested_amount' => 'required|numeric|min:100|max:100000',
            'interest_rate' => 'required|numeric|min:1|max:50',
            'term_months' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string|max:255',
            'collateral' => 'string|max:255'
        ];

        $errors = $this->validator->validate($data, $rules);
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
