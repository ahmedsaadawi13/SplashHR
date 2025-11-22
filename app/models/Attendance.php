<?php
// FILE: /app/models/Attendance.php

/**
 * Attendance Model
 * Handles employee attendance data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Attendance extends Model {

    protected $table = 'attendance';

    /**
     * Get attendance records for a tenant with employee details
     * @param int $tenantId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAttendanceByTenant($tenantId, $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT a.*,
                       e.employee_code, e.first_name, e.last_name,
                       d.name as department_name
                FROM attendance a
                INNER JOIN employees e ON a.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE a.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND a.employee_id = :employee_id";
            $params[':employee_id'] = $filters['employee_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :department_id";
            $params[':department_id'] = $filters['department_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND a.date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND a.date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['month']) && !empty($filters['year'])) {
            $sql .= " AND MONTH(a.date) = :month AND YEAR(a.date) = :year";
            $params[':month'] = $filters['month'];
            $params[':year'] = $filters['year'];
        }

        $sql .= " ORDER BY a.date DESC, e.first_name LIMIT :limit OFFSET :offset";

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
     * Count attendance records
     * @param int $tenantId
     * @param array $filters
     * @return int
     */
    public function countByTenant($tenantId, $filters = []) {
        $sql = "SELECT COUNT(*) as count
                FROM attendance a
                INNER JOIN employees e ON a.employee_id = e.id
                WHERE a.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND a.employee_id = :employee_id";
            $params[':employee_id'] = $filters['employee_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :department_id";
            $params[':department_id'] = $filters['department_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND a.date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND a.date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['month']) && !empty($filters['year'])) {
            $sql .= " AND MONTH(a.date) = :month AND YEAR(a.date) = :year";
            $params[':month'] = $filters['month'];
            $params[':year'] = $filters['year'];
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get employee attendance for a month
     * @param int $employeeId
     * @param int $month
     * @param int $year
     * @param int $tenantId
     * @return array
     */
    public function getEmployeeMonthAttendance($employeeId, $month, $year, $tenantId) {
        $sql = "SELECT * FROM attendance
                WHERE employee_id = :employee_id
                AND tenant_id = :tenant_id
                AND MONTH(date) = :month
                AND YEAR(date) = :year
                ORDER BY date";

        $stmt = $this->query($sql, [
            ':employee_id' => $employeeId,
            ':tenant_id' => $tenantId,
            ':month' => $month,
            ':year' => $year
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Check if attendance exists for employee on date
     * @param int $employeeId
     * @param string $date
     * @param int $tenantId
     * @return bool
     */
    public function attendanceExists($employeeId, $date, $tenantId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM attendance
                WHERE employee_id = :employee_id
                AND date = :date
                AND tenant_id = :tenant_id";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);

        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Calculate total hours from check-in and check-out
     * @param string $checkIn
     * @param string $checkOut
     * @return float
     */
    public function calculateTotalHours($checkIn, $checkOut) {
        if (empty($checkIn) || empty($checkOut)) {
            return 0;
        }

        $start = new DateTime($checkIn);
        $end = new DateTime($checkOut);
        $diff = $start->diff($end);

        return $diff->h + ($diff->i / 60);
    }

    /**
     * Get attendance summary for dashboard
     * @param int $tenantId
     * @param string $month
     * @param string $year
     * @return array
     */
    public function getAttendanceSummary($tenantId, $month = null, $year = null) {
        if (!$month) $month = date('m');
        if (!$year) $year = date('Y');

        $sql = "SELECT
                COUNT(DISTINCT CASE WHEN is_late = 1 THEN id END) as late_arrivals,
                COUNT(DISTINCT CASE WHEN is_absent = 1 THEN id END) as absences,
                COUNT(DISTINCT CASE WHEN is_early_leave = 1 THEN id END) as early_leaves,
                SUM(overtime_hours) as total_overtime_hours
                FROM attendance
                WHERE tenant_id = :tenant_id
                AND MONTH(date) = :month
                AND YEAR(date) = :year";

        $stmt = $this->query($sql, [
            ':tenant_id' => $tenantId,
            ':month' => $month,
            ':year' => $year
        ]);
        return $stmt->fetch();
    }
}
