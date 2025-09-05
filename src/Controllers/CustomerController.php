<?php

namespace MIMS\Controllers;

use MIMS\Services\CustomerService;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;
use MIMS\Core\Validation\Validator;

/**
 * Customer Controller
 * Handles HTTP requests for customer operations
 */
class CustomerController
{
    private CustomerService $customerService;
    private Validator $validator;

    public function __construct()
    {
        $this->customerService = new CustomerService();
        $this->validator = new Validator();
    }

    /**
     * Create new customer
     */
    public function create(): void
    {
        try {
            // Sanitize input data
            $data = $this->validator->sanitize($_POST);
            
            // Create customer
            $customer = $this->customerService->createCustomer($data);
            
            // Set success message
            $_SESSION['success_message'] = 'Customer created successfully';
            $_SESSION['customer_number'] = $customer->getCustomerNumber();
            
            // Redirect to customer list
            header('Location: managecustomer.php');
            exit;
            
        } catch (ValidationException $e) {
            $_SESSION['error_message'] = 'Validation failed: ' . $e->getMessage();
            $_SESSION['validation_errors'] = $e->getErrors();
            $_SESSION['form_data'] = $_POST;
            header('Location: addcustomer.php');
            exit;
            
        } catch (BusinessLogicException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: addcustomer.php');
            exit;
            
        } catch (Exception $e) {
            error_log("Customer creation error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while creating the customer';
            header('Location: addcustomer.php');
            exit;
        }
    }

    /**
     * Update existing customer
     */
    public function update(): void
    {
        try {
            $customerNumber = $_POST['customer_number'] ?? '';
            
            if (empty($customerNumber)) {
                throw new ValidationException('Customer number is required');
            }
            
            // Sanitize input data
            $data = $this->validator->sanitize($_POST);
            
            // Update customer
            $customer = $this->customerService->updateCustomer($customerNumber, $data);
            
            // Set success message
            $_SESSION['success_message'] = 'Customer updated successfully';
            
            // Redirect to customer list
            header('Location: managecustomer.php');
            exit;
            
        } catch (ValidationException $e) {
            $_SESSION['error_message'] = 'Validation failed: ' . $e->getMessage();
            $_SESSION['validation_errors'] = $e->getErrors();
            $_SESSION['form_data'] = $_POST;
            header('Location: updatecustomer.php?customer_number=' . ($_POST['customer_number'] ?? ''));
            exit;
            
        } catch (BusinessLogicException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: updatecustomer.php?customer_number=' . ($_POST['customer_number'] ?? ''));
            exit;
            
        } catch (Exception $e) {
            error_log("Customer update error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while updating the customer';
            header('Location: managecustomer.php');
            exit;
        }
    }

    /**
     * Delete customer
     */
    public function delete(): void
    {
        try {
            $customerNumber = $_GET['customer_number'] ?? '';
            
            if (empty($customerNumber)) {
                throw new ValidationException('Customer number is required');
            }
            
            // Delete customer
            $this->customerService->deleteCustomer($customerNumber);
            
            // Set success message
            $_SESSION['success_message'] = 'Customer deleted successfully';
            
            // Redirect to customer list
            header('Location: managecustomer.php');
            exit;
            
        } catch (ValidationException $e) {
            $_SESSION['error_message'] = 'Validation failed: ' . $e->getMessage();
            header('Location: managecustomer.php');
            exit;
            
        } catch (BusinessLogicException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: managecustomer.php');
            exit;
            
        } catch (Exception $e) {
            error_log("Customer deletion error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while deleting the customer';
            header('Location: managecustomer.php');
            exit;
        }
    }

    /**
     * Get customer details
     */
    public function show(): ?array
    {
        try {
            $customerNumber = $_GET['customer_number'] ?? '';
            
            if (empty($customerNumber)) {
                throw new ValidationException('Customer number is required');
            }
            
            $customer = $this->customerService->getCustomer($customerNumber);
            return $customer->toArray();
            
        } catch (Exception $e) {
            error_log("Customer retrieval error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * List all customers
     */
    public function index(): array
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $limit = 20;
            
            return $this->customerService->getAllCustomers($page, $limit);
            
        } catch (Exception $e) {
            error_log("Customer listing error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search customers
     */
    public function search(): array
    {
        try {
            $searchTerm = $_GET['search'] ?? '';
            
            if (empty(trim($searchTerm))) {
                return [];
            }
            
            return $this->customerService->searchCustomers($searchTerm);
            
        } catch (Exception $e) {
            error_log("Customer search error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get customer statistics
     */
    public function statistics(): array
    {
        try {
            return $this->customerService->getCustomerStatistics();
            
        } catch (Exception $e) {
            error_log("Customer statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle AJAX requests
     */
    public function ajax(): void
    {
        header('Content-Type: application/json');
        
        try {
            $action = $_GET['action'] ?? '';
            
            switch ($action) {
                case 'search':
                    $results = $this->search();
                    echo json_encode(['success' => true, 'data' => $results]);
                    break;
                    
                case 'statistics':
                    $stats = $this->statistics();
                    echo json_encode(['success' => true, 'data' => $stats]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
