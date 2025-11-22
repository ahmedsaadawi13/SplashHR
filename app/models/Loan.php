<?php
// FILE: /app/models/Loan.php

/**
 * Loan Model
 * Handles employee loan data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Loan extends Model {

    protected $table = 'loans';

    /**
     * Get loans for a tenant with employee details
     * @param int $tenantId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLoansByTenant($tenantId, $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT l.*,
                       e.employee_code, e.first_name, e.last_name
                FROM loans l
                INNER JOIN employees e ON l.employee_id = e.id
                WHERE l.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND l.employee_id = :employee_id";
            $params[':employee_id'] = $filters['employee_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get active loans for employee
     * @param int $employeeId
     * @param int $tenantId
     * @return array
     */
    public function getActiveLoans($employeeId, $tenantId) {
        $sql = "SELECT * FROM loans
                WHERE employee_id = :employee_id
                AND tenant_id = :tenant_id
                AND status = 'active'
                ORDER BY created_at";

        $stmt = $this->query($sql, [
            ':employee_id' => $employeeId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Get loan with employee details
     * @param int $id
     * @param int $tenantId
     * @return array|false
     */
    public function getLoanWithDetails($id, $tenantId) {
        $sql = "SELECT l.*,
                       e.employee_code, e.first_name, e.last_name
                FROM loans l
                INNER JOIN employees e ON l.employee_id = e.id
                WHERE l.id = :id AND l.tenant_id = :tenant_id
                LIMIT 1";

        $stmt = $this->query($sql, [
            ':id' => $id,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch();
    }
}
