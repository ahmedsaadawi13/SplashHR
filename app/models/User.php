<?php
// FILE: /app/models/User.php

/**
 * User Model
 * Handles user authentication and account operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class User extends Model {

    protected $table = 'users';

    /**
     * Get user with employee details
     * @param int $userId
     * @return array|false
     */
    public function getUserWithDetails($userId) {
        $sql = "SELECT u.*, e.employee_code, e.department_id, e.job_title_id,
                       t.company_name as tenant_name
                FROM users u
                LEFT JOIN employees e ON u.employee_id = e.id
                LEFT JOIN tenants t ON u.tenant_id = t.id
                WHERE u.id = :user_id
                LIMIT 1";

        $stmt = $this->query($sql, [':user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Get all users for a tenant
     * @param int $tenantId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUsersByTenant($tenantId, $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT u.*, e.employee_code, e.first_name as emp_first_name, e.last_name as emp_last_name
                FROM users u
                LEFT JOIN employees e ON u.employee_id = e.id
                WHERE u.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['role'])) {
            $sql .= " AND u.role = :role";
            $params[':role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND u.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";

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
     * Check if email exists
     * @param string $email
     * @param int $excludeId
     * @return bool
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
