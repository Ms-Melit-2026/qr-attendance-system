<?php
// pages/admin/dashboard.php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/session.php';
require_once '../../classes/Database.php';
require_once '../../classes/User.php';
require_once '../../classes/Student.php';
require_once '../../classes/Teacher.php';
require_once '../../classes/ClassManagement.php';
require_once '../../helpers/helpers.php';

requireRole('admin');

$db = new Database($conn);
$user = new User($db);
$student = new Student($db);
$teacher = new Teacher($db);
$class = new ClassManagement($db);

// Get statistics
$total_students = $student->getStudentCount();
$total_teachers = $teacher->getTeacherCount();

// Get user count by role
$all_users = $user->getAllUsers();
$admin_count = 0;
$teacher_count = 0;
$student_count = 0;
foreach ($all_users as $u) {
    if ($u['role'] === 'admin') $admin_count++;
    elseif ($u['role'] === 'teacher') $teacher_count++;
    elseif ($u['role'] === 'student') $student_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 20px;
        }
        .sidebar {
            background: white;
            min-height: calc(100vh - 70px);
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            left: 0;
            top: 70px;
            width: 250px;
            padding: 20px 0;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            margin-top: 70px;
            padding: 20px;
        }
        .nav-link {
            color: #333 !important;
            padding: 12px 20px !important;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: #f0f0f0;
            border-left-color: #667eea;
        }
        .nav-link.active {
            background-color: #f0f0f0;
            border-left-color: #667eea;
            color: #667eea !important;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }
        .stat-card {
            padding: 25px;
            text-align: center;
            border-radius: 10px;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .page-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>">
                <i class="fas fa-qrcode"></i> QR Attendance
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link" href="users.php">
                <i class="fas fa-users"></i> User Management
            </a>
            <a class="nav-link" href="students.php">
                <i class="fas fa-user-graduate"></i> Students
            </a>
            <a class="nav-link" href="teachers.php">
                <i class="fas fa-chalkboard-user"></i> Teachers
            </a>
            <a class="nav-link" href="classes.php">
                <i class="fas fa-book"></i> Classes
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a class="nav-link" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a class="nav-link" href="audit-logs.php">
                <i class="fas fa-history"></i> Audit Logs
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="page-title">Admin Dashboard</h1>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="stat-icon"><i class="fas fa-chalkboard-user"></i></div>
                    <div class="stat-number"><?php echo $total_teachers; ?></div>
                    <div class="stat-label">Total Teachers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-number">0</div>
                    <div class="stat-label">Active Classes</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number">0</div>
                    <div class="stat-label">Today's Attendance</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Quick Actions</h5>
                        <button class="btn btn-primary" onclick="location.href='users.php?action=add'">
                            <i class="fas fa-plus"></i> Add New User
                        </button>
                        <button class="btn btn-info" onclick="location.href='students.php?action=add'">
                            <i class="fas fa-plus"></i> Add Student
                        </button>
                        <button class="btn btn-success" onclick="location.href='classes.php?action=add'">
                            <i class="fas fa-plus"></i> Create Class
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">System Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <td><strong>System Name:</strong></td>
                                <td><?php echo APP_NAME; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Version:</strong></td>
                                <td><?php echo APP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Admin User:</strong></td>
                                <td><?php echo htmlspecialchars($_SESSION['full_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Current Date & Time:</strong></td>
                                <td><?php echo formatDateTime(date('Y-m-d H:i:s')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>