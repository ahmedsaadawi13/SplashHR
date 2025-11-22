<?php
// FILE: /app/controllers/ApiController.php

/**
 * API Controller
 * Handles REST API endpoints for external integrations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class ApiController extends Controller {

    private $tenantId;
    private $apiKey;

    /**
     * Authenticate API request
     */
    private function authenticate() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->json(['error' => 'API key is required'], 401);
        }

        $apiKeyModel = $this->model('ApiKey');
        $keyData = $apiKeyModel->validateApiKey($apiKey);

        if (!$keyData) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        $this->tenantId = $keyData['tenant_id'];
        $this->apiKey = $keyData;
    }

    /**
     * Check API permission
     */
    private function checkPermission($permission) {
        $permissions = explode(',', $this->apiKey['permissions']);
        if (!in_array($permission, $permissions)) {
            $this->json(['error' => 'Permission denied'], 403);
        }
    }

    /**
     * Get employees list
     * GET /api/employees
     */
    public function getEmployees() {
        $this->authenticate();
        $this->checkPermission('employees.read');

        $employeeModel = $this->model('Employee');

        // Filters
        $filters = [
            'department_id' => $this->input('department_id'),
            'status' => $this->input('status', 'active'),
            'search' => $this->input('search')
        ];

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = min(100, (int)$this->input('per_page', 20));
        $offset = ($page - 1) * $perPage;

        $employees = $employeeModel->getEmployeesByTenant($this->tenantId, $filters, $perPage, $offset);
        $total = $employeeModel->countByTenant($this->tenantId, $filters);

        // Format response
        $data = [];
        foreach ($employees as $emp) {
            $data[] = [
                'id' => $emp['id'],
                'employee_code' => $emp['employee_code'],
                'first_name' => $emp['first_name'],
                'last_name' => $emp['last_name'],
                'email' => $emp['email'],
                'phone' => $emp['phone'],
                'department' => $emp['department_name'],
                'job_title' => $emp['job_title_name'],
                'employment_type' => $emp['employment_type'],
                'status' => $emp['status'],
                'hire_date' => $emp['hire_date']
            ];
        }

        $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Get single employee
     * GET /api/employees/:id
     */
    public function getEmployee($id) {
        $this->authenticate();
        $this->checkPermission('employees.read');

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeWithDetails($id, $this->tenantId);

        if (!$employee) {
            $this->json(['error' => 'Employee not found'], 404);
        }

        $data = [
            'id' => $employee['id'],
            'employee_code' => $employee['employee_code'],
            'first_name' => $employee['first_name'],
            'last_name' => $employee['last_name'],
            'email' => $employee['email'],
            'phone' => $employee['phone'],
            'gender' => $employee['gender'],
            'date_of_birth' => $employee['date_of_birth'],
            'department' => $employee['department_name'],
            'job_title' => $employee['job_title_name'],
            'employment_type' => $employee['employment_type'],
            'basic_salary' => $employee['basic_salary'],
            'status' => $employee['status'],
            'hire_date' => $employee['hire_date']
        ];

        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Submit attendance record
     * POST /api/attendance
     */
    public function submitAttendance() {
        $this->authenticate();
        $this->checkPermission('attendance.write');

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->json(['error' => 'Invalid JSON input'], 400);
        }

        $employeeCode = $input['employee_code'] ?? '';
        $date = $input['date'] ?? '';
        $checkIn = $input['check_in'] ?? '';
        $checkOut = $input['check_out'] ?? '';

        // Validation
        if (empty($employeeCode) || empty($date)) {
            $this->json(['error' => 'employee_code and date are required'], 400);
        }

        // Find employee by code
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->findOne([
            'tenant_id' => $this->tenantId,
            'employee_code' => $employeeCode
        ]);

        if (!$employee) {
            $this->json(['error' => 'Employee not found'], 404);
        }

        $attendanceModel = $this->model('Attendance');

        // Check if attendance already exists
        if ($attendanceModel->attendanceExists($employee['id'], $date, $this->tenantId)) {
            $this->json(['error' => 'Attendance record already exists for this date'], 400);
        }

        // Calculate total hours
        $totalHours = 0;
        if ($checkIn && $checkOut) {
            $totalHours = $attendanceModel->calculateTotalHours($checkIn, $checkOut);
        }

        // Get settings
        $settingModel = $this->model('Setting');
        $workStartTime = $settingModel->getValue('work_start_time', $this->tenantId, '09:00');
        $workHoursPerDay = $settingModel->getValue('work_hours_per_day', $this->tenantId, 8);

        $isLate = 0;
        if ($checkIn && $checkIn > $workStartTime) {
            $isLate = 1;
        }

        $overtimeHours = 0;
        if ($totalHours > $workHoursPerDay) {
            $overtimeHours = $totalHours - $workHoursPerDay;
        }

        $data = [
            'tenant_id' => $this->tenantId,
            'employee_id' => $employee['id'],
            'date' => $date,
            'check_in' => $checkIn ?: null,
            'check_out' => $checkOut ?: null,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'is_late' => $isLate,
            'is_absent' => 0
        ];

        $attendanceId = $attendanceModel->insert($data);

        if ($attendanceId) {
            $this->json([
                'success' => true,
                'message' => 'Attendance submitted successfully',
                'data' => [
                    'id' => $attendanceId,
                    'employee_code' => $employeeCode,
                    'date' => $date,
                    'total_hours' => $totalHours
                ]
            ], 201);
        } else {
            $this->json(['error' => 'Failed to submit attendance'], 500);
        }
    }

    /**
     * Get attendance records
     * GET /api/attendance
     */
    public function getAttendance() {
        $this->authenticate();
        $this->checkPermission('attendance.read');

        $attendanceModel = $this->model('Attendance');

        // Filters
        $filters = [
            'employee_id' => $this->input('employee_id'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'month' => $this->input('month'),
            'year' => $this->input('year')
        ];

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = min(100, (int)$this->input('per_page', 20));
        $offset = ($page - 1) * $perPage;

        $attendanceRecords = $attendanceModel->getAttendanceByTenant($this->tenantId, $filters, $perPage, $offset);
        $total = $attendanceModel->countByTenant($this->tenantId, $filters);

        // Format response
        $data = [];
        foreach ($attendanceRecords as $att) {
            $data[] = [
                'id' => $att['id'],
                'employee_code' => $att['employee_code'],
                'employee_name' => $att['first_name'] . ' ' . $att['last_name'],
                'date' => $att['date'],
                'check_in' => $att['check_in'],
                'check_out' => $att['check_out'],
                'total_hours' => $att['total_hours'],
                'overtime_hours' => $att['overtime_hours'],
                'is_late' => (bool)$att['is_late'],
                'is_absent' => (bool)$att['is_absent']
            ];
        }

        $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }
}
