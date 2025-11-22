<?php
// FILE: /app/models/Document.php

/**
 * Document Model
 * Handles document/file upload data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Document extends Model {

    protected $table = 'documents';

    /**
     * Get documents for an employee
     * @param int $employeeId
     * @param int $tenantId
     * @return array
     */
    public function getEmployeeDocuments($employeeId, $tenantId) {
        $sql = "SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                FROM documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.employee_id = :employee_id AND d.tenant_id = :tenant_id
                ORDER BY d.created_at DESC";

        $stmt = $this->query($sql, [
            ':employee_id' => $employeeId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Get all documents for a tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getDocumentsByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT d.*,
                       e.employee_code, e.first_name, e.last_name,
                       CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                FROM documents d
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.tenant_id = :tenant_id
                ORDER BY d.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
