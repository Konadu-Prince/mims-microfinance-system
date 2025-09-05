<?php

namespace MIMS\Models;

use MIMS\Core\Database\Repository\BaseRepository;

/**
 * Customer Model
 * Represents customer entity and business logic
 */
class Customer
{
    private ?int $id = null;
    private string $customerNumber;
    private int $customerType;
    private string $firstName;
    private ?string $middleName = null;
    private string $surname;
    private string $gender;
    private string $dateOfBirth;
    private string $nationality;
    private string $hometown;
    private string $email;
    private string $contact;
    private string $createdAt;
    private string $updatedAt;

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    public function getCustomerType(): int
    {
        return $this->customerType;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    public function getNationality(): string
    {
        return $this->nationality;
    }

    public function getHometown(): string
    {
        return $this->hometown;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getContact(): string
    {
        return $this->contact;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    // Setters
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setCustomerNumber(string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function setCustomerType(int $customerType): self
    {
        $this->customerType = $customerType;
        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;
        return $this;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function setDateOfBirth(string $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function setNationality(string $nationality): self
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function setHometown(string $hometown): self
    {
        $this->hometown = $hometown;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        $name = $this->firstName;
        if ($this->middleName) {
            $name .= ' ' . $this->middleName;
        }
        $name .= ' ' . $this->surname;
        return $name;
    }

    /**
     * Get age from date of birth
     */
    public function getAge(): int
    {
        $birthDate = new \DateTime($this->dateOfBirth);
        $today = new \DateTime();
        return $today->diff($birthDate)->y;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_number' => $this->customerNumber,
            'customer_type' => $this->customerType,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'surname' => $this->surname,
            'gender' => $this->gender,
            'date_of_birth' => $this->dateOfBirth,
            'nationality' => $this->nationality,
            'hometown' => $this->hometown,
            'email' => $this->email,
            'contact' => $this->contact,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        $customer = new self();
        
        if (isset($data['id'])) {
            $customer->setId($data['id']);
        }
        
        $customer->setCustomerNumber($data['customer_number'] ?? '');
        $customer->setCustomerType($data['customer_type'] ?? 0);
        $customer->setFirstName($data['first_name'] ?? '');
        $customer->setMiddleName($data['middle_name'] ?? null);
        $customer->setSurname($data['surname'] ?? '');
        $customer->setGender($data['gender'] ?? '');
        $customer->setDateOfBirth($data['date_of_birth'] ?? '');
        $customer->setNationality($data['nationality'] ?? '');
        $customer->setHometown($data['hometown'] ?? '');
        $customer->setEmail($data['email'] ?? '');
        $customer->setContact($data['contact'] ?? '');
        $customer->setCreatedAt($data['created_at'] ?? date('Y-m-d H:i:s'));
        $customer->setUpdatedAt($data['updated_at'] ?? date('Y-m-d H:i:s'));

        return $customer;
    }
}
