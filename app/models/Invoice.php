<?php
// FILE: /app/models/Invoice.php

/**
 * Invoice Model
 * Handles invoice data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Invoice extends Model {

    protected $table = 'invoices';

    /**
     * Get invoices for a tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getInvoicesByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT i.*, ts.billing_cycle, sp.name as plan_name
                FROM invoices i
                LEFT JOIN tenant_subscriptions ts ON i.subscription_id = ts.id
                LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
                WHERE i.tenant_id = :tenant_id
                ORDER BY i.invoice_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Generate unique invoice number
     * @return string
     */
    public function generateInvoiceNumber() {
        $year = date('Y');
        $sql = "SELECT COUNT(*) as count FROM invoices WHERE YEAR(invoice_date) = :year";
        $stmt = $this->query($sql, [':year' => $year]);
        $result = $stmt->fetch();
        $count = $result['count'] + 1;

        return 'INV-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
