<?php
// FILE: /config/database.php

/**
 * Database Configuration
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_DATABASE') ?: 'splashhr',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
];
