<?php
/**
 * Notification Class for handling notifications
 */

class Notification {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Create notification
     */
    public function createNotification($user_id, $notification_type, $subject, $message, $student_id = null) {
        try {
            $this->db->prepare('INSERT INTO notifications (user_id, student_id, notification_type, subject, message, is_read) 
            VALUES (?, ?, ?, ?, ?, 0)');
            $this->db->bind('i', $user_id);
            $this->db->bind('i', $student_id);
            $this->db->bind('s', $notification_type);
            $this->db->bind('s', $subject);
            $this->db->bind('s', $message);
            $this->db->execute();

            return ['success' => true, 'message' => 'Notification created', 'notification_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send attendance notification
     */
    public function sendAttendanceNotification($student_id, $status, $class_id) {
        try {
            $student = $this->getStudentInfo($student_id);
            if (!$student) {
                return ['success' => false, 'message' => 'Student not found'];
            }

            $class = $this->getClassInfo($class_id);
            if (!$class) {
                return ['success' => false, 'message' => 'Class not found'];
            }

            $subject = 'Attendance Notification - ' . $class['class_name'];
            $message = 'Your attendance for ' . $class['class_name'] . ' has been marked as ' . ucfirst($status) . '.';

            return $this->createNotification($student['user_id'], 'attendance', $subject, $message, $student_id);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send absence notification
     */
    public function sendAbsenceNotification($student_id, $class_id) {
        try {
            $student = $this->getStudentInfo($student_id);
            if (!$student) {
                return ['success' => false, 'message' => 'Student not found'];
            }

            $class = $this->getClassInfo($class_id);

            $subject = 'Absence Alert - ' . $class['class_name'];
            $message = 'You have been marked absent in ' . $class['class_name'] . ' on ' . date('d M Y') . '.';

            return $this->createNotification($student['user_id'], 'absence', $subject, $message, $student_id);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send late notification
     */
    public function sendLateNotification($student_id, $class_id) {
        try {
            $student = $this->getStudentInfo($student_id);
            if (!$student) {
                return ['success' => false, 'message' => 'Student not found'];
            }

            $class = $this->getClassInfo($class_id);

            $subject = 'Late Attendance - ' . $class['class_name'];
            $message = 'You have marked as late in ' . $class['class_name'] . '.';

            return $this->createNotification($student['user_id'], 'late', $subject, $message, $student_id);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get student info helper
     */
    private function getStudentInfo($student_id) {
        try {
            $this->db->prepare('SELECT s.*, u.user_id FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?');
            $this->db->bind('i', $student_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get class info helper
     */
    private function getClassInfo($class_id) {
        try {
            $this->db->prepare('SELECT * FROM classes WHERE id = ?');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($user_id, $limit = 20, $offset = 0, $unread_only = false) {
        try {
            $query = 'SELECT * FROM notifications WHERE user_id = ?';
            if ($unread_only) {
                $query .= ' AND is_read = 0';
            }
            $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';

            $this->db->prepare($query);
            $this->db->bind('i', $user_id);
            $this->db->bind('i', $limit);
            $this->db->bind('i', $offset);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notification_id) {
        try {
            $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?');
            $this->db->bind('i', $notification_id);
            $this->db->execute();
            return ['success' => true, 'message' => 'Notification marked as read'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead($user_id) {
        try {
            $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
            $this->db->bind('i', $user_id);
            $this->db->execute();
            return ['success' => true, 'message' => 'All notifications marked as read'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($user_id) {
        try {
            $this->db->prepare('SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0');
            $this->db->bind('i', $user_id);
            $this->db->execute();
            $result = $this->db->getSingleResult();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notification_id) {
        try {
            $this->db->prepare('DELETE FROM notifications WHERE id = ?');
            $this->db->bind('i', $notification_id);
            $this->db->execute();
            return ['success' => true, 'message' => 'Notification deleted'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>