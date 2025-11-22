<?php
// FILE: /app/core/Auth.php

/**
 * Authentication Class
 * Handles user authentication and authorization
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Auth {

    /**
     * Check if user is authenticated
     * @return bool
     */
    public static function check() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current authenticated user
     * @return array|null
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        if (!isset($_SESSION['user_data'])) {
            // Load user data from database
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_data'] = $user;
            } else {
                self::logout();
                return null;
            }
        }

        return $_SESSION['user_data'];
    }

    /**
     * Get current user ID
     * @return int|null
     */
    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current tenant ID
     * @return int|null
     */
    public static function tenantId() {
        $user = self::user();
        return $user['tenant_id'] ?? null;
    }

    /**
     * Check if user has role
     * @param string|array $roles
     * @return bool
     */
    public static function hasRole($roles) {
        $user = self::user();
        if (!$user) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($user['role'], $roles);
        }

        return $user['role'] === $roles;
    }

    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return bool
     */
    public static function login($email, $password) {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_data'] = $user;

            // Update last login
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $updateStmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
            $updateStmt->execute();

            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public static function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_data']);
        session_destroy();
    }

    /**
     * Hash password
     * @param string $password
     * @return string
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
