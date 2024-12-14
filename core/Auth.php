<?php

namespace Core;

class Auth {
    private $db;
    
    public function __construct(\Database $db) {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login(string $email, string $password): array {
        $errors = $this->validateLogin($email, $password);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $user = $this->db->query('SELECT * FROM users WHERE email = :email', ['email' => $email])->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            return ['success' => true];
        }
        
        return ['success' => false, 'errors' => ['Invalid credentials']];
    }
    
    public function signup(array $data): array {
        $errors = $this->validateSignup($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $this->db->query(
                'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)',
                ['name' => $data['name'], 'email' => $data['email'], 'password' => $hashedPassword]
            );
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['Registration failed']];
        }
    }
    
    private function validateLogin(string $email, string $password): array {
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        return $errors;
    }
    
    private function validateSignup(array $data): array {
        $errors = [];
        if (empty($data['name'])) {
            $errors[] = 'Name is required.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if (empty($data['password'])) {
            $errors[] = 'Password is required.';
        }
        if (empty($data['confirm'])) {
            $errors[] = 'You must agree to the terms and policy.';
        }
        
        $existingUser = $this->db->query('SELECT id FROM users WHERE email = :email', ['email' => $data['email']])->fetch();
        if ($existingUser) {
            $errors[] = 'Email is already registered.';
        }
        
        return $errors;
    }
    
    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }
    
    public static function logout(): void {
        session_unset();
        session_destroy();
    }
    
    public static function user() {
        return $_SESSION['user_name'] ?? null;
    }
}
