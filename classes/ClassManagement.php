<?php
/**
 * Class Class for class management
 */

class ClassManagement {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Create class
     */
    public function createClass($class_name, $class_code, $teacher_id, $semester, $academic_year, $schedule, $room_number) {
        try {
            // Check if class code already exists
            $this->db->prepare('SELECT id FROM classes WHERE class_code = ?');
            $this->db->bind('s', $class_code);
            $this->db->execute();

            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'Class code already exists'];
            }

            $this->db->prepare('INSERT INTO classes (class_name, class_code, teacher_id, semester, academic_year, schedule, room_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?)');
            $this->db->bind('s', $class_name);
            $this->db->bind('s', $class_code);
            $this->db->bind('i', $teacher_id);
            $this->db->bind('s', $semester);
            $this->db->bind('s', $academic_year);
            $this->db->bind('s', $schedule);
            $this->db->bind('s', $room_number);
            $this->db->execute();

            return ['success' => true, 'message' => 'Class created successfully', 'class_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get class by ID
     */
    public function getClassById($id) {
        try {
            $this->db->prepare('SELECT c.*, t.id as teacher_id, u.full_name as teacher_name FROM classes c 
            JOIN teachers t ON c.teacher_id = t.id 
            JOIN users u ON t.user_id = u.id WHERE c.id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all classes
     */
    public function getAllClasses($teacher_id = null, $academic_year = null, $limit = null, $offset = 0) {
        try {
            $query = 'SELECT c.*, t.id as teacher_id, u.full_name as teacher_name FROM classes c 
            JOIN teachers t ON c.teacher_id = t.id 
            JOIN users u ON t.user_id = u.id WHERE 1=1';

            if ($teacher_id) {
                $query .= ' AND c.teacher_id = ' . $teacher_id;
            }
            if ($academic_year) {
                $query .= ' AND c.academic_year = "' . $academic_year . '"';
            }

            $query .= ' ORDER BY c.class_name';

            if ($limit) {
                $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
            }

            $this->db->prepare($query);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get class students
     */
    public function getClassStudents($class_id, $status = 'active') {
        try {
            $this->db->prepare('SELECT s.*, u.full_name, u.email FROM students s 
            JOIN class_enrollment ce ON s.id = ce.student_id 
            JOIN users u ON s.user_id = u.id 
            WHERE ce.class_id = ? AND ce.status = ? ORDER BY u.full_name');
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $status);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update class
     */
    public function updateClass($id, $class_name, $semester, $schedule, $room_number) {
        try {
            $this->db->prepare('UPDATE classes SET class_name = ?, semester = ?, schedule = ?, room_number = ? WHERE id = ?');
            $this->db->bind('s', $class_name);
            $this->db->bind('s', $semester);
            $this->db->bind('s', $schedule);
            $this->db->bind('s', $room_number);
            $this->db->bind('i', $id);
            $this->db->execute();

            return ['success' => true, 'message' => 'Class updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get class statistics
     */
    public function getClassStats($class_id) {
        try {
            $stats = [];

            // Total students
            $this->db->prepare('SELECT COUNT(*) as count FROM class_enrollment WHERE class_id = ? AND status = "active"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['total_students'] = $this->db->getSingleResult()['count'] ?? 0;

            // Today's present
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance WHERE class_id = ? AND attendance_date = CURDATE() AND status = "present"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['today_present'] = $this->db->getSingleResult()['count'] ?? 0;

            // Today's absent
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance WHERE class_id = ? AND attendance_date = CURDATE() AND status = "absent"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['today_absent'] = $this->db->getSingleResult()['count'] ?? 0;

            // Today's late
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance WHERE class_id = ? AND attendance_date = CURDATE() AND status = "late"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['today_late'] = $this->db->getSingleResult()['count'] ?? 0;

            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search classes
     */
    public function searchClasses($search_term, $limit = 20) {
        try {
            $search_term = '%' . $search_term . '%';
            $this->db->prepare('SELECT c.*, t.id as teacher_id, u.full_name as teacher_name FROM classes c 
            JOIN teachers t ON c.teacher_id = t.id 
            JOIN users u ON t.user_id = u.id 
            WHERE c.class_name LIKE ? OR c.class_code LIKE ? LIMIT ?');
            $this->db->bind('s', $search_term);
            $this->db->bind('s', $search_term);
            $this->db->bind('i', $limit);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>