<?php
// FILE: /app/core/Session.php

/**
 * Session Management Class
 * Handles session and CSRF token management
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Session {

    /**
     * Start session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     * @param string $token
     * @return bool
     */
    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Set flash message
     * @param string $type
     * @param string $message
     */
    public static function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash message
     * @param string $type
     * @return string|null
     */
    public static function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Check if flash message exists
     * @param string $type
     * @return bool
     */
    public static function hasFlash($type) {
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Set session value
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove session value
     * @param string $key
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     */
    public static function destroy() {
        session_destroy();
    }
}
