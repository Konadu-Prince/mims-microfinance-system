<?php

namespace MIMS\Services;

use MIMS\Repositories\UserRepository;
use MIMS\Models\User;
use MIMS\Core\Validation\Validator;
use MIMS\Core\Security\PasswordHasher;
use MIMS\Core\Exceptions\ValidationException;
use MIMS\Core\Exceptions\BusinessLogicException;

/**
 * User Service
 * Implements business logic for user operations
 */
class UserService
{
    private UserRepository $userRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->validator = new Validator();
    }

    /**
     * Create new user
     */
    public function createUser(array $userData): User
    {
        // Validate input data
        $this->validateUserData($userData);

        // Check if email already exists
        if ($this->userRepository->emailExists($userData['email'])) {
            throw new BusinessLogicException('Email already exists');
        }

        // Check if username already exists
        if ($this->userRepository->usernameExists($userData['username'])) {
            throw new BusinessLogicException('Username already exists');
        }

        // Hash password
        $userData['password'] = PasswordHasher::hash($userData['password']);

        // Create user model
        $user = User::fromArray($userData);
        $user->setActive(true);
        $user->setCreatedAt(date('Y-m-d H:i:s'));
        $user->setUpdatedAt(date('Y-m-d H:i:s'));

        // Save to database
        if (!$this->userRepository->save($user)) {
            throw new BusinessLogicException('Failed to create user');
        }

        return $user;
    }

    /**
     * Authenticate user
     */
    public function authenticateUser(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !$user->isActive()) {
            return null;
        }

        if (!PasswordHasher::verify($password, $user->getPassword())) {
            return null;
        }

        // Update last login
        $user->setLastLoginAt(date('Y-m-d H:i:s'));
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Update user
     */
    public function updateUser(int $userId, array $userData): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new BusinessLogicException('User not found');
        }

        // Validate input data (excluding password)
        if (isset($userData['password'])) {
            unset($userData['password']);
        }
        $this->validateUserData($userData, $userId);

        // Check for duplicate email (excluding current user)
        if (isset($userData['email']) && 
            $this->userRepository->emailExists($userData['email'], $userId)) {
            throw new BusinessLogicException('Email already exists');
        }

        // Check for duplicate username (excluding current user)
        if (isset($userData['username']) && 
            $this->userRepository->usernameExists($userData['username'], $userId)) {
            throw new BusinessLogicException('Username already exists');
        }

        // Update user data
        foreach ($userData as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($user, $method)) {
                $user->$method($value);
            }
        }

        $user->setUpdatedAt(date('Y-m-d H:i:s'));

        // Save to database
        if (!$this->userRepository->save($user)) {
            throw new BusinessLogicException('Failed to update user');
        }

        return $user;
    }

    /**
     * Change password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new BusinessLogicException('User not found');
        }

        // Verify current password
        if (!PasswordHasher::verify($currentPassword, $user->getPassword())) {
            throw new BusinessLogicException('Current password is incorrect');
        }

        // Validate new password
        $passwordErrors = PasswordHasher::validatePassword($newPassword);
        if (!empty($passwordErrors)) {
            throw new ValidationException('Password validation failed', ['password' => $passwordErrors]);
        }

        // Hash new password
        $user->setPassword(PasswordHasher::hash($newPassword));
        $user->setUpdatedAt(date('Y-m-d H:i:s'));

        return $this->userRepository->save($user);
    }

    /**
     * Deactivate user
     */
    public function deactivateUser(int $userId): bool
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new BusinessLogicException('User not found');
        }

        $user->setActive(false);
        $user->setUpdatedAt(date('Y-m-d H:i:s'));

        return $this->userRepository->save($user);
    }

    /**
     * Get user by ID
     */
    public function getUser(int $userId): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new BusinessLogicException('User not found');
        }

        return $user;
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): array
    {
        return $this->userRepository->findByRole($role);
    }

    /**
     * Validate user data
     */
    private function validateUserData(array $data, ?int $excludeUserId = null): void
    {
        $rules = [
            'username' => 'required|string|min:3|max:50',
            'email' => 'required|email|max:100',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'role' => 'required|in:admin,manager,staff,viewer'
        ];

        // Add password validation only for new users
        if (!isset($excludeUserId)) {
            $rules['password'] = 'required|string|min:8';
        }

        $errors = $this->validator->validate($data, $rules);
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
