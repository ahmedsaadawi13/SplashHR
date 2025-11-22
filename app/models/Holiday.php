<?php
// FILE: /app/models/Holiday.php

/**
 * Holiday Model
 * Handles public holiday data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Holiday extends Model {

    protected $table = 'holidays';

    /**
     * Get holidays for a tenant
     * @param int $tenantId
     * @param int $year
     * @return array
     */
    public function getHolidaysByTenant($tenantId, $year = null) {
        $sql = "SELECT * FROM holidays WHERE tenant_id = :tenant_id AND status = 'active'";
        $params = [':tenant_id' => $tenantId];

        if ($year) {
            $sql .= " AND YEAR(date) = :year";
            $params[':year'] = $year;
        }

        $sql .= " ORDER BY date ASC";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Check if date is a holiday
     * @param string $date
     * @param int $tenantId
     * @return bool
     */
    public function isHoliday($date, $tenantId) {
        $count = $this->count([
            'tenant_id' => $tenantId,
            'date' => $date,
            'status' => 'active'
        ]);

        return $count > 0;
    }
}
