<?php

namespace MIMS\Services;

use MIMS\Models\Customer;
use MIMS\Repositories\CustomerRepository;
use MIMS\Core\Validation\Validator;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;

/**
 * Customer Service
 * Implements business logic for customer operations
 */
class CustomerService
{
    private CustomerRepository $customerRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->customerRepository = new CustomerRepository();
        $this->validator = new Validator();
    }

    /**
     * Create new customer
     */
    public function createCustomer(array $customerData): Customer
    {
        // Validate input data
        $this->validateCustomerData($customerData);

        // Check for duplicate email
        if ($this->customerRepository->emailExists($customerData['email'])) {
            throw new BusinessLogicException('Email already exists');
        }

        // Check for duplicate contact
        if ($this->customerRepository->contactExists($customerData['contact'])) {
            throw new BusinessLogicException('Contact number already exists');
        }

        // Generate customer number if not provided
        if (empty($customerData['customer_number'])) {
            $customerData['customer_number'] = $this->customerRepository->generateCustomerNumber();
        }

        // Create customer model
        $customer = Customer::fromArray($customerData);
        $customer->setCreatedAt(date('Y-m-d H:i:s'));
        $customer->setUpdatedAt(date('Y-m-d H:i:s'));

        // Save to database
        if (!$this->customerRepository->save($customer)) {
            throw new BusinessLogicException('Failed to create customer');
        }

        return $customer;
    }

    /**
     * Update existing customer
     */
    public function updateCustomer(string $customerNumber, array $customerData): Customer
    {
        // Find existing customer
        $customer = $this->customerRepository->findByCustomerNumber($customerNumber);
        if (!$customer) {
            throw new BusinessLogicException('Customer not found');
        }

        // Validate input data
        $this->validateCustomerData($customerData, $customerNumber);

        // Check for duplicate email (excluding current customer)
        if (isset($customerData['email']) && 
            $this->customerRepository->emailExists($customerData['email'], $customerNumber)) {
            throw new BusinessLogicException('Email already exists');
        }

        // Check for duplicate contact (excluding current customer)
        if (isset($customerData['contact']) && 
            $this->customerRepository->contactExists($customerData['contact'], $customerNumber)) {
            throw new BusinessLogicException('Contact number already exists');
        }

        // Update customer data
        foreach ($customerData as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($customer, $method)) {
                $customer->$method($value);
            }
        }

        $customer->setUpdatedAt(date('Y-m-d H:i:s'));

        // Save to database
        if (!$this->customerRepository->save($customer)) {
            throw new BusinessLogicException('Failed to update customer');
        }

        return $customer;
    }

    /**
     * Get customer by customer number
     */
    public function getCustomer(string $customerNumber): Customer
    {
        $customer = $this->customerRepository->findByCustomerNumber($customerNumber);
        if (!$customer) {
            throw new BusinessLogicException('Customer not found');
        }

        return $customer;
    }

    /**
     * Get all customers with pagination
     */
    public function getAllCustomers(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $customers = $this->customerRepository->findAll([], ['created_at DESC'], $limit, $offset);
        
        return array_map(fn($data) => Customer::fromArray($data), $customers);
    }

    /**
     * Search customers
     */
    public function searchCustomers(string $searchTerm): array
    {
        if (empty(trim($searchTerm))) {
            return [];
        }

        return $this->customerRepository->searchByName($searchTerm);
    }

    /**
     * Get customers by type
     */
    public function getCustomersByType(int $customerType): array
    {
        return $this->customerRepository->findByCustomerType($customerType);
    }

    /**
     * Get customer statistics
     */
    public function getCustomerStatistics(): array
    {
        return $this->customerRepository->getCustomerStats();
    }

    /**
     * Get recent customers
     */
    public function getRecentCustomers(int $limit = 10): array
    {
        return $this->customerRepository->getRecentCustomers($limit);
    }

    /**
     * Delete customer
     */
    public function deleteCustomer(string $customerNumber): bool
    {
        $customer = $this->customerRepository->findByCustomerNumber($customerNumber);
        if (!$customer) {
            throw new BusinessLogicException('Customer not found');
        }

        // Check if customer has accounts or transactions
        // This would require additional repository methods
        // For now, we'll just delete the customer
        
        return $this->customerRepository->deleteByCustomerNumber($customerNumber);
    }

    /**
     * Validate customer data
     */
    private function validateCustomerData(array $data, ?string $excludeCustomerNumber = null): void
    {
        $rules = [
            'first_name' => 'required|string|max:50',
            'surname' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'contact' => 'required|string|max:20',
            'gender' => 'required|in:M,F,O',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string|max:50',
            'hometown' => 'required|string|max:100',
            'customer_type' => 'required|integer|min:1'
        ];

        $errors = $this->validator->validate($data, $rules);
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }

        // Additional business logic validation
        $this->validateBusinessRules($data, $excludeCustomerNumber);
    }

    /**
     * Validate business rules
     */
    private function validateBusinessRules(array $data, ?string $excludeCustomerNumber = null): void
    {
        // Validate age (must be at least 18)
        if (isset($data['date_of_birth'])) {
            $birthDate = new \DateTime($data['date_of_birth']);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
            
            if ($age < 18) {
                throw new ValidationException('Customer must be at least 18 years old');
            }
        }

        // Validate email format
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }

        // Validate contact number format (basic validation)
        if (isset($data['contact']) && !preg_match('/^[0-9+\-\s()]+$/', $data['contact'])) {
            throw new ValidationException('Invalid contact number format');
        }
    }
}
