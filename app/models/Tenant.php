<?php
// FILE: /app/models/Tenant.php

/**
 * Tenant Model
 * Handles tenant (company) data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Tenant extends Model {

    protected $table = 'tenants';

    /**
     * Get tenant with subscription details
     * @param int $tenantId
     * @return array|false
     */
    public function getTenantWithSubscription($tenantId) {
        $sql = "SELECT t.*, sp.name as plan_name, ts.status as subscription_status,
                       ts.end_date as subscription_end_date, sp.max_employees, sp.max_departments
                FROM tenants t
                LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
                LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
                WHERE t.id = :tenant_id
                LIMIT 1";

        $stmt = $this->query($sql, [':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Get all tenants with subscription info
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllWithSubscriptions($limit = 20, $offset = 0) {
        $sql = "SELECT t.*, sp.name as plan_name, ts.status as subscription_status,
                       ts.end_date as subscription_end_date
                FROM tenants t
                LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
                LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->query($sql, [
            ':limit' => (int)$limit,
            ':offset' => (int)$offset
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Check if tenant slug exists
     * @param string $slug
     * @param int $excludeId
     * @return bool
     */
    public function slugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM tenants WHERE slug = :slug";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $slug);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
