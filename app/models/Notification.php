<?php
// FILE: /app/models/Notification.php

/**
 * Notification Model
 * Handles user notification data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Notification extends Model {

    protected $table = 'notifications';

    /**
     * Get notifications for a user
     * @param int $userId
     * @param bool $unreadOnly
     * @param int $limit
     * @return array
     */
    public function getUserNotifications($userId, $unreadOnly = false, $limit = 20) {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark notification as read
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function markAsRead($id, $userId) {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW()
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->query($sql, [
            ':id' => $id,
            ':user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Mark all notifications as read for user
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW()
                WHERE user_id = :user_id AND is_read = 0";

        $stmt = $this->query($sql, [':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get unread count for user
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId) {
        return $this->count(['user_id' => $userId, 'is_read' => 0]);
    }

    /**
     * Create notification (simulated email)
     * @param int $tenantId
     * @param int $userId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @return int
     */
    public function createNotification($tenantId, $userId, $type, $title, $message, $data = []) {
        return $this->insert([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => json_encode($data)
        ]);
    }
}
