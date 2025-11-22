<?php
// FILE: /public/index.php

/**
 * Application Entry Point
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

// Start session
session_start();

// Set timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load core classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Validator.php';

// Load helper functions
require_once __DIR__ . '/../app/helpers/functions.php';

// Load all models
foreach (glob(__DIR__ . '/../app/models/*.php') as $model) {
    require_once $model;
}

// Load all controllers
foreach (glob(__DIR__ . '/../app/controllers/*.php') as $controller) {
    require_once $controller;
}

// Initialize Session
Session::start();

// Initialize Router
$router = new Router();

// Define routes

// Authentication routes
$router->get('/', 'DashboardController', 'index');
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'loginPost');
$router->get('/logout', 'AuthController', 'logout');

// Dashboard
$router->get('/dashboard', 'DashboardController', 'index');

// Tenants (Platform Admin)
$router->get('/tenants', 'TenantController', 'index');
$router->get('/tenants/:id', 'TenantController', 'view');

// Employees
$router->get('/employees', 'EmployeeController', 'index');
$router->get('/employees/create', 'EmployeeController', 'create');
$router->post('/employees/create', 'EmployeeController', 'store');
$router->get('/employees/:id', 'EmployeeController', 'view');
$router->get('/employees/:id/edit', 'EmployeeController', 'edit');
$router->post('/employees/:id/edit', 'EmployeeController', 'update');
$router->post('/employees/:id/delete', 'EmployeeController', 'delete');

// Departments
$router->get('/departments', 'DepartmentController', 'index');
$router->get('/departments/create', 'DepartmentController', 'create');
$router->post('/departments/create', 'DepartmentController', 'store');
$router->get('/departments/:id/edit', 'DepartmentController', 'edit');
$router->post('/departments/:id/edit', 'DepartmentController', 'update');
$router->post('/departments/:id/delete', 'DepartmentController', 'delete');

// Job Titles
$router->get('/job-titles', 'JobTitleController', 'index');
$router->get('/job-titles/create', 'JobTitleController', 'create');
$router->post('/job-titles/create', 'JobTitleController', 'store');
$router->get('/job-titles/:id/edit', 'JobTitleController', 'edit');
$router->post('/job-titles/:id/edit', 'JobTitleController', 'update');

// Attendance
$router->get('/attendance', 'AttendanceController', 'index');
$router->get('/attendance/create', 'AttendanceController', 'create');
$router->post('/attendance/create', 'AttendanceController', 'store');
$router->get('/attendance/:id/edit', 'AttendanceController', 'edit');
$router->post('/attendance/:id/edit', 'AttendanceController', 'update');
$router->post('/attendance/:id/delete', 'AttendanceController', 'delete');

// Leaves
$router->get('/leaves', 'LeaveController', 'index');
$router->get('/leaves/create', 'LeaveController', 'create');
$router->post('/leaves/create', 'LeaveController', 'store');
$router->post('/leaves/:id/approve', 'LeaveController', 'approve');
$router->post('/leaves/:id/reject', 'LeaveController', 'reject');

// Payroll
$router->get('/payroll', 'PayrollController', 'index');
$router->get('/payroll/create', 'PayrollController', 'create');
$router->post('/payroll/process', 'PayrollController', 'process');
$router->get('/payroll/:id', 'PayrollController', 'view');
$router->post('/payroll/:id/finalize', 'PayrollController', 'finalize');
$router->get('/payslip/:id', 'PayrollController', 'payslip');

// Loans
$router->get('/loans', 'LoanController', 'index');
$router->get('/loans/create', 'LoanController', 'create');
$router->post('/loans/create', 'LoanController', 'store');
$router->get('/loans/:id', 'LoanController', 'view');

// Settings
$router->get('/settings', 'SettingsController', 'index');
$router->post('/settings', 'SettingsController', 'update');

// API Routes
$router->get('/api/employees', 'ApiController', 'getEmployees');
$router->get('/api/employees/:id', 'ApiController', 'getEmployee');
$router->post('/api/attendance', 'ApiController', 'submitAttendance');
$router->get('/api/attendance', 'ApiController', 'getAttendance');

// Dispatch the request
$router->dispatch();
