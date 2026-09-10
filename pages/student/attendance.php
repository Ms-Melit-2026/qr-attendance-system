<?php
// pages/student/attendance.php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/session.php';
require_once '../../classes/Database.php';
require_once '../../classes/Student.php';
require_once '../../classes/Attendance.php';
require_once '../../classes/Report.php';
require_once '../../helpers/helpers.php';

requireRole('student');

$db = new Database($conn);
$student = new Student($db);
$attendance = new Attendance($db);
$report = new Report($db);

// Get student info
$student_info = $student->getStudentByUserId($_SESSION['user_id']);
$student_classes = $student->getStudentClasses($student_info['id']);

$class_id = $_GET['class_id'] ?? null;
$class_attendance = [];
$stats = [];

if ($class_id) {
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-t');
    
    $class_attendance = $attendance->getStudentAttendance($student_info['id'], $class_id, $start_date, $end_date);
    $stats = $attendance->getAttendanceStats($student_info['id'], $class_id, $start_date, $end_date);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - <?php echo APP_NAME; ?></title>
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
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .stat-box {
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
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
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h1 class="mb-4">My Attendance Records</h1>

        <!-- Class Selection -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="class_id" class="mr-2">Select Class:</label>
                        <select class="form-control" id="class_id" name="class_id" onchange="this.form.submit()">
                            <option value="">-- Select Class --</option>
                            <?php foreach ($student_classes as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $class_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($class_id && !empty($stats)): ?>
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5>Total Days</h5>
                        <h3><?php echo $stats['total_days']; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <h5>Present</h5>
                        <h3><?php echo $stats['present_days']; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <h5>Absent</h5>
                        <h3><?php echo $stats['absent_days']; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <h5>Attendance %</h5>
                        <h3><?php echo formatPercentage($stats['attendance_percentage']); ?></h3>
                    </div>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Attendance Records</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($class_attendance)): ?>
                                    <?php foreach ($class_attendance as $record): ?>
                                        <tr>
                                            <td><?php echo formatDate($record['attendance_date']); ?></td>
                                            <td><?php echo formatTime($record['time_in']); ?></td>
                                            <td><?php echo formatTime($record['time_out']); ?></td>
                                            <td><?php echo getStatusBadge($record['status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No attendance records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif (!$class_id): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Please select a class to view attendance records.
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No attendance records found for the selected date range.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>