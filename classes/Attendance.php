<?php
/**
 * Attendance Class for attendance tracking
 */

class Attendance {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Record attendance (Time In)
     */
    public function recordTimeIn($student_id, $class_id, $qr_code_id, $late_threshold = 10) {
        try {
            $attendance_date = date('Y-m-d');
            $time_in = date('Y-m-d H:i:s');

            // Check if already marked present today
            $this->db->prepare('SELECT id FROM attendance WHERE student_id = ? AND class_id = ? AND attendance_date = ?');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $attendance_date);
            $this->db->execute();

            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'Attendance already recorded for today'];
            }

            // Get class schedule to check if late
            $class_info = $this->getClassSchedule($class_id);
            $status = 'present';

            if ($class_info && $class_info['start_time']) {
                $start_time = strtotime($attendance_date . ' ' . $class_info['start_time']);
                $current_time = strtotime($time_in);
                $time_diff = ($current_time - $start_time) / 60; // in minutes

                if ($time_diff > $late_threshold) {
                    $status = 'late';
                }
            }

            // Insert attendance record
            $this->db->prepare('INSERT INTO attendance (student_id, class_id, qr_code_id, attendance_date, time_in, status) 
            VALUES (?, ?, ?, ?, ?, ?)');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('i', $qr_code_id);
            $this->db->bind('s', $attendance_date);
            $this->db->bind('s', $time_in);
            $this->db->bind('s', $status);
            $this->db->execute();

            return [
                'success' => true,
                'message' => $status === 'late' ? 'Marked as late' : 'Attendance recorded successfully',
                'attendance_id' => $this->db->lastInsertId(),
                'status' => $status
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Record time out
     */
    public function recordTimeOut($student_id, $class_id, $attendance_date = null) {
        try {
            if (!$attendance_date) {
                $attendance_date = date('Y-m-d');
            }

            $time_out = date('Y-m-d H:i:s');

            $this->db->prepare('UPDATE attendance SET time_out = ? WHERE student_id = ? AND class_id = ? AND attendance_date = ?');
            $this->db->bind('s', $time_out);
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $attendance_date);
            $this->db->execute();

            if ($this->db->affectedRows() > 0) {
                return ['success' => true, 'message' => 'Time out recorded successfully'];
            } else {
                return ['success' => false, 'message' => 'Attendance record not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get class schedule
     */
    private function getClassSchedule($class_id) {
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
     * Get attendance record
     */
    public function getAttendanceRecord($id) {
        try {
            $this->db->prepare('SELECT a.*, s.student_id, u.full_name, c.class_name FROM attendance a 
            JOIN students s ON a.student_id = s.id 
            JOIN users u ON s.user_id = u.id 
            JOIN classes c ON a.class_id = c.id 
            WHERE a.id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get student attendance for date range
     */
    public function getStudentAttendance($student_id, $class_id, $start_date, $end_date) {
        try {
            $this->db->prepare('SELECT * FROM attendance WHERE student_id = ? AND class_id = ? 
            AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date DESC');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $start_date);
            $this->db->bind('s', $end_date);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get class attendance for date
     */
    public function getClassAttendanceForDate($class_id, $attendance_date) {
        try {
            $this->db->prepare('SELECT a.*, s.student_id, u.full_name FROM attendance a 
            JOIN students s ON a.student_id = s.id 
            JOIN users u ON s.user_id = u.id 
            WHERE a.class_id = ? AND a.attendance_date = ? ORDER BY u.full_name');
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $attendance_date);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update attendance status
     */
    public function updateAttendanceStatus($id, $status, $remarks = null) {
        try {
            $this->db->prepare('UPDATE attendance SET status = ?, remarks = ? WHERE id = ?');
            $this->db->bind('s', $status);
            $this->db->bind('s', $remarks);
            $this->db->bind('i', $id);
            $this->db->execute();
            return ['success' => true, 'message' => 'Attendance updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get attendance statistics
     */
    public function getAttendanceStats($student_id, $class_id, $start_date, $end_date) {
        try {
            $stats = [];

            // Total days
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance WHERE student_id = ? AND class_id = ? 
            AND attendance_date BETWEEN ? AND ?');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $start_date);
            $this->db->bind('s', $end_date);
            $this->db->execute();
            $stats['total_days'] = $this->db->getSingleResult()['count'] ?? 0;

            // Present days
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance WHERE student_id = ? AND class_id = ? 
            AND attendance_date BETWEEN ? AND ? AND status = "present"');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $start_date);
            $this->db->bind('s', $end_date);
            $this->db->execute();
            $stats['present_days'] = $this->db->getSingleResult()['count'] ?? 0;

            // Absent days
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance WHERE student_id = ? AND class_id = ? 
            AND attendance_date BETWEEN ? AND ? AND status = "absent"');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $start_date);
            $this->db->bind('s', $end_date);
            $this->db->execute();
            $stats['absent_days'] = $this->db->getSingleResult()['count'] ?? 0;

            // Late days
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance WHERE student_id = ? AND class_id = ? 
            AND attendance_date BETWEEN ? AND ? AND status = "late"');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $start_date);
            $this->db->bind('s', $end_date);
            $this->db->execute();
            $stats['late_days'] = $this->db->getSingleResult()['count'] ?? 0;

            // Calculate percentage
            if ($stats['total_days'] > 0) {
                $stats['attendance_percentage'] = round(($stats['present_days'] / $stats['total_days']) * 100, 2);
            } else {
                $stats['attendance_percentage'] = 0;
            }

            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
}
?>