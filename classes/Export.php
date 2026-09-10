<?php
/**
 * Export Class for generating PDF and Excel reports
 */

class Export {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Export attendance to CSV
     */
    public function exportToCSV($data, $filename = 'attendance_report') {
        try {
            $filename = $filename . '_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Export attendance to Excel (using CSV as fallback)
     */
    public function exportToExcel($data, $filename = 'attendance_report') {
        try {
            $filename = $filename . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // For full Excel support, use PHPExcel library
            // This is a simplified CSV version that Excel can read
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($output, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generate HTML table for report
     */
    public function generateHTMLTable($data, $title = 'Attendance Report') {
        try {
            $html = '<html><head><meta charset="UTF-8"><title>' . $title . '</title>';
            $html .= '<style>';
            $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
            $html .= 'table { border-collapse: collapse; width: 100%; margin-top: 20px; }';
            $html .= 'th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }';
            $html .= 'th { background-color: #4CAF50; color: white; }';
            $html .= 'tr:nth-child(even) { background-color: #f2f2f2; }';
            $html .= 'h1 { color: #333; }';
            $html .= '</style>';
            $html .= '</head><body>';
            $html .= '<h1>' . $title . '</h1>';
            $html .= '<p>Generated on: ' . date('d M Y H:i:s') . '</p>';
            $html .= '<table>';

            // Table header
            $html .= '<thead><tr>';
            foreach (array_keys($data[0]) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';

            // Table body
            $html .= '<tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '</body></html>';

            return ['success' => true, 'html' => $html];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generate printable PDF (using HTML2PDF or similar)
     */
    public function generatePDF($html, $filename = 'report') {
        try {
            // For full PDF generation, integrate a library like TCPDF or DOMPDF
            // This returns HTML that can be printed
            $filename = $filename . '_' . date('Y-m-d_H-i-s');
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '.pdf"');
            
            // Placeholder - actual PDF generation would require library
            echo $html;
            exit;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Prepare attendance data for export
     */
    public function prepareAttendanceData($class_id, $start_date, $end_date) {
        try {
            $this->db->prepare('SELECT s.student_id, u.full_name, a.attendance_date, a.status, a.time_in, a.time_out 
            FROM attendance a 
            JOIN students s ON a.student_id = s.id 
            JOIN users u ON s.user_id = u.id 
            WHERE a.class_id = ? AND a.attendance_date BETWEEN ? AND ? 
            ORDER BY a.attendance_date, u.full_name');
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
     * Prepare student summary data for export
     */
    public function prepareStudentSummaryData($class_id) {
        try {
            $query = 'SELECT 
                s.student_id,
                u.full_name,
                COUNT(CASE WHEN a.status = "present" THEN 1 END) as present,
                COUNT(CASE WHEN a.status = "absent" THEN 1 END) as absent,
                COUNT(CASE WHEN a.status = "late" THEN 1 END) as late,
                COUNT(CASE WHEN a.status = "excused" THEN 1 END) as excused,
                COUNT(*) as total,
                ROUND((COUNT(CASE WHEN a.status = "present" THEN 1 END) / COUNT(*) * 100), 2) as percentage
            FROM students s
            JOIN class_enrollment ce ON s.id = ce.student_id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN attendance a ON s.id = a.student_id AND a.class_id = ?
            WHERE ce.class_id = ? AND ce.status = "active"
            GROUP BY s.id, u.full_name
            ORDER BY u.full_name';

            $this->db->prepare($query);
            $this->db->bind('i', $class_id);
            $this->db->bind('i', $class_id);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>