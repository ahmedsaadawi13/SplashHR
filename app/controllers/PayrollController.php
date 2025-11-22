<?php
// FILE: /app/controllers/PayrollController.php

/**
 * Payroll Controller
 * Handles payroll processing operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class PayrollController extends Controller {

    /**
     * List all payroll runs
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $payrollModel = $this->model('PayrollRun');

        // Pagination
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $payrollRuns = $payrollModel->getPayrollRunsByTenant($tenantId, $perPage, $offset);
        $total = $payrollModel->count(['tenant_id' => $tenantId]);
        $pagination = paginate($total, $page, $perPage);

        $data = [
            'payroll_runs' => $payrollRuns,
            'pagination' => $pagination
        ];

        $this->view->render('payroll/index', $data);
    }

    /**
     * Show payroll run details
     */
    public function view($id) {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $payrollModel = $this->model('PayrollRun');
        $payrollRun = $payrollModel->getPayrollRunWithDetails($id, $tenantId);

        if (!$payrollRun) {
            $this->flash('error', 'Payroll run not found.');
            $this->redirect('/payroll');
        }

        // Get payroll items
        $payrollItemModel = $this->model('PayrollItem');
        $payrollItems = $payrollItemModel->getPayrollItemsByRun($id);

        $data = [
            'payroll_run' => $payrollRun,
            'payroll_items' => $payrollItems
        ];

        $this->view->render('payroll/view', $data);
    }

    /**
     * Show create payroll run form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->tenantId();

        $data = [];
        $this->view->render('payroll/create', $data);
    }

    /**
     * Process payroll run
     */
    public function process() {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/payroll');
        }

        $tenantId = $this->tenantId();
        $user = $this->user();

        $month = (int)$this->input('month');
        $year = (int)$this->input('year');

        // Validation
        if ($month < 1 || $month > 12 || $year < 2000) {
            $this->flash('error', 'Invalid month or year.');
            $this->redirect('/payroll/create');
        }

        $payrollModel = $this->model('PayrollRun');

        // Check if payroll already exists
        if ($payrollModel->payrollRunExists($month, $year, $tenantId)) {
            $this->flash('error', 'Payroll run already exists for this period.');
            $this->redirect('/payroll/create');
        }

        // Create payroll run
        $payDate = $this->input('pay_date') ?: date('Y-m-d', strtotime("$year-$month-25"));

        $payrollRunId = $payrollModel->insert([
            'tenant_id' => $tenantId,
            'month' => $month,
            'year' => $year,
            'pay_date' => $payDate,
            'status' => 'draft',
            'processed_by' => $user['id']
        ]);

        if (!$payrollRunId) {
            $this->flash('error', 'Failed to create payroll run.');
            $this->redirect('/payroll/create');
        }

        // Get all active employees
        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getEmployeesByTenant($tenantId, ['status' => 'active'], 10000, 0);

        $attendanceModel = $this->model('Attendance');
        $leaveModel = $this->model('LeaveRequest');
        $loanModel = $this->model('Loan');
        $settingModel = $this->model('Setting');
        $payrollItemModel = $this->model('PayrollItem');

        $overtimeRate = $settingModel->getValue('overtime_rate_per_hour', $tenantId, 25);
        $workingDaysInMonth = 22; // Can be calculated based on actual working days

        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;

        foreach ($employees as $employee) {
            // Get attendance for month
            $attendance = $attendanceModel->getEmployeeMonthAttendance($employee['id'], $month, $year, $tenantId);

            $presentDays = 0;
            $absentDays = 0;
            $overtimeHours = 0;

            foreach ($attendance as $att) {
                if ($att['is_absent']) {
                    $absentDays++;
                } else {
                    $presentDays++;
                }
                $overtimeHours += $att['overtime_hours'];
            }

            $absentDays = $workingDaysInMonth - $presentDays;

            // Get unpaid leave days
            $unpaidLeaveDays = $leaveModel->getUnpaidLeaveDays($employee['id'], $month, $year, $tenantId);

            // Calculate salary components
            $basicSalary = (float)$employee['basic_salary'];
            $housingAllowance = (float)$employee['housing_allowance'];
            $transportAllowance = (float)$employee['transport_allowance'];
            $otherAllowances = (float)$employee['other_allowances'];

            // Overtime calculation
            $overtimeAmount = $overtimeHours * $overtimeRate;

            // Gross salary
            $grossSalary = $basicSalary + $housingAllowance + $transportAllowance + $otherAllowances + $overtimeAmount;

            // Deductions
            $socialSecurityDeduction = (float)$employee['social_security_deduction'];

            // Loan deduction
            $loanDeduction = 0;
            $activeLoans = $loanModel->getActiveLoans($employee['id'], $tenantId);
            foreach ($activeLoans as $loan) {
                if ($loan['paid_installments'] < $loan['total_installments']) {
                    $loanDeduction += (float)$loan['installment_amount'];
                }
            }

            // Unpaid leave deduction (pro-rated salary)
            $unpaidLeaveDeduction = 0;
            if ($unpaidLeaveDays > 0) {
                $dailySalary = $basicSalary / $workingDaysInMonth;
                $unpaidLeaveDeduction = $dailySalary * $unpaidLeaveDays;
            }

            $totalDeductionsEmp = $socialSecurityDeduction + $loanDeduction + $unpaidLeaveDeduction;

            // Net salary
            $netSalary = $grossSalary - $totalDeductionsEmp;

            // Insert payroll item
            $payrollItemModel->insert([
                'payroll_run_id' => $payrollRunId,
                'employee_id' => $employee['id'],
                'basic_salary' => $basicSalary,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'other_allowances' => $otherAllowances,
                'overtime_amount' => $overtimeAmount,
                'gross_salary' => $grossSalary,
                'social_security_deduction' => $socialSecurityDeduction,
                'loan_deduction' => $loanDeduction,
                'unpaid_leave_deduction' => $unpaidLeaveDeduction,
                'other_deductions' => 0,
                'total_deductions' => $totalDeductionsEmp,
                'net_salary' => $netSalary,
                'working_days' => $workingDaysInMonth,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'overtime_hours' => $overtimeHours
            ]);

            $totalGross += $grossSalary;
            $totalDeductions += $totalDeductionsEmp;
            $totalNet += $netSalary;
        }

        // Update payroll run totals
        $payrollModel->update($payrollRunId, [
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
            'processed_at' => date('Y-m-d H:i:s')
        ]);

        $this->flash('success', 'Payroll processed successfully.');
        $this->redirect('/payroll/' . $payrollRunId);
    }

    /**
     * Finalize payroll run
     */
    public function finalize($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $tenantId = $this->tenantId();
        $payrollModel = $this->model('PayrollRun');

        $payrollRun = $payrollModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$payrollRun) {
            $this->json(['success' => false, 'message' => 'Payroll run not found'], 404);
        }

        if ($payrollModel->update($id, ['status' => 'finalized'])) {
            $this->flash('success', 'Payroll run finalized successfully.');
            $this->redirect('/payroll/' . $id);
        } else {
            $this->flash('error', 'Failed to finalize payroll run.');
            $this->redirect('/payroll/' . $id);
        }
    }

    /**
     * Show payslip
     */
    public function payslip($id) {
        $this->requireAuth();
        $user = $this->user();

        $payrollItemModel = $this->model('PayrollItem');
        $payslip = $payrollItemModel->getPayrollItemWithDetails($id);

        if (!$payslip) {
            $this->flash('error', 'Payslip not found.');
            $this->redirect('/dashboard');
        }

        // Check authorization
        if ($user['role'] === 'employee' && $payslip['employee_id'] != $user['employee_id']) {
            $this->flash('error', 'Unauthorized access.');
            $this->redirect('/dashboard');
        }

        $data = ['payslip' => $payslip];
        $this->view->render('payroll/payslip', $data, null);
    }
}
