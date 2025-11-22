<?php
// FILE: /app/controllers/EmployeeController.php

/**
 * Employee Controller
 * Handles employee CRUD operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class EmployeeController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * List all employees
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $employeeModel = $this->model('Employee');

        // Get filters
        $filters = [
            'department_id' => $this->input('department_id'),
            'status' => $this->input('status'),
            'search' => $this->input('search')
        ];

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $employees = $employeeModel->getEmployeesByTenant($tenantId, $filters, $perPage, $offset);
        $total = $employeeModel->countByTenant($tenantId, $filters);
        $pagination = paginate($total, $page, $perPage);

        // Get departments for filter
        $departmentModel = $this->model('Department');
        $departments = $departmentModel->getByTenant($tenantId);

        $data = [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $this->view->render('employees/index', $data);
    }

    /**
     * Show employee details
     */
    public function view($id) {
        $this->requireAuth();
        $tenantId = $this->tenantId();

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeWithDetails($id, $tenantId);

        if (!$employee) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('/employees');
        }

        // Get documents
        $documentModel = $this->model('Document');
        $documents = $documentModel->getEmployeeDocuments($id, $tenantId);

        $data = [
            'employee' => $employee,
            'documents' => $documents
        ];

        $this->view->render('employees/view', $data);
    }

    /**
     * Show create employee form
     */
    public function create() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        // Check subscription limit
        $subscriptionModel = $this->model('TenantSubscription');
        if ($subscriptionModel->hasReachedEmployeeLimit($tenantId)) {
            $this->flash('error', 'You have reached your employee limit. Please upgrade your subscription.');
            $this->redirect('/employees');
        }

        $departmentModel = $this->model('Department');
        $jobTitleModel = $this->model('JobTitle');

        $data = [
            'departments' => $departmentModel->getByTenant($tenantId),
            'job_titles' => $jobTitleModel->getByTenant($tenantId)
        ];

        $this->view->render('employees/create', $data);
    }

    /**
     * Process create employee
     */
    public function store() {
        $this->requireRole(['tenant_admin', 'manager']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/employees');
        }

        $tenantId = $this->tenantId();
        $employeeModel = $this->model('Employee');

        $data = [
            'tenant_id' => $tenantId,
            'employee_code' => $this->sanitize($this->input('employee_code')),
            'first_name' => $this->sanitize($this->input('first_name')),
            'last_name' => $this->sanitize($this->input('last_name')),
            'email' => $this->sanitize($this->input('email')),
            'phone' => $this->sanitize($this->input('phone')),
            'gender' => $this->input('gender'),
            'date_of_birth' => $this->input('date_of_birth'),
            'hire_date' => $this->input('hire_date'),
            'department_id' => $this->input('department_id') ?: null,
            'job_title_id' => $this->input('job_title_id') ?: null,
            'employment_type' => $this->input('employment_type'),
            'basic_salary' => $this->input('basic_salary', 0),
            'housing_allowance' => $this->input('housing_allowance', 0),
            'transport_allowance' => $this->input('transport_allowance', 0),
            'other_allowances' => $this->input('other_allowances', 0),
            'social_security_deduction' => $this->input('social_security_deduction', 0),
            'bank_name' => $this->sanitize($this->input('bank_name')),
            'bank_account_number' => $this->sanitize($this->input('bank_account_number')),
            'status' => $this->input('status', 'active')
        ];

        // Validation
        $validator = new Validator();
        $validator->required('employee_code', $data['employee_code'], 'Employee Code');
        $validator->required('first_name', $data['first_name'], 'First Name');
        $validator->required('last_name', $data['last_name'], 'Last Name');
        $validator->required('email', $data['email'], 'Email');
        $validator->email('email', $data['email']);
        $validator->required('hire_date', $data['hire_date'], 'Hire Date');

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            $this->redirect('/employees/create');
        }

        // Check if employee code exists
        if ($employeeModel->employeeCodeExists($data['employee_code'], $tenantId)) {
            $this->flash('error', 'Employee code already exists.');
            $this->redirect('/employees/create');
        }

        $employeeId = $employeeModel->insert($data);

        if ($employeeId) {
            $this->flash('success', 'Employee created successfully.');
            $this->redirect('/employees/' . $employeeId);
        } else {
            $this->flash('error', 'Failed to create employee.');
            $this->redirect('/employees/create');
        }
    }

    /**
     * Show edit employee form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeWithDetails($id, $tenantId);

        if (!$employee) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('/employees');
        }

        $departmentModel = $this->model('Department');
        $jobTitleModel = $this->model('JobTitle');

        $data = [
            'employee' => $employee,
            'departments' => $departmentModel->getByTenant($tenantId),
            'job_titles' => $jobTitleModel->getByTenant($tenantId)
        ];

        $this->view->render('employees/edit', $data);
    }

    /**
     * Process update employee
     */
    public function update($id) {
        $this->requireRole(['tenant_admin', 'manager']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/employees');
        }

        $tenantId = $this->tenantId();
        $employeeModel = $this->model('Employee');

        $employee = $employeeModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$employee) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('/employees');
        }

        $data = [
            'employee_code' => $this->sanitize($this->input('employee_code')),
            'first_name' => $this->sanitize($this->input('first_name')),
            'last_name' => $this->sanitize($this->input('last_name')),
            'email' => $this->sanitize($this->input('email')),
            'phone' => $this->sanitize($this->input('phone')),
            'gender' => $this->input('gender'),
            'date_of_birth' => $this->input('date_of_birth'),
            'hire_date' => $this->input('hire_date'),
            'department_id' => $this->input('department_id') ?: null,
            'job_title_id' => $this->input('job_title_id') ?: null,
            'employment_type' => $this->input('employment_type'),
            'basic_salary' => $this->input('basic_salary', 0),
            'housing_allowance' => $this->input('housing_allowance', 0),
            'transport_allowance' => $this->input('transport_allowance', 0),
            'other_allowances' => $this->input('other_allowances', 0),
            'social_security_deduction' => $this->input('social_security_deduction', 0),
            'bank_name' => $this->sanitize($this->input('bank_name')),
            'bank_account_number' => $this->sanitize($this->input('bank_account_number')),
            'status' => $this->input('status')
        ];

        // Check if employee code exists (excluding current)
        if ($employeeModel->employeeCodeExists($data['employee_code'], $tenantId, $id)) {
            $this->flash('error', 'Employee code already exists.');
            $this->redirect('/employees/' . $id . '/edit');
        }

        if ($employeeModel->update($id, $data)) {
            $this->flash('success', 'Employee updated successfully.');
            $this->redirect('/employees/' . $id);
        } else {
            $this->flash('error', 'Failed to update employee.');
            $this->redirect('/employees/' . $id . '/edit');
        }
    }

    /**
     * Delete employee
     */
    public function delete($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $tenantId = $this->tenantId();
        $employeeModel = $this->model('Employee');

        $employee = $employeeModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$employee) {
            $this->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        if ($employeeModel->delete($id)) {
            $this->flash('success', 'Employee deleted successfully.');
            $this->redirect('/employees');
        } else {
            $this->flash('error', 'Failed to delete employee.');
            $this->redirect('/employees');
        }
    }
}
