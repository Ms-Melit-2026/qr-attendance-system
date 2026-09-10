<?php
/**
 * QRCode Class for QR code generation and management
 */

class QRCode {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Generate unique QR code ID
     */
    private function generateQRCodeId() {
        return uniqid('QR_') . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Create new QR code session
     */
    public function createQRCode($class_id, $validity_minutes = 15) {
        try {
            $qr_code_id = $this->generateQRCodeId();
            $session_id = 'SESSION_' . date('YmdHis') . '_' . uniqid();
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$validity_minutes} minutes"));

            $this->db->prepare('INSERT INTO qr_codes (qr_code_unique_id, class_id, session_id, expires_at, status) 
            VALUES (?, ?, ?, ?, "active")');
            $this->db->bind('s', $qr_code_id);
            $this->db->bind('i', $class_id);
            $this->db->bind('s', $session_id);
            $this->db->bind('s', $expires_at);
            $this->db->execute();

            $qr_id = $this->db->lastInsertId();
            return [
                'success' => true,
                'message' => 'QR code generated successfully',
                'qr_id' => $qr_id,
                'qr_code_id' => $qr_code_id,
                'session_id' => $session_id,
                'expires_at' => $expires_at
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get QR code by ID
     */
    public function getQRCodeById($id) {
        try {
            $this->db->prepare('SELECT * FROM qr_codes WHERE id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get QR code by unique ID
     */
    public function getQRCodeByUniqueId($qr_code_unique_id) {
        try {
            $this->db->prepare('SELECT * FROM qr_codes WHERE qr_code_unique_id = ?');
            $this->db->bind('s', $qr_code_unique_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Verify QR code validity
     */
    public function verifyQRCode($qr_code_id) {
        try {
            $qr_code = $this->getQRCodeByUniqueId($qr_code_id);

            if (!$qr_code) {
                return ['valid' => false, 'message' => 'QR code not found'];
            }

            if ($qr_code['status'] === 'expired') {
                return ['valid' => false, 'message' => 'QR code has expired'];
            }

            if ($qr_code['status'] === 'used') {
                return ['valid' => false, 'message' => 'QR code has already been used'];
            }

            $now = new DateTime();
            $expires = new DateTime($qr_code['expires_at']);

            if ($now > $expires) {
                // Update status to expired
                $this->updateQRCodeStatus($qr_code['id'], 'expired');
                return ['valid' => false, 'message' => 'QR code has expired'];
            }

            return ['valid' => true, 'qr_code' => $qr_code];
        } catch (Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update QR code status
     */
    public function updateQRCodeStatus($id, $status) {
        try {
            $this->db->prepare('UPDATE qr_codes SET status = ? WHERE id = ?');
            $this->db->bind('s', $status);
            $this->db->bind('i', $id);
            $this->db->execute();
            return ['success' => true, 'message' => 'QR code status updated'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get active QR codes for class
     */
    public function getActiveQRCodesForClass($class_id) {
        try {
            $this->db->prepare('SELECT * FROM qr_codes WHERE class_id = ? AND status = "active" ORDER BY created_at DESC');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            return $this->db->getResults();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get latest QR code for class
     */
    public function getLatestQRCodeForClass($class_id) {
        try {
            $this->db->prepare('SELECT * FROM qr_codes WHERE class_id = ? ORDER BY created_at DESC LIMIT 1');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Expire all QR codes for class
     */
    public function expireAllQRCodesForClass($class_id) {
        try {
            $this->db->prepare('UPDATE qr_codes SET status = "expired" WHERE class_id = ? AND status = "active"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            return ['success' => true, 'message' => 'QR codes expired successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get QR code statistics
     */
    public function getQRCodeStats($class_id) {
        try {
            $stats = [];

            // Total QR codes generated
            $this->db->prepare('SELECT COUNT(*) as count FROM qr_codes WHERE class_id = ?');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['total_generated'] = $this->db->getSingleResult()['count'] ?? 0;

            // Active QR codes
            $this->db->prepare('SELECT COUNT(*) as count FROM qr_codes WHERE class_id = ? AND status = "active"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['active'] = $this->db->getSingleResult()['count'] ?? 0;

            // Used QR codes
            $this->db->prepare('SELECT COUNT(*) as count FROM qr_codes WHERE class_id = ? AND status = "used"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['used'] = $this->db->getSingleResult()['count'] ?? 0;

            // Expired QR codes
            $this->db->prepare('SELECT COUNT(*) as count FROM qr_codes WHERE class_id = ? AND status = "expired"');
            $this->db->bind('i', $class_id);
            $this->db->execute();
            $stats['expired'] = $this->db->getSingleResult()['count'] ?? 0;

            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
}
?>