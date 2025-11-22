<?php
// FILE: /app/models/LeaveRequest.php

/**
 * Leave Request Model
 * Handles leave request data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class LeaveRequest extends Model {

    protected $table = 'leave_requests';

    /**
     * Get leave requests with employee and leave type details
     * @param int $tenantId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLeaveRequestsByTenant($tenantId, $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT lr.*,
                       e.employee_code, e.first_name, e.last_name,
                       lt.name as leave_type_name,
                       CONCAT(approver.first_name, ' ', approver.last_name) as approver_name
                FROM leave_requests lr
                INNER JOIN employees e ON lr.employee_id = e.id
                INNER JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN users approver ON lr.approved_by = approver.id
                WHERE lr.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND lr.employee_id = :employee_id";
            $params[':employee_id'] = $filters['employee_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND lr.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['leave_type_id'])) {
            $sql .= " AND lr.leave_type_id = :leave_type_id";
            $params[':leave_type_id'] = $filters['leave_type_id'];
        }

        $sql .= " ORDER BY lr.created_at DESC LIMIT :limit OFFSET :offset";

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
     * Count leave requests
     * @param int $tenantId
     * @param array $filters
     * @return int
     */
    public function countByTenant($tenantId, $filters = []) {
        $sql = "SELECT COUNT(*) as count FROM leave_requests WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND employee_id = :employee_id";
            $params[':employee_id'] = $filters['employee_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['leave_type_id'])) {
            $sql .= " AND leave_type_id = :leave_type_id";
            $params[':leave_type_id'] = $filters['leave_type_id'];
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get leave request with details
     * @param int $id
     * @param int $tenantId
     * @return array|false
     */
    public function getLeaveRequestWithDetails($id, $tenantId) {
        $sql = "SELECT lr.*,
                       e.employee_code, e.first_name, e.last_name, e.email,
                       lt.name as leave_type_name, lt.is_paid,
                       CONCAT(approver.first_name, ' ', approver.last_name) as approver_name
                FROM leave_requests lr
                INNER JOIN employees e ON lr.employee_id = e.id
                INNER JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN users approver ON lr.approved_by = approver.id
                WHERE lr.id = :id AND lr.tenant_id = :tenant_id
                LIMIT 1";

        $stmt = $this->query($sql, [
            ':id' => $id,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch();
    }

    /**
     * Get approved unpaid leave days for employee in a month
     * @param int $employeeId
     * @param int $month
     * @param int $year
     * @param int $tenantId
     * @return int
     */
    public function getUnpaidLeaveDays($employeeId, $month, $year, $tenantId) {
        $sql = "SELECT COALESCE(SUM(lr.total_days), 0) as unpaid_days
                FROM leave_requests lr
                INNER JOIN leave_types lt ON lr.leave_type_id = lt.id
                WHERE lr.employee_id = :employee_id
                AND lr.tenant_id = :tenant_id
                AND lr.status = 'approved'
                AND lt.is_paid = 0
                AND ((MONTH(lr.start_date) = :month AND YEAR(lr.start_date) = :year)
                     OR (MONTH(lr.end_date) = :month AND YEAR(lr.end_date) = :year))";

        $stmt = $this->query($sql, [
            ':employee_id' => $employeeId,
            ':tenant_id' => $tenantId,
            ':month' => $month,
            ':year' => $year
        ]);
        $result = $stmt->fetch();
        return (int)$result['unpaid_days'];
    }
}
