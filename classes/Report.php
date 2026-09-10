<?php
/**
 * Report Class for generating attendance reports
 */

class Report {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Generate daily report for student
     */
    public function generateDailyReport($student_id, $class_id, $date) {
        try {
            $this->db->prepare('SELECT * FROM attendance WHERE student_id = ? AND class_id = ? AND attendance_date = ?');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $date);
            $this->db->execute();

            $attendance = $this->db->getSingleResult();

            return [
                'type' => 'daily',
                'student_id' => $student_id,
                'class_id' => $class_id,
                'date' => $date,
                'attendance' => $attendance,
                'status' => $attendance['status'] ?? 'absent'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generate weekly report for student
     */
    public function generateWeeklyReport($student_id, $class_id, $start_date = null, $end_date = null) {
        try {
            if (!$start_date) {
                $start_date = date('Y-m-d', strtotime('monday this week'));
            }
            if (!$end_date) {
                $end_date = date('Y-m-d', strtotime('sunday this week'));
            }

            $this->db->prepare('SELECT * FROM attendance WHERE student_id = ? AND class_id = ? 
            AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $start_date);
            $this->db->bind('s', $end_date);
            $this->db->execute();

            $attendance_records = $this->db->getResults();

            // Calculate statistics
            $stats = [
                'total_days' => count($attendance_records),
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];

            foreach ($attendance_records as $record) {
                $stats[$record['status']]++;
            }

            $percentage = $stats['total_days'] > 0 ? round(($stats['present'] / $stats['total_days']) * 100, 2) : 0;

            return [
                'type' => 'weekly',
                'student_id' => $student_id,
                'class_id' => $class_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'attendance_records' => $attendance_records,
                'statistics' => $stats,
                'attendance_percentage' => $percentage
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generate monthly report for student
     */
    public function generateMonthlyReport($student_id, $class_id, $month = null, $year = null) {
        try {
            if (!$month) $month = date('m');
            if (!$year) $year = date('Y');

            $start_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));

            $this->db->prepare('SELECT * FROM attendance WHERE student_id = ? AND class_id = ? 
            AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $start_date);
            $this->db->bind('s', $end_date);
            $this->db->execute();

            $attendance_records = $this->db->getResults();

            // Calculate statistics
            $stats = [
                'total_days' => count($attendance_records),
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];

            foreach ($attendance_records as $record) {
                $stats[$record['status']]++;
            }

            $percentage = $stats['total_days'] > 0 ? round(($stats['present'] / $stats['total_days']) * 100, 2) : 0;

            // Save report to database
            $this->db->prepare('INSERT INTO attendance_reports (student_id, class_id, report_type, total_days, present_days, absent_days, late_days, attendance_percentage, report_date) 
            VALUES (?, ?, "monthly", ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE total_days = VALUES(total_days), present_days = VALUES(present_days), absent_days = VALUES(absent_days), late_days = VALUES(late_days), attendance_percentage = VALUES(attendance_percentage)');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('i', $stats['total_days']);
            $this->db->bind('i', $stats['present']);
            $this->db->bind('i', $stats['absent']);
            $this->db->bind('i', $stats['late']);
            $this->db->bind('d', $percentage);
            $this->db->bind('s', date('Y-m-01'));
            $this->db->execute();

            return [
                'type' => 'monthly',
                'student_id' => $student_id,
                'class_id' => $class_id,
                'month' => $month,
                'year' => $year,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'attendance_records' => $attendance_records,
                'statistics' => $stats,
                'attendance_percentage' => $percentage
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generate class attendance report for date
     */
    public function generateClassReportForDate($class_id, $attendance_date) {
        try {
            $this->db->prepare('SELECT a.*, s.student_id, s.roll_number, u.full_name FROM attendance a 
            JOIN students s ON a.student_id = s.id 
            JOIN users u ON s.user_id = u.id 
            WHERE a.class_id = ? AND a.attendance_date = ? ORDER BY u.full_name');
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $attendance_date);
            $this->db->execute();

            $attendance_records = $this->db->getResults();

            $stats = [
                'total_present' => 0,
                'total_absent' => 0,
                'total_late' => 0,
                'total_excused' => 0,
                'class_average' => 0
            ];

            foreach ($attendance_records as $record) {
                if ($record['status'] === 'present') $stats['total_present']++;
                elseif ($record['status'] === 'absent') $stats['total_absent']++;
                elseif ($record['status'] === 'late') $stats['total_late']++;
                elseif ($record['status'] === 'excused') $stats['total_excused']++;
            }

            $total = count($attendance_records);
            if ($total > 0) {
                $stats['class_average'] = round((($stats['total_present'] + $stats['total_excused']) / $total) * 100, 2);
            }

            return [
                'type' => 'class_daily',
                'class_id' => $class_id,
                'date' => $attendance_date,
                'attendance_records' => $attendance_records,
                'statistics' => $stats
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get student attendance summary
     */
    public function getStudentSummary($student_id, $class_id) {
        try {
            // Get total classes
            $this->db->prepare('SELECT COUNT(DISTINCT attendance_date) as total_classes FROM attendance 
            WHERE student_id = ? AND class_id = ?');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $result = $this->db->getSingleResult();
            $total_classes = $result['total_classes'] ?? 0;

            // Get attendance by status
            $this->db->prepare('SELECT status, COUNT(*) as count FROM attendance 
            WHERE student_id = ? AND class_id = ? GROUP BY status');
            $this->db->bind('i', $student_id);
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $status_records = $this->db->getResults();

            $summary = [
                'total_classes' => $total_classes,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];

            foreach ($status_records as $record) {
                $summary[$record['status']] = $record['count'];
            }

            $percentage = $total_classes > 0 ? round(($summary['present'] / $total_classes) * 100, 2) : 0;
            $summary['attendance_percentage'] = $percentage;

            return $summary;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get saved reports
     */
    public function getSavedReports($student_id = null, $class_id = null, $report_type = null) {
        try {
            $query = 'SELECT ar.*, s.student_id, u.full_name, c.class_name FROM attendance_reports ar 
            JOIN students s ON ar.student_id = s.id 
            JOIN users u ON s.user_id = u.id 
            JOIN classes c ON ar.class_id = c.id WHERE 1=1';

            if ($student_id) {
                $query .= ' AND ar.student_id = ' . $student_id;
            }
            if ($class_id) {
                $query .= ' AND ar.class_id = ' . $class_id;
            }
            if ($report_type) {
                $query .= ' AND ar.report_type = "' . $report_type . '"';
            }

            $query .= ' ORDER BY ar.report_date DESC';

            $this->db->prepare($query);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>