<?php
// FILE: /app/models/Department.php

/**
 * Department Model
 * Handles department data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Department extends Model {

    protected $table = 'departments';

    /**
     * Get department with manager details
     * @param int $departmentId
     * @param int $tenantId
     * @return array|false
     */
    public function getDepartmentWithDetails($departmentId, $tenantId) {
        $sql = "SELECT d.*,
                       CONCAT(e.first_name, ' ', e.last_name) as manager_name,
                       COUNT(DISTINCT emp.id) as employee_count
                FROM departments d
                LEFT JOIN employees e ON d.manager_id = e.id
                LEFT JOIN employees emp ON d.id = emp.department_id AND emp.status = 'active'
                WHERE d.id = :department_id AND d.tenant_id = :tenant_id
                GROUP BY d.id
                LIMIT 1";

        $stmt = $this->query($sql, [
            ':department_id' => $departmentId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch();
    }

    /**
     * Get all departments for a tenant
     * @param int $tenantId
     * @param string $status
     * @return array
     */
    public function getByTenant($tenantId, $status = 'active') {
        $sql = "SELECT d.*,
                       CONCAT(e.first_name, ' ', e.last_name) as manager_name,
                       COUNT(DISTINCT emp.id) as employee_count
                FROM departments d
                LEFT JOIN employees e ON d.manager_id = e.id
                LEFT JOIN employees emp ON d.id = emp.department_id AND emp.status = 'active'
                WHERE d.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if ($status) {
            $sql .= " AND d.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " GROUP BY d.id ORDER BY d.name";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Check if department code exists for tenant
     * @param string $code
     * @param int $tenantId
     * @param int $excludeId
     * @return bool
     */
    public function codeExists($code, $tenantId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM departments WHERE code = :code AND tenant_id = :tenant_id";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $code);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
