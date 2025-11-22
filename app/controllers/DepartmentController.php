<?php
// FILE: /app/controllers/DepartmentController.php

/**
 * Department Controller
 * Handles department CRUD operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class DepartmentController extends Controller {

    /**
     * List all departments
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $departmentModel = $this->model('Department');
        $departments = $departmentModel->getByTenant($tenantId, null);

        $data = ['departments' => $departments];
        $this->view->render('departments/index', $data);
    }

    /**
     * Show create department form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->tenantId();

        // Check subscription limit
        $subscriptionModel = $this->model('TenantSubscription');
        if ($subscriptionModel->hasReachedDepartmentLimit($tenantId)) {
            $this->flash('error', 'You have reached your department limit. Please upgrade your subscription.');
            $this->redirect('/departments');
        }

        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);

        $data = ['employees' => $employees];
        $this->view->render('departments/create', $data);
    }

    /**
     * Process create department
     */
    public function store() {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/departments');
        }

        $tenantId = $this->tenantId();
        $departmentModel = $this->model('Department');

        $data = [
            'tenant_id' => $tenantId,
            'name' => $this->sanitize($this->input('name')),
            'code' => $this->sanitize($this->input('code')),
            'manager_id' => $this->input('manager_id') ?: null,
            'description' => $this->sanitize($this->input('description')),
            'status' => 'active'
        ];

        // Validation
        $validator = new Validator();
        $validator->required('name', $data['name'], 'Department Name');
        $validator->required('code', $data['code'], 'Department Code');

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            $this->redirect('/departments/create');
        }

        // Check if code exists
        if ($departmentModel->codeExists($data['code'], $tenantId)) {
            $this->flash('error', 'Department code already exists.');
            $this->redirect('/departments/create');
        }

        if ($departmentModel->insert($data)) {
            $this->flash('success', 'Department created successfully.');
            $this->redirect('/departments');
        } else {
            $this->flash('error', 'Failed to create department.');
            $this->redirect('/departments/create');
        }
    }

    /**
     * Show edit department form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->tenantId();

        $departmentModel = $this->model('Department');
        $department = $departmentModel->getDepartmentWithDetails($id, $tenantId);

        if (!$department) {
            $this->flash('error', 'Department not found.');
            $this->redirect('/departments');
        }

        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);

        $data = [
            'department' => $department,
            'employees' => $employees
        ];

        $this->view->render('departments/edit', $data);
    }

    /**
     * Process update department
     */
    public function update($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/departments');
        }

        $tenantId = $this->tenantId();
        $departmentModel = $this->model('Department');

        $department = $departmentModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$department) {
            $this->flash('error', 'Department not found.');
            $this->redirect('/departments');
        }

        $data = [
            'name' => $this->sanitize($this->input('name')),
            'code' => $this->sanitize($this->input('code')),
            'manager_id' => $this->input('manager_id') ?: null,
            'description' => $this->sanitize($this->input('description')),
            'status' => $this->input('status')
        ];

        // Check if code exists (excluding current)
        if ($departmentModel->codeExists($data['code'], $tenantId, $id)) {
            $this->flash('error', 'Department code already exists.');
            $this->redirect('/departments/' . $id . '/edit');
        }

        if ($departmentModel->update($id, $data)) {
            $this->flash('success', 'Department updated successfully.');
            $this->redirect('/departments');
        } else {
            $this->flash('error', 'Failed to update department.');
            $this->redirect('/departments/' . $id . '/edit');
        }
    }

    /**
     * Delete department
     */
    public function delete($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $tenantId = $this->tenantId();
        $departmentModel = $this->model('Department');

        $department = $departmentModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$department) {
            $this->json(['success' => false, 'message' => 'Department not found'], 404);
        }

        if ($departmentModel->delete($id)) {
            $this->flash('success', 'Department deleted successfully.');
            $this->redirect('/departments');
        } else {
            $this->flash('error', 'Failed to delete department.');
            $this->redirect('/departments');
        }
    }
}
