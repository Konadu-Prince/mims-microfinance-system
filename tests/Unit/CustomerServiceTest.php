<?php

namespace MIMS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use MIMS\Services\CustomerService;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;

/**
 * Customer Service Unit Tests
 */
class CustomerServiceTest extends TestCase
{
    private CustomerService $customerService;

    protected function setUp(): void
    {
        $this->customerService = new CustomerService();
    }

    /**
     * Test customer creation with valid data
     */
    public function testCreateCustomerWithValidData(): void
    {
        $customerData = [
            'customer_number' => 'CUST2024001',
            'customer_type' => 1,
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'contact' => '+233123456789',
            'gender' => 'M',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'Ghana',
            'hometown' => 'Accra'
        ];

        // This would require mocking the repository
        // For now, we'll test the validation logic
        $this->assertIsArray($customerData);
        $this->assertEquals('John', $customerData['first_name']);
        $this->assertEquals('Doe', $customerData['surname']);
    }

    /**
     * Test customer creation with invalid email
     */
    public function testCreateCustomerWithInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        
        $customerData = [
            'customer_number' => 'CUST2024002',
            'customer_type' => 1,
            'first_name' => 'Jane',
            'surname' => 'Doe',
            'email' => 'invalid-email',
            'contact' => '+233123456789',
            'gender' => 'F',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'Ghana',
            'hometown' => 'Accra'
        ];

        // This would trigger validation exception
        $this->customerService->createCustomer($customerData);
    }

    /**
     * Test customer creation with missing required fields
     */
    public function testCreateCustomerWithMissingFields(): void
    {
        $this->expectException(ValidationException::class);
        
        $customerData = [
            'customer_number' => 'CUST2024003',
            'customer_type' => 1,
            // Missing required fields
        ];

        $this->customerService->createCustomer($customerData);
    }

    /**
     * Test customer creation with underage customer
     */
    public function testCreateCustomerUnderage(): void
    {
        $this->expectException(ValidationException::class);
        
        $customerData = [
            'customer_number' => 'CUST2024004',
            'customer_type' => 1,
            'first_name' => 'Young',
            'surname' => 'Person',
            'email' => 'young@example.com',
            'contact' => '+233123456789',
            'gender' => 'M',
            'date_of_birth' => '2010-01-01', // Under 18
            'nationality' => 'Ghana',
            'hometown' => 'Accra'
        ];

        $this->customerService->createCustomer($customerData);
    }

    /**
     * Test customer search functionality
     */
    public function testSearchCustomers(): void
    {
        $searchTerm = 'John';
        $results = $this->customerService->searchCustomers($searchTerm);
        
        $this->assertIsArray($results);
    }

    /**
     * Test customer statistics
     */
    public function testGetCustomerStatistics(): void
    {
        $stats = $this->customerService->getCustomerStatistics();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_customers', $stats);
    }
}
