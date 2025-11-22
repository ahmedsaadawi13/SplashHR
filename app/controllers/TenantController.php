<?php
// FILE: /app/controllers/TenantController.php

/**
 * Tenant Controller (Platform Admin)
 * Handles tenant management for platform administrators
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class TenantController extends Controller {

    /**
     * List all tenants
     */
    public function index() {
        $this->requireRole(['platform_admin']);

        $tenantModel = $this->model('Tenant');

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $tenants = $tenantModel->getAllWithSubscriptions($perPage, $offset);
        $total = $tenantModel->count();
        $pagination = paginate($total, $page, $perPage);

        $data = [
            'tenants' => $tenants,
            'pagination' => $pagination
        ];

        $this->view->render('tenants/index', $data);
    }

    /**
     * View tenant details
     */
    public function view($id) {
        $this->requireRole(['platform_admin']);

        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->getTenantWithSubscription($id);

        if (!$tenant) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect('/tenants');
        }

        // Get employee count
        $employeeModel = $this->model('Employee');
        $employeeCount = $employeeModel->count(['tenant_id' => $id]);

        $data = [
            'tenant' => $tenant,
            'employee_count' => $employeeCount
        ];

        $this->view->render('tenants/view', $data);
    }
}
