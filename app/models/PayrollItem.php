<?php
// FILE: /app/models/PayrollItem.php

/**
 * Payroll Item Model
 * Handles individual employee payroll items
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class PayrollItem extends Model {

    protected $table = 'payroll_items';

    /**
     * Get payroll items for a payroll run with employee details
     * @param int $payrollRunId
     * @return array
     */
    public function getPayrollItemsByRun($payrollRunId) {
        $sql = "SELECT pi.*,
                       e.employee_code, e.first_name, e.last_name,
                       d.name as department_name,
                       jt.title as job_title_name
                FROM payroll_items pi
                INNER JOIN employees e ON pi.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.id
                WHERE pi.payroll_run_id = :payroll_run_id
                ORDER BY e.first_name, e.last_name";

        $stmt = $this->query($sql, [':payroll_run_id' => $payrollRunId]);
        return $stmt->fetchAll();
    }

    /**
     * Get payroll item with full details
     * @param int $id
     * @return array|false
     */
    public function getPayrollItemWithDetails($id) {
        $sql = "SELECT pi.*,
                       e.employee_code, e.first_name, e.last_name, e.bank_name, e.bank_account_number,
                       d.name as department_name,
                       jt.title as job_title_name,
                       pr.month, pr.year, pr.pay_date
                FROM payroll_items pi
                INNER JOIN employees e ON pi.employee_id = e.id
                INNER JOIN payroll_runs pr ON pi.payroll_run_id = pr.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.id
                WHERE pi.id = :id
                LIMIT 1";

        $stmt = $this->query($sql, [':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get employee payroll history
     * @param int $employeeId
     * @param int $limit
     * @return array
     */
    public function getEmployeePayrollHistory($employeeId, $limit = 12) {
        $sql = "SELECT pi.*, pr.month, pr.year, pr.pay_date, pr.status
                FROM payroll_items pi
                INNER JOIN payroll_runs pr ON pi.payroll_run_id = pr.id
                WHERE pi.employee_id = :employee_id
                ORDER BY pr.year DESC, pr.month DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
