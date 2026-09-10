<?php
/**
 * Student Class for student management
 */

class Student {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Create student profile
     */
    public function createStudent($user_id, $student_id, $roll_number, $class, $section, $phone, $address, $guardian_name, $guardian_phone) {
        try {
            $this->db->prepare('INSERT INTO students (user_id, student_id, roll_number, class, section, phone, address, guardian_name, guardian_phone, enrollment_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $this->db->bind('i', $user_id);
            $this->db->bind('s', $student_id);
            $this->db->bind('s', $roll_number);
            $this->db->bind('s', $class);
            $this->db->bind('s', $section);
            $this->db->bind('s', $phone);
            $this->db->bind('s', $address);
            $this->db->bind('s', $guardian_name);
            $this->db->bind('s', $guardian_phone);
            $this->db->execute();

            return ['success' => true, 'message' => 'Student profile created successfully', 'student_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get student by user ID
     */
    public function getStudentByUserId($user_id) {
        try {
            $this->db->prepare('SELECT s.*, u.full_name, u.email, u.username FROM students s 
            JOIN users u ON s.user_id = u.id WHERE s.user_id = ?');
            $this->db->bind('i', $user_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get student by student ID
     */
    public function getStudentByStudentId($student_id) {
        try {
            $this->db->prepare('SELECT s.*, u.full_name, u.email, u.username FROM students s 
            JOIN users u ON s.user_id = u.id WHERE s.student_id = ?');
            $this->db->bind('s', $student_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get student by ID
     */
    public function getStudentById($id) {
        try {
            $this->db->prepare('SELECT s.*, u.full_name, u.email, u.username FROM students s 
            JOIN users u ON s.user_id = u.id WHERE s.id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all students with filters
     */
    public function getAllStudents($class = null, $section = null, $status = null, $limit = null, $offset = 0) {
        try {
            $query = 'SELECT s.*, u.full_name, u.email, u.status FROM students s 
            JOIN users u ON s.user_id = u.id WHERE 1=1';

            if ($class) {
                $query .= ' AND s.class = "' . $class . '"';
            }
            if ($section) {
                $query .= ' AND s.section = "' . $section . '"';
            }
            if ($status) {
                $query .= ' AND u.status = "' . $status . '"';
            }

            $query .= ' ORDER BY s.created_at DESC';

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
     * Search students
     */
    public function searchStudents($search_term, $limit = 20) {
        try {
            $search_term = '%' . $search_term . '%';
            $this->db->prepare('SELECT s.*, u.full_name, u.email FROM students s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.student_id LIKE ? OR u.full_name LIKE ? OR s.roll_number LIKE ?
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
     * Update student
     */
    public function updateStudent($id, $roll_number, $class, $section, $phone, $address, $guardian_name, $guardian_phone) {
        try {
            $this->db->prepare('UPDATE students SET roll_number = ?, class = ?, section = ?, phone = ?, address = ?, guardian_name = ?, guardian_phone = ? WHERE id = ?');
            $this->db->bind('s', $roll_number);
            $this->db->bind('s', $class);
            $this->db->bind('s', $section);
            $this->db->bind('s', $phone);
            $this->db->bind('s', $address);
            $this->db->bind('s', $guardian_name);
            $this->db->bind('s', $guardian_phone);
            $this->db->bind('i', $id);
            $this->db->execute();

            return ['success' => true, 'message' => 'Student updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get classes enrolled by student
     */
    public function getStudentClasses($student_id) {
        try {
            $this->db->prepare('SELECT c.*, t.id as teacher_id, u.full_name as teacher_name FROM classes c 
            JOIN class_enrollment ce ON c.id = ce.class_id 
            JOIN teachers t ON c.teacher_id = t.id 
            JOIN users u ON t.user_id = u.id 
            WHERE ce.student_id = ? AND ce.status = "active"
            ORDER BY c.class_name');
            $this->db->bind('i', $student_id);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Enroll student in class
     */
    public function enrollInClass($student_id, $class_id) {
        try {
            // Check if already enrolled
            $this->db->prepare('SELECT id FROM class_enrollment WHERE student_id = ? AND class_id = ?');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->execute();

            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'Student already enrolled in this class'];
            }

            $this->db->prepare('INSERT INTO class_enrollment (class_id, student_id, status) VALUES (?, ?, "active")');
            $this->db->bind('i', $class_id);
            $this->db->bind('i', $student_id);
            $this->db->execute();

            return ['success' => true, 'message' => 'Student enrolled successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Remove student from class
     */
    public function removeFromClass($student_id, $class_id) {
        try {
            $this->db->prepare('UPDATE class_enrollment SET status = "dropped" WHERE student_id = ? AND class_id = ?');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->execute();

            return ['success' => true, 'message' => 'Student removed from class'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get student count
     */
    public function getStudentCount($class = null, $section = null) {
        try {
            $query = 'SELECT COUNT(*) as count FROM students s JOIN users u ON s.user_id = u.id WHERE u.status = "active"';
            if ($class) {
                $query .= ' AND s.class = "' . $class . '"';
            }
            if ($section) {
                $query .= ' AND s.section = "' . $section . '"';
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