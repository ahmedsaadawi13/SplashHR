<?php
// FILE: /app/models/Employee.php

/**
 * Employee Model
 * Handles employee data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Employee extends Model {

    protected $table = 'employees';

    /**
     * Get employee with department and job title details
     * @param int $employeeId
     * @param int $tenantId
     * @return array|false
     */
    public function getEmployeeWithDetails($employeeId, $tenantId) {
        $sql = "SELECT e.*, d.name as department_name, jt.title as job_title_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.id
                WHERE e.id = :employee_id AND e.tenant_id = :tenant_id
                LIMIT 1";

        $stmt = $this->query($sql, [
            ':employee_id' => $employeeId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch();
    }

    /**
     * Get all employees for a tenant with filters
     * @param int $tenantId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getEmployeesByTenant($tenantId, $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT e.*, d.name as department_name, jt.title as job_title_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.id
                WHERE e.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :department_id";
            $params[':department_id'] = $filters['department_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.first_name LIKE :search OR e.last_name LIKE :search OR e.employee_code LIKE :search OR e.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY e.first_name, e.last_name LIMIT :limit OFFSET :offset";

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
     * Count employees by tenant
     * @param int $tenantId
     * @param array $filters
     * @return int
     */
    public function countByTenant($tenantId, $filters = []) {
        $sql = "SELECT COUNT(*) as count FROM employees WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['department_id'])) {
            $sql .= " AND department_id = :department_id";
            $params[':department_id'] = $filters['department_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR employee_code LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Check if employee code exists for tenant
     * @param string $employeeCode
     * @param int $tenantId
     * @param int $excludeId
     * @return bool
     */
    public function employeeCodeExists($employeeCode, $tenantId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM employees WHERE employee_code = :code AND tenant_id = :tenant_id";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $employeeCode);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get employees by department
     * @param int $departmentId
     * @param int $tenantId
     * @return array
     */
    public function getByDepartment($departmentId, $tenantId) {
        $sql = "SELECT * FROM employees WHERE department_id = :department_id AND tenant_id = :tenant_id AND status = 'active'";

        $stmt = $this->query($sql, [
            ':department_id' => $departmentId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Get employees with upcoming birthdays
     * @param int $tenantId
     * @param int $days
     * @return array
     */
    public function getUpcomingBirthdays($tenantId, $days = 7) {
        $sql = "SELECT *,
                DAYOFYEAR(CONCAT(YEAR(CURDATE()), '-', MONTH(date_of_birth), '-', DAY(date_of_birth))) as birthday_day,
                DAYOFYEAR(CURDATE()) as today_day
                FROM employees
                WHERE tenant_id = :tenant_id AND status = 'active' AND date_of_birth IS NOT NULL
                HAVING (birthday_day BETWEEN today_day AND today_day + :days)
                    OR (birthday_day < today_day AND birthday_day + 365 BETWEEN today_day AND today_day + :days)
                ORDER BY birthday_day";

        $stmt = $this->query($sql, [
            ':tenant_id' => $tenantId,
            ':days' => $days
        ]);
        return $stmt->fetchAll();
    }
}
