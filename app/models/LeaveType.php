<?php
// FILE: /app/models/LeaveType.php

/**
 * Leave Type Model
 * Handles leave type data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class LeaveType extends Model {

    protected $table = 'leave_types';

    /**
     * Get all leave types for a tenant
     * @param int $tenantId
     * @param string $status
     * @return array
     */
    public function getByTenant($tenantId, $status = 'active') {
        $conditions = ['tenant_id' => $tenantId];

        if ($status) {
            $conditions['status'] = $status;
        }

        return $this->findAll($conditions, 'name ASC');
    }
}
