<?php
// FILE: /app/controllers/AttendanceController.php

/**
 * Attendance Controller
 * Handles employee attendance operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class AttendanceController extends Controller {

    /**
     * List all attendance records
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $attendanceModel = $this->model('Attendance');

        // Get filters
        $filters = [
            'employee_id' => $this->input('employee_id'),
            'department_id' => $this->input('department_id'),
            'month' => $this->input('month', date('m')),
            'year' => $this->input('year', date('Y'))
        ];

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $attendanceRecords = $attendanceModel->getAttendanceByTenant($tenantId, $filters, $perPage, $offset);
        $total = $attendanceModel->countByTenant($tenantId, $filters);
        $pagination = paginate($total, $page, $perPage);

        // Get employees and departments for filters
        $employeeModel = $this->model('Employee');
        $departmentModel = $this->model('Department');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);
        $departments = $departmentModel->getByTenant($tenantId);

        $data = [
            'attendance_records' => $attendanceRecords,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $this->view->render('attendance/index', $data);
    }

    /**
     * Show create attendance form
     */
    public function create() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);

        $data = ['employees' => $employees];
        $this->view->render('attendance/create', $data);
    }

    /**
     * Process create attendance
     */
    public function store() {
        $this->requireRole(['tenant_admin', 'manager']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/attendance');
        }

        $tenantId = $this->tenantId();
        $attendanceModel = $this->model('Attendance');

        $employeeId = $this->input('employee_id');
        $date = $this->input('date');
        $checkIn = $this->input('check_in');
        $checkOut = $this->input('check_out');
        $isAbsent = $this->input('is_absent') ? 1 : 0;

        // Validation
        $validator = new Validator();
        $validator->required('employee_id', $employeeId, 'Employee');
        $validator->required('date', $date, 'Date');

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            $this->redirect('/attendance/create');
        }

        // Check if attendance already exists
        if ($attendanceModel->attendanceExists($employeeId, $date, $tenantId)) {
            $this->flash('error', 'Attendance record already exists for this employee on this date.');
            $this->redirect('/attendance/create');
        }

        // Calculate total hours
        $totalHours = 0;
        if (!$isAbsent && $checkIn && $checkOut) {
            $totalHours = $attendanceModel->calculateTotalHours($checkIn, $checkOut);
        }

        // Get settings for late check
        $settingModel = $this->model('Setting');
        $workStartTime = $settingModel->getValue('work_start_time', $tenantId, '09:00');
        $workHoursPerDay = $settingModel->getValue('work_hours_per_day', $tenantId, 8);

        $isLate = 0;
        if (!$isAbsent && $checkIn && $checkIn > $workStartTime) {
            $isLate = 1;
        }

        $overtimeHours = 0;
        if ($totalHours > $workHoursPerDay) {
            $overtimeHours = $totalHours - $workHoursPerDay;
        }

        $data = [
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'date' => $date,
            'check_in' => $checkIn ?: null,
            'check_out' => $checkOut ?: null,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'is_late' => $isLate,
            'is_absent' => $isAbsent,
            'notes' => $this->sanitize($this->input('notes'))
        ];

        if ($attendanceModel->insert($data)) {
            $this->flash('success', 'Attendance record created successfully.');
            $this->redirect('/attendance');
        } else {
            $this->flash('error', 'Failed to create attendance record.');
            $this->redirect('/attendance/create');
        }
    }

    /**
     * Show edit attendance form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $attendanceModel = $this->model('Attendance');
        $attendance = $attendanceModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);

        if (!$attendance) {
            $this->flash('error', 'Attendance record not found.');
            $this->redirect('/attendance');
        }

        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);

        $data = [
            'attendance' => $attendance,
            'employees' => $employees
        ];

        $this->view->render('attendance/edit', $data);
    }

    /**
     * Process update attendance
     */
    public function update($id) {
        $this->requireRole(['tenant_admin', 'manager']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/attendance');
        }

        $tenantId = $this->tenantId();
        $attendanceModel = $this->model('Attendance');

        $attendance = $attendanceModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$attendance) {
            $this->flash('error', 'Attendance record not found.');
            $this->redirect('/attendance');
        }

        $checkIn = $this->input('check_in');
        $checkOut = $this->input('check_out');
        $isAbsent = $this->input('is_absent') ? 1 : 0;

        // Calculate total hours
        $totalHours = 0;
        if (!$isAbsent && $checkIn && $checkOut) {
            $totalHours = $attendanceModel->calculateTotalHours($checkIn, $checkOut);
        }

        // Get settings
        $settingModel = $this->model('Setting');
        $workStartTime = $settingModel->getValue('work_start_time', $tenantId, '09:00');
        $workHoursPerDay = $settingModel->getValue('work_hours_per_day', $tenantId, 8);

        $isLate = 0;
        if (!$isAbsent && $checkIn && $checkIn > $workStartTime) {
            $isLate = 1;
        }

        $overtimeHours = 0;
        if ($totalHours > $workHoursPerDay) {
            $overtimeHours = $totalHours - $workHoursPerDay;
        }

        $data = [
            'check_in' => $checkIn ?: null,
            'check_out' => $checkOut ?: null,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'is_late' => $isLate,
            'is_absent' => $isAbsent,
            'notes' => $this->sanitize($this->input('notes'))
        ];

        if ($attendanceModel->update($id, $data)) {
            $this->flash('success', 'Attendance record updated successfully.');
            $this->redirect('/attendance');
        } else {
            $this->flash('error', 'Failed to update attendance record.');
            $this->redirect('/attendance/' . $id . '/edit');
        }
    }

    /**
     * Delete attendance
     */
    public function delete($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $tenantId = $this->tenantId();
        $attendanceModel = $this->model('Attendance');

        $attendance = $attendanceModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$attendance) {
            $this->json(['success' => false, 'message' => 'Attendance record not found'], 404);
        }

        if ($attendanceModel->delete($id)) {
            $this->flash('success', 'Attendance record deleted successfully.');
            $this->redirect('/attendance');
        } else {
            $this->flash('error', 'Failed to delete attendance record.');
            $this->redirect('/attendance');
        }
    }
}
