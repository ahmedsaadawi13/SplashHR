<?php
// FILE: /app/controllers/LoanController.php

/**
 * Loan Controller
 * Handles employee loan operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class LoanController extends Controller {

    /**
     * List all loans
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $loanModel = $this->model('Loan');

        // Filters
        $filters = [
            'employee_id' => $this->input('employee_id'),
            'status' => $this->input('status')
        ];

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $loans = $loanModel->getLoansByTenant($tenantId, $filters, $perPage, $offset);
        $total = $loanModel->count(array_merge(['tenant_id' => $tenantId], array_filter($filters)));
        $pagination = paginate($total, $page, $perPage);

        // Get employees for filter
        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);

        $data = [
            'loans' => $loans,
            'employees' => $employees,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $this->view->render('loans/index', $data);
    }

    /**
     * Show create loan form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->tenantId();

        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 1000, 0);

        $data = ['employees' => $employees];
        $this->view->render('loans/create', $data);
    }

    /**
     * Process create loan
     */
    public function store() {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/loans');
        }

        $tenantId = $this->tenantId();
        $loanModel = $this->model('Loan');

        $employeeId = $this->input('employee_id');
        $loanAmount = (float)$this->input('loan_amount');
        $totalInstallments = (int)$this->input('total_installments');
        $startMonth = (int)$this->input('start_month');
        $startYear = (int)$this->input('start_year');

        // Validation
        $validator = new Validator();
        $validator->required('employee_id', $employeeId, 'Employee');
        $validator->required('loan_amount', $loanAmount, 'Loan Amount');
        $validator->numeric('loan_amount', $loanAmount, 'Loan Amount');
        $validator->required('total_installments', $totalInstallments, 'Total Installments');

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            $this->redirect('/loans/create');
        }

        if ($loanAmount <= 0 || $totalInstallments <= 0) {
            $this->flash('error', 'Invalid loan amount or installments.');
            $this->redirect('/loans/create');
        }

        $installmentAmount = $loanAmount / $totalInstallments;

        $data = [
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'loan_amount' => $loanAmount,
            'installment_amount' => $installmentAmount,
            'total_installments' => $totalInstallments,
            'paid_installments' => 0,
            'remaining_amount' => $loanAmount,
            'start_month' => $startMonth,
            'start_year' => $startYear,
            'status' => 'active',
            'notes' => $this->sanitize($this->input('notes'))
        ];

        if ($loanModel->insert($data)) {
            $this->flash('success', 'Loan created successfully.');
            $this->redirect('/loans');
        } else {
            $this->flash('error', 'Failed to create loan.');
            $this->redirect('/loans/create');
        }
    }

    /**
     * Show loan details
     */
    public function view($id) {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $loanModel = $this->model('Loan');
        $loan = $loanModel->getLoanWithDetails($id, $tenantId);

        if (!$loan) {
            $this->flash('error', 'Loan not found.');
            $this->redirect('/loans');
        }

        $data = ['loan' => $loan];
        $this->view->render('loans/view', $data);
    }
}
