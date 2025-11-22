<?php
// FILE: /app/controllers/LeaveController.php

/**
 * Leave Controller
 * Handles leave request operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class LeaveController extends Controller {

    /**
     * List all leave requests
     */
    public function index() {
        $this->requireAuth();
        $tenantId = $this->tenantId();
        $user = $this->user();

        $leaveModel = $this->model('LeaveRequest');

        // Filters
        $filters = [
            'status' => $this->input('status'),
            'leave_type_id' => $this->input('leave_type_id')
        ];

        // If employee role, only show their leaves
        if ($user['role'] === 'employee') {
            $filters['employee_id'] = $user['employee_id'];
        } else {
            $filters['employee_id'] = $this->input('employee_id');
        }

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $leaveRequests = $leaveModel->getLeaveRequestsByTenant($tenantId, $filters, $perPage, $offset);
        $total = $leaveModel->countByTenant($tenantId, $filters);
        $pagination = paginate($total, $page, $perPage);

        // Get leave types for filter
        $leaveTypeModel = $this->model('LeaveType');
        $leaveTypes = $leaveTypeModel->getByTenant($tenantId);

        // Get employees for filter (admin only)
        $employees = [];
        if (in_array($user['role'], ['tenant_admin', 'manager'])) {
            $employeeModel = $this->model('Employee');
            $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);
        }

        $data = [
            'leave_requests' => $leaveRequests,
            'leave_types' => $leaveTypes,
            'employees' => $employees,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $this->view->render('leaves/index', $data);
    }

    /**
     * Show create leave request form
     */
    public function create() {
        $this->requireAuth();
        $tenantId = $this->tenantId();

        $leaveTypeModel = $this->model('LeaveType');
        $leaveTypes = $leaveTypeModel->getByTenant($tenantId);

        $data = ['leave_types' => $leaveTypes];
        $this->view->render('leaves/create', $data);
    }

    /**
     * Process create leave request
     */
    public function store() {
        $this->requireAuth();

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/leaves');
        }

        $tenantId = $this->tenantId();
        $user = $this->user();
        $leaveModel = $this->model('LeaveRequest');

        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $leaveTypeId = $this->input('leave_type_id');
        $reason = $this->sanitize($this->input('reason'));

        // Validation
        $validator = new Validator();
        $validator->required('start_date', $startDate, 'Start Date');
        $validator->required('end_date', $endDate, 'End Date');
        $validator->required('leave_type_id', $leaveTypeId, 'Leave Type');

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            $this->redirect('/leaves/create');
        }

        // Calculate total days
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $totalDays = calculateWorkDays($startDate, $endDate);

        if ($totalDays <= 0) {
            $this->flash('error', 'Invalid date range.');
            $this->redirect('/leaves/create');
        }

        $data = [
            'tenant_id' => $tenantId,
            'employee_id' => $user['employee_id'],
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $reason,
            'status' => 'pending'
        ];

        $leaveId = $leaveModel->insert($data);

        if ($leaveId) {
            // Create notification for admin/manager
            $notificationModel = $this->model('Notification');
            $notificationModel->createNotification(
                $tenantId,
                null, // Will need to get admin user
                'leave_request',
                'New Leave Request',
                'New leave request from ' . $user['first_name'] . ' ' . $user['last_name']
            );

            $this->flash('success', 'Leave request submitted successfully.');
            $this->redirect('/leaves');
        } else {
            $this->flash('error', 'Failed to submit leave request.');
            $this->redirect('/leaves/create');
        }
    }

    /**
     * Approve leave request
     */
    public function approve($id) {
        $this->requireRole(['tenant_admin', 'manager']);

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $tenantId = $this->tenantId();
        $user = $this->user();
        $leaveModel = $this->model('LeaveRequest');

        $leave = $leaveModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$leave) {
            $this->json(['success' => false, 'message' => 'Leave request not found'], 404);
        }

        $data = [
            'status' => 'approved',
            'approved_by' => $user['id'],
            'approved_at' => date('Y-m-d H:i:s')
        ];

        if ($leaveModel->update($id, $data)) {
            // Create notification for employee
            $notificationModel = $this->model('Notification');
            $employeeUser = $this->model('User')->findOne(['employee_id' => $leave['employee_id']]);
            if ($employeeUser) {
                $notificationModel->createNotification(
                    $tenantId,
                    $employeeUser['id'],
                    'leave_approved',
                    'Leave Request Approved',
                    'Your leave request has been approved.'
                );
            }

            $this->flash('success', 'Leave request approved successfully.');
            $this->redirect('/leaves');
        } else {
            $this->flash('error', 'Failed to approve leave request.');
            $this->redirect('/leaves');
        }
    }

    /**
     * Reject leave request
     */
    public function reject($id) {
        $this->requireRole(['tenant_admin', 'manager']);

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $tenantId = $this->tenantId();
        $leaveModel = $this->model('LeaveRequest');

        $leave = $leaveModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$leave) {
            $this->json(['success' => false, 'message' => 'Leave request not found'], 404);
        }

        $rejectionReason = $this->sanitize($this->input('rejection_reason'));

        $data = [
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
            'approved_by' => $this->user()['id'],
            'approved_at' => date('Y-m-d H:i:s')
        ];

        if ($leaveModel->update($id, $data)) {
            // Create notification for employee
            $notificationModel = $this->model('Notification');
            $employeeUser = $this->model('User')->findOne(['employee_id' => $leave['employee_id']]);
            if ($employeeUser) {
                $notificationModel->createNotification(
                    $tenantId,
                    $employeeUser['id'],
                    'leave_rejected',
                    'Leave Request Rejected',
                    'Your leave request has been rejected.'
                );
            }

            $this->flash('success', 'Leave request rejected.');
            $this->redirect('/leaves');
        } else {
            $this->flash('error', 'Failed to reject leave request.');
            $this->redirect('/leaves');
        }
    }
}
