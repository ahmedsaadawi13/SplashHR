<?php
// FILE: /app/models/PayrollRun.php

/**
 * Payroll Run Model
 * Handles payroll run data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class PayrollRun extends Model {

    protected $table = 'payroll_runs';

    /**
     * Get payroll runs for a tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPayrollRunsByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT pr.*,
                       CONCAT(u.first_name, ' ', u.last_name) as processed_by_name,
                       COUNT(pi.id) as employee_count
                FROM payroll_runs pr
                LEFT JOIN users u ON pr.processed_by = u.id
                LEFT JOIN payroll_items pi ON pr.id = pi.payroll_run_id
                WHERE pr.tenant_id = :tenant_id
                GROUP BY pr.id
                ORDER BY pr.year DESC, pr.month DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get payroll run with details
     * @param int $id
     * @param int $tenantId
     * @return array|false
     */
    public function getPayrollRunWithDetails($id, $tenantId) {
        $sql = "SELECT pr.*,
                       CONCAT(u.first_name, ' ', u.last_name) as processed_by_name,
                       COUNT(pi.id) as employee_count
                FROM payroll_runs pr
                LEFT JOIN users u ON pr.processed_by = u.id
                LEFT JOIN payroll_items pi ON pr.id = pi.payroll_run_id
                WHERE pr.id = :id AND pr.tenant_id = :tenant_id
                GROUP BY pr.id
                LIMIT 1";

        $stmt = $this->query($sql, [
            ':id' => $id,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch();
    }

    /**
     * Check if payroll run exists for month/year
     * @param int $month
     * @param int $year
     * @param int $tenantId
     * @param int $excludeId
     * @return bool
     */
    public function payrollRunExists($month, $year, $tenantId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM payroll_runs
                WHERE month = :month AND year = :year AND tenant_id = :tenant_id";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':month', $month, PDO::PARAM_INT);
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get latest payroll run
     * @param int $tenantId
     * @return array|false
     */
    public function getLatestPayrollRun($tenantId) {
        $sql = "SELECT * FROM payroll_runs
                WHERE tenant_id = :tenant_id
                ORDER BY year DESC, month DESC
                LIMIT 1";

        $stmt = $this->query($sql, [':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }
}
