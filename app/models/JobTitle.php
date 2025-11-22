<?php
// FILE: /app/models/JobTitle.php

/**
 * Job Title Model
 * Handles job title data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class JobTitle extends Model {

    protected $table = 'job_titles';

    /**
     * Get all job titles for a tenant
     * @param int $tenantId
     * @param string $status
     * @return array
     */
    public function getByTenant($tenantId, $status = 'active') {
        $sql = "SELECT jt.*, COUNT(DISTINCT e.id) as employee_count
                FROM job_titles jt
                LEFT JOIN employees e ON jt.id = e.job_title_id AND e.status = 'active'
                WHERE jt.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if ($status) {
            $sql .= " AND jt.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " GROUP BY jt.id ORDER BY jt.title";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
}
