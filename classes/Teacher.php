<?php
/**
 * Teacher Class for teacher management
 */

class Teacher {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Create teacher profile
     */
    public function createTeacher($user_id, $teacher_id, $department, $subject_specialization, $phone, $office_location) {
        try {
            $this->db->prepare('INSERT INTO teachers (user_id, teacher_id, department, subject_specialization, phone, office_location, hire_date) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $this->db->bind('i', $user_id);
            $this->db->bind('s', $teacher_id);
            $this->db->bind('s', $department);
            $this->db->bind('s', $subject_specialization);
            $this->db->bind('s', $phone);
            $this->db->bind('s', $office_location);
            $this->db->execute();

            return ['success' => true, 'message' => 'Teacher profile created successfully', 'teacher_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get teacher by user ID
     */
    public function getTeacherByUserId($user_id) {
        try {
            $this->db->prepare('SELECT t.*, u.full_name, u.email, u.username FROM teachers t 
            JOIN users u ON t.user_id = u.id WHERE t.user_id = ?');
            $this->db->bind('i', $user_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get teacher by teacher ID
     */
    public function getTeacherByTeacherId($teacher_id) {
        try {
            $this->db->prepare('SELECT t.*, u.full_name, u.email, u.username FROM teachers t 
            JOIN users u ON t.user_id = u.id WHERE t.teacher_id = ?');
            $this->db->bind('s', $teacher_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get teacher by ID
     */
    public function getTeacherById($id) {
        try {
            $this->db->prepare('SELECT t.*, u.full_name, u.email, u.username FROM teachers t 
            JOIN users u ON t.user_id = u.id WHERE t.id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all teachers
     */
    public function getAllTeachers($department = null, $status = null, $limit = null, $offset = 0) {
        try {
            $query = 'SELECT t.*, u.full_name, u.email, u.status FROM teachers t 
            JOIN users u ON t.user_id = u.id WHERE 1=1';

            if ($department) {
                $query .= ' AND t.department = "' . $department . '"';
            }
            if ($status) {
                $query .= ' AND u.status = "' . $status . '"';
            }

            $query .= ' ORDER BY t.created_at DESC';

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
     * Search teachers
     */
    public function searchTeachers($search_term, $limit = 20) {
        try {
            $search_term = '%' . $search_term . '%';
            $this->db->prepare('SELECT t.*, u.full_name, u.email FROM teachers t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.teacher_id LIKE ? OR u.full_name LIKE ? OR t.subject_specialization LIKE ?
            LIMIT ?');
            $this->db->bind('s', $search_term);
            $this->db->bind('s', $search_term);
            $this->db->bind('s', $search_term);
            $this->db->bind('i', $limit);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update teacher
     */
    public function updateTeacher($id, $department, $subject_specialization, $phone, $office_location) {
        try {
            $this->db->prepare('UPDATE teachers SET department = ?, subject_specialization = ?, phone = ?, office_location = ? WHERE id = ?');
            $this->db->bind('s', $department);
            $this->db->bind('s', $subject_specialization);
            $this->db->bind('s', $phone);
            $this->db->bind('s', $office_location);
            $this->db->bind('i', $id);
            $this->db->execute();

            return ['success' => true, 'message' => 'Teacher updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get classes taught by teacher
     */
    public function getTeacherClasses($teacher_id) {
        try {
            $this->db->prepare('SELECT * FROM classes WHERE teacher_id = ? ORDER BY class_name');
            $this->db->bind('i', $teacher_id);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get teacher statistics
     */
    public function getTeacherStats($teacher_id) {
        try {
            $stats = [];

            // Total classes
            $this->db->prepare('SELECT COUNT(*) as count FROM classes WHERE teacher_id = ?');
            $this->db->bind('i', $teacher_id);
            $this->db->execute();
            $stats['total_classes'] = $this->db->getSingleResult()['count'] ?? 0;

            // Total students
            $this->db->prepare('SELECT COUNT(DISTINCT ce.student_id) as count FROM class_enrollment ce 
            JOIN classes c ON ce.class_id = c.id WHERE c.teacher_id = ? AND ce.status = "active"');
            $this->db->bind('i', $teacher_id);
            $this->db->execute();
            $stats['total_students'] = $this->db->getSingleResult()['count'] ?? 0;

            // Today's attendance
            $this->db->prepare('SELECT COUNT(*) as count FROM attendance a 
            JOIN classes c ON a.class_id = c.id 
            WHERE c.teacher_id = ? AND DATE(a.attendance_date) = CURDATE()');
            $this->db->bind('i', $teacher_id);
            $this->db->execute();
            $stats['today_attendance'] = $this->db->getSingleResult()['count'] ?? 0;

            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get teacher count
     */
    public function getTeacherCount($department = null) {
        try {
            $query = 'SELECT COUNT(*) as count FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.status = "active"';
            if ($department) {
                $query .= ' AND t.department = "' . $department . '"';
            }
            $this->db->prepare($query);
            $this->db->execute();
            $result = $this->db->getSingleResult();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>