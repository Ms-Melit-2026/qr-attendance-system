<?php
// pages/teacher/dashboard.php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/session.php';
require_once '../../classes/Database.php';
require_once '../../classes/Teacher.php';
require_once '../../classes/ClassManagement.php';
require_once '../../helpers/helpers.php';

requireRole('teacher');

$db = new Database($conn);
$teacher_class = new Teacher($db);
$class_mgmt = new ClassManagement($db);

// Get teacher info
$teacher_info = $teacher_class->getTeacherByUserId($_SESSION['user_id']);
$teacher_stats = $teacher_class->getTeacherStats($teacher_info['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .stat-card {
            padding: 25px;
            text-align: center;
            border-radius: 10px;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
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
            <div class="navbar-nav ml-auto">
                <span class="nav-link">
                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link" href="classes.php">
                <i class="fas fa-book"></i> My Classes
            </a>
            <a class="nav-link" href="attendance.php">
                <i class="fas fa-check-circle"></i> Attendance
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="mb-4">Teacher Dashboard</h1>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div style="font-size: 40px; margin-bottom: 10px;"><i class="fas fa-book"></i></div>
                    <div style="font-size: 24px; font-weight: 700;"><?php echo $teacher_stats['total_classes']; ?></div>
                    <div>Total Classes</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div style="font-size: 40px; margin-bottom: 10px;"><i class="fas fa-users"></i></div>
                    <div style="font-size: 24px; font-weight: 700;"><?php echo $teacher_stats['total_students']; ?></div>
                    <div>Total Students</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div style="font-size: 40px; margin-bottom: 10px;"><i class="fas fa-check-circle"></i></div>
                    <div style="font-size: 24px; font-weight: 700;"><?php echo $teacher_stats['today_attendance']; ?></div>
                    <div>Today's Attendance</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Teacher Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($teacher_info['full_name']); ?></p>
                        <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($teacher_info['teacher_id']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($teacher_info['department']); ?></p>
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($teacher_info['subject_specialization']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>