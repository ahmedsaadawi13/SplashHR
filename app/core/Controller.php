<?php
// FILE: /app/core/Controller.php

/**
 * Base Controller Class
 * All controllers extend this class
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Controller {

    protected $view;

    public function __construct() {
        $this->view = new View();
    }

    /**
     * Load a model
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }

    /**
     * Redirect to a URL
     * @param string $url
     */
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }

    /**
     * Return JSON response
     * @param mixed $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    protected function isAuthenticated() {
        return Auth::check();
    }

    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    /**
     * Check if user has role
     * @param string|array $roles
     * @return bool
     */
    protected function hasRole($roles) {
        return Auth::hasRole($roles);
    }

    /**
     * Require specific role
     * @param string|array $roles
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        if (!$this->hasRole($roles)) {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Get current user
     * @return array|null
     */
    protected function user() {
        return Auth::user();
    }

    /**
     * Get current tenant ID
     * @return int|null
     */
    protected function tenantId() {
        return Auth::tenantId();
    }

    /**
     * Validate CSRF token
     * @return bool
     */
    protected function validateCSRF() {
        $token = $_POST['csrf_token'] ?? '';
        return Session::validateCSRF($token);
    }

    /**
     * Get input from request
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function input($key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Sanitize input
     * @param string $input
     * @return string
     */
    protected function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Flash message to session
     * @param string $type success|error|warning|info
     * @param string $message
     */
    protected function flash($type, $message) {
        Session::setFlash($type, $message);
    }
}
