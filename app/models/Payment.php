<?php
// FILE: /app/models/Payment.php

/**
 * Payment Model
 * Handles payment data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Payment extends Model {

    protected $table = 'payments';

    /**
     * Get payments for a tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPaymentsByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, i.invoice_number
                FROM payments p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = :tenant_id
                ORDER BY p.payment_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
