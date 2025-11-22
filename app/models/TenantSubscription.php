<?php
// FILE: /app/models/TenantSubscription.php

/**
 * Tenant Subscription Model
 * Handles tenant subscription data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class TenantSubscription extends Model {

    protected $table = 'tenant_subscriptions';

    /**
     * Get active subscription for tenant
     * @param int $tenantId
     * @return array|false
     */
    public function getActiveSubscription($tenantId) {
        $sql = "SELECT ts.*, sp.name as plan_name, sp.max_employees, sp.max_departments,
                       sp.max_monthly_payroll_runs, sp.max_storage_mb
                FROM tenant_subscriptions ts
                INNER JOIN subscription_plans sp ON ts.plan_id = sp.id
                WHERE ts.tenant_id = :tenant_id
                AND ts.status IN ('trialing', 'active')
                ORDER BY ts.created_at DESC
                LIMIT 1";

        $stmt = $this->query($sql, [':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Check if tenant has reached employee limit
     * @param int $tenantId
     * @return bool
     */
    public function hasReachedEmployeeLimit($tenantId) {
        $subscription = $this->getActiveSubscription($tenantId);

        if (!$subscription || $subscription['max_employees'] === null) {
            return false; // No limit
        }

        $employeeModel = new Employee();
        $employeeCount = $employeeModel->count(['tenant_id' => $tenantId, 'status' => 'active']);

        return $employeeCount >= $subscription['max_employees'];
    }

    /**
     * Check if tenant has reached department limit
     * @param int $tenantId
     * @return bool
     */
    public function hasReachedDepartmentLimit($tenantId) {
        $subscription = $this->getActiveSubscription($tenantId);

        if (!$subscription || $subscription['max_departments'] === null) {
            return false; // No limit
        }

        $departmentModel = new Department();
        $departmentCount = $departmentModel->count(['tenant_id' => $tenantId, 'status' => 'active']);

        return $departmentCount >= $subscription['max_departments'];
    }
}
