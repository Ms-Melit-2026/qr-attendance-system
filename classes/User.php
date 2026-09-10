<?php
/**
 * User Class for user management
 */

class User {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Register new user
     */
    public function register($username, $email, $password, $full_name, $role) {
        try {
            // Check if username exists
            $this->db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $this->db->bind('s', $username);
            $this->db->bind('s', $email);
            $this->db->execute();

            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user
            $this->db->prepare('INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, ?, "active")');
            $this->db->bind('s', $username);
            $this->db->bind('s', $email);
            $this->db->bind('s', $hashed_password);
            $this->db->bind('s', $full_name);
            $this->db->bind('s', $role);
            $this->db->execute();

            $user_id = $this->db->lastInsertId();
            return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $user_id];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            $this->db->prepare('SELECT id, username, email, password, full_name, role, status FROM users WHERE username = ? OR email = ?');
            $this->db->bind('s', $username);
            $this->db->bind('s', $username);
            $this->db->execute();

            if ($this->db->rowCount() == 0) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            $user = $this->db->getSingleResult();

            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Your account is inactive'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            return ['success' => true, 'message' => 'Login successful', 'user' => $user];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        try {
            $this->db->prepare('SELECT id, username, email, full_name, role, status, created_at FROM users WHERE id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            return $this->db->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all users with filter
     */
    public function getAllUsers($role = null, $status = null, $limit = null, $offset = 0) {
        try {
            $query = 'SELECT id, username, email, full_name, role, status, created_at FROM users WHERE 1=1';

            if ($role) {
                $query .= ' AND role = "' . $role . '"';
            }
            if ($status) {
                $query .= ' AND status = "' . $status . '"';
            }

            $query .= ' ORDER BY created_at DESC';

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
     * Update user
     */
    public function updateUser($id, $email, $full_name, $status) {
        try {
            $this->db->prepare('UPDATE users SET email = ?, full_name = ?, status = ? WHERE id = ?');
            $this->db->bind('s', $email);
            $this->db->bind('s', $full_name);
            $this->db->bind('s', $status);
            $this->db->bind('i', $id);
            $this->db->execute();

            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Change password
     */
    public function changePassword($id, $old_password, $new_password) {
        try {
            $this->db->prepare('SELECT password FROM users WHERE id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            $user = $this->db->getSingleResult();

            if (!password_verify($old_password, $user['password'])) {
                return ['success' => false, 'message' => 'Incorrect current password'];
            }

            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $this->db->bind('s', $hashed_password);
            $this->db->bind('i', $id);
            $this->db->execute();

            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        try {
            $this->db->prepare('DELETE FROM users WHERE id = ?');
            $this->db->bind('i', $id);
            $this->db->execute();
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>