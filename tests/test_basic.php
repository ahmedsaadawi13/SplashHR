<?php
// FILE: /tests/test_basic.php

/**
 * Basic Functional Tests for SplashHR
 * Run: php tests/test_basic.php
 */

// Load dependencies
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/helpers/functions.php';

// Load models
foreach (glob(__DIR__ . '/../app/models/*.php') as $model) {
    require_once $model;
}

echo "SplashHR - Running Basic Tests\n";
echo "================================\n\n";

$passed = 0;
$failed = 0;

function test($name, $callback) {
    global $passed, $failed;

    try {
        $result = $callback();
        if ($result) {
            echo "[PASS] $name\n";
            $passed++;
        } else {
            echo "[FAIL] $name\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "[ERROR] $name: " . $e->getMessage() . "\n";
        $failed++;
    }
}

// Test 1: Database Connection
test('Database connection works', function() {
    $db = Database::getInstance();
    return $db->getConnection() !== null;
});

// Test 2: Tenant Model
test('Can fetch tenants from database', function() {
    $tenantModel = new Tenant();
    $tenants = $tenantModel->findAll();
    return is_array($tenants);
});

// Test 3: User Model
test('Can fetch users from database', function() {
    $userModel = new User();
    $users = $userModel->findAll();
    return is_array($users);
});

// Test 4: Employee Model
test('Can fetch employees from database', function() {
    $employeeModel = new Employee();
    $employees = $employeeModel->findAll([], '', 10, 0);
    return is_array($employees);
});

// Test 5: Tenant Isolation
test('Employee count filtering by tenant works', function() {
    $employeeModel = new Employee();
    $tenant1Employees = $employeeModel->count(['tenant_id' => 1]);
    $tenant2Employees = $employeeModel->count(['tenant_id' => 2]);
    return $tenant1Employees > 0 && $tenant2Employees > 0;
});

// Test 6: Attendance Model
test('Can fetch attendance records', function() {
    $attendanceModel = new Attendance();
    $attendance = $attendanceModel->findAll([], '', 10, 0);
    return is_array($attendance);
});

// Test 7: Payroll Model
test('Can fetch payroll runs', function() {
    $payrollModel = new PayrollRun();
    $payrolls = $payrollModel->findAll();
    return is_array($payrolls);
});

// Test 8: Helper Function - Currency Formatting
test('Currency formatting works', function() {
    $formatted = formatCurrency(1000.50, 'USD');
    return strpos($formatted, '1,000.50') !== false;
});

// Test 9: Helper Function - Date Formatting
test('Date formatting works', function() {
    $formatted = formatDate('2025-01-15');
    return $formatted === '2025-01-15';
});

// Test 10: Helper Function - Work Days Calculation
test('Work days calculation works', function() {
    $workDays = calculateWorkDays('2025-01-01', '2025-01-07');
    return $workDays === 5; // Mon-Fri
});

echo "\n================================\n";
echo "Tests completed: " . ($passed + $failed) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($failed === 0) {
    echo "\nAll tests passed!\n";
    exit(0);
} else {
    echo "\nSome tests failed.\n";
    exit(1);
}
