<?php

namespace MIMS\Repositories;

use MIMS\Core\Database\Repository\BaseRepository;
use MIMS\Models\Customer;

/**
 * Customer Repository
 * Handles all customer-related database operations
 */
class CustomerRepository extends BaseRepository
{
    protected string $table = 'customers';
    protected string $primaryKey = 'customer_number';

    /**
     * Find customer by customer number
     */
    public function findByCustomerNumber(string $customerNumber): ?Customer
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_number = :customer_number";
        $result = $this->executeQuerySingle($sql, [':customer_number' => $customerNumber]);
        
        return $result ? Customer::fromArray($result) : null;
    }

    /**
     * Find customers by type
     */
    public function findByCustomerType(int $customerType): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_type = :customer_type ORDER BY created_at DESC";
        $results = $this->executeQuery($sql, [':customer_type' => $customerType]);
        
        return array_map(fn($data) => Customer::fromArray($data), $results);
    }

    /**
     * Search customers by name
     */
    public function searchByName(string $searchTerm): array
    {
        $searchTerm = "%{$searchTerm}%";
        $sql = "SELECT * FROM {$this->table} 
                WHERE first_name LIKE :search 
                   OR middle_name LIKE :search 
                   OR surname LIKE :search 
                ORDER BY first_name, surname";
        
        $results = $this->executeQuery($sql, [':search' => $searchTerm]);
        
        return array_map(fn($data) => Customer::fromArray($data), $results);
    }

    /**
     * Find customers by email
     */
    public function findByEmail(string $email): ?Customer
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $result = $this->executeQuerySingle($sql, [':email' => $email]);
        
        return $result ? Customer::fromArray($result) : null;
    }

    /**
     * Find customers by contact
     */
    public function findByContact(string $contact): ?Customer
    {
        $sql = "SELECT * FROM {$this->table} WHERE contact = :contact";
        $result = $this->executeQuerySingle($sql, [':contact' => $contact]);
        
        return $result ? Customer::fromArray($result) : null;
    }

    /**
     * Get customer statistics
     */
    public function getCustomerStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_customers,
                    COUNT(CASE WHEN gender = 'M' THEN 1 END) as male_customers,
                    COUNT(CASE WHEN gender = 'F' THEN 1 END) as female_customers,
                    COUNT(CASE WHEN customer_type = 1 THEN 1 END) as individual_customers,
                    COUNT(CASE WHEN customer_type = 2 THEN 1 END) as group_customers,
                    COUNT(CASE WHEN customer_type = 3 THEN 1 END) as corporate_customers
                FROM {$this->table}";
        
        return $this->executeQuerySingle($sql);
    }

    /**
     * Get recent customers
     */
    public function getRecentCustomers(int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
        $results = $this->executeQuery($sql, [':limit' => $limit]);
        
        return array_map(fn($data) => Customer::fromArray($data), $results);
    }

    /**
     * Check if customer number exists
     */
    public function customerNumberExists(string $customerNumber): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE customer_number = :customer_number";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':customer_number', $customerNumber);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?string $excludeCustomerNumber = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeCustomerNumber) {
            $sql .= " AND customer_number != :exclude_customer_number";
            $params[':exclude_customer_number'] = $excludeCustomerNumber;
        }
        
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if contact exists
     */
    public function contactExists(string $contact, ?string $excludeCustomerNumber = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE contact = :contact";
        $params = [':contact' => $contact];
        
        if ($excludeCustomerNumber) {
            $sql .= " AND customer_number != :exclude_customer_number";
            $params[':exclude_customer_number'] = $excludeCustomerNumber;
        }
        
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Generate unique customer number
     */
    public function generateCustomerNumber(): string
    {
        do {
            $customerNumber = 'CUST' . date('Y') . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->customerNumberExists($customerNumber));
        
        return $customerNumber;
    }

    /**
     * Save customer (insert or update)
     */
    public function save(Customer $customer): bool
    {
        $data = $customer->toArray();
        unset($data['id']); // Remove id from data array
        
        if ($customer->getId()) {
            // Update existing customer
            return $this->update($customer->getId(), $data);
        } else {
            // Insert new customer
            $id = $this->insert($data);
            if ($id) {
                $customer->setId($id);
                return true;
            }
            return false;
        }
    }

    /**
     * Delete customer by customer number
     */
    public function deleteByCustomerNumber(string $customerNumber): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE customer_number = :customer_number";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':customer_number', $customerNumber);
        
        return $stmt->execute();
    }
}
