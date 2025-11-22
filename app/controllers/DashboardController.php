<?php
// FILE: /app/controllers/DashboardController.php

/**
 * Dashboard Controller
 * Handles main dashboard display for different user roles
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class DashboardController extends Controller {

    /**
     * Show dashboard
     */
    public function index() {
        $this->requireAuth();

        $user = $this->user();

        if ($user['role'] === 'platform_admin') {
            $this->platformAdminDashboard();
        } elseif (in_array($user['role'], ['tenant_admin', 'manager'])) {
            $this->tenantDashboard();
        } else {
            $this->employeeDashboard();
        }
    }

    /**
     * Platform admin dashboard
     */
    private function platformAdminDashboard() {
        $tenantModel = $this->model('Tenant');
        $employeeModel = $this->model('Employee');
        $subscriptionModel = $this->model('TenantSubscription');

        // Get stats
        $totalTenants = $tenantModel->count();
        $activeTenants = $tenantModel->count(['status' => 'active']);
        $totalEmployees = $employeeModel->count();

        // Get recent tenants
        $recentTenants = $tenantModel->getAllWithSubscriptions(10, 0);

        $data = [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'total_employees' => $totalEmployees,
            'recent_tenants' => $recentTenants
        ];

        $this->view->render('dashboard/platform_admin', $data);
    }

    /**
     * Tenant admin/manager dashboard
     */
    private function tenantDashboard() {
        $tenantId = $this->tenantId();

        $employeeModel = $this->model('Employee');
        $departmentModel = $this->model('Department');
        $attendanceModel = $this->model('Attendance');
        $leaveModel = $this->model('LeaveRequest');
        $payrollModel = $this->model('PayrollRun');

        // Employee stats
        $totalEmployees = $employeeModel->countByTenant($tenantId);
        $activeEmployees = $employeeModel->countByTenant($tenantId, ['status' => 'active']);
        $probationEmployees = $employeeModel->countByTenant($tenantId, ['status' => 'probation']);

        // Department stats
        $departments = $departmentModel->getByTenant($tenantId);

        // Upcoming birthdays
        $upcomingBirthdays = $employeeModel->getUpcomingBirthdays($tenantId, 7);

        // Attendance summary
        $attendanceSummary = $attendanceModel->getAttendanceSummary($tenantId, date('m'), date('Y'));

        // Pending leave requests
        $pendingLeaves = $leaveModel->getLeaveRequestsByTenant($tenantId, ['status' => 'pending'], 5, 0);

        // Latest payroll run
        $latestPayroll = $payrollModel->getLatestPayrollRun($tenantId);

        $data = [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'probation_employees' => $probationEmployees,
            'departments' => $departments,
            'upcoming_birthdays' => $upcomingBirthdays,
            'attendance_summary' => $attendanceSummary,
            'pending_leaves' => $pendingLeaves,
            'latest_payroll' => $latestPayroll
        ];

        $this->view->render('dashboard/tenant', $data);
    }

    /**
     * Employee dashboard
     */
    private function employeeDashboard() {
        $user = $this->user();
        $tenantId = $this->tenantId();

        if (!$user['employee_id']) {
            $this->flash('error', 'No employee record found for your account.');
            $this->redirect('/logout');
        }

        $employeeModel = $this->model('Employee');
        $attendanceModel = $this->model('Attendance');
        $leaveModel = $this->model('LeaveRequest');
        $payrollModel = $this->model('PayrollItem');

        // Get employee details
        $employee = $employeeModel->getEmployeeWithDetails($user['employee_id'], $tenantId);

        // Get recent attendance
        $recentAttendance = $attendanceModel->getEmployeeMonthAttendance(
            $user['employee_id'],
            date('m'),
            date('Y'),
            $tenantId
        );

        // Get leave requests
        $leaveRequests = $leaveModel->getLeaveRequestsByTenant(
            $tenantId,
            ['employee_id' => $user['employee_id']],
            5,
            0
        );

        // Get recent payslips
        $payslips = $payrollModel->getEmployeePayrollHistory($user['employee_id'], 3);

        $data = [
            'employee' => $employee,
            'recent_attendance' => $recentAttendance,
            'leave_requests' => $leaveRequests,
            'payslips' => $payslips
        ];

        $this->view->render('dashboard/employee', $data);
    }
}
