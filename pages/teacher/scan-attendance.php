<?php
// pages/teacher/scan-attendance.php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/session.php';
require_once '../../classes/Database.php';
require_once '../../classes/Teacher.php';
require_once '../../classes/ClassManagement.php';
require_once '../../classes/QRCode.php';
require_once '../../classes/Attendance.php';
require_once '../../helpers/helpers.php';

requireRole('teacher');

$db = new Database($conn);
$teacher = new Teacher($db);
$class_mgmt = new ClassManagement($db);
$qrcode = new QRCode($db);
$attendance = new Attendance($db);

// Get teacher info
$teacher_info = $teacher->getTeacherByUserId($_SESSION['user_id']);

$message = '';
$error = '';
$class_id = $_GET['class_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    $qr_code_id = $_POST['qr_code_id'] ?? null;

    if (!$class_id || !$student_id || !$qr_code_id) {
        $error = 'Missing required fields';
    } else {
        // Verify QR code
        $qr_verification = $qrcode->verifyQRCode($qr_code_id);
        if (!$qr_verification['valid']) {
            $error = $qr_verification['message'];
        } else {
            // Record attendance
            $result = $attendance->recordTimeIn($student_id, $class_id, $qr_verification['qr_code']['id']);
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

$classes = $teacher->getTeacherClasses($teacher_info['id']);
$class_data = null;
if ($class_id) {
    $class_data = $class_mgmt->getClassById($class_id);
    $class_students = $class_mgmt->getClassStudents($class_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Attendance - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.js"></script>
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
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }
        #video {
            width: 100%;
            display: block;
        }
        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            border: 3px solid #00ff00;
            border-radius: 10px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5) inset;
        }
        .attendance-list {
            max-height: 400px;
            overflow-y: auto;
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
                    <i class="fas fa-home"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h1 class="mb-4">Mark Attendance via QR Code</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Class Selection -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Select Class</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="form-group">
                                <label for="class_id">Class</label>
                                <select class="form-control" id="class_id" name="class_id" onchange="this.form.submit()">
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $class_id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>

                        <?php if ($class_data): ?>
                            <div class="alert alert-info">
                                <strong>Class Info:</strong><br>
                                <small>
                                    Code: <?php echo htmlspecialchars($class_data['class_code']); ?><br>
                                    Room: <?php echo htmlspecialchars($class_data['room_number']); ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- QR Scanner -->
            <div class="col-md-4">
                <?php if ($class_data): ?>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">QR Code Scanner</h5>
                        </div>
                        <div class="card-body">
                            <div class="camera-container">
                                <video id="video" playsinline></video>
                                <div class="scanner-overlay"></div>
                            </div>
                            <p class="text-center text-muted small mt-3">Point camera at QR code</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-camera" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-3">Select a class to start scanning</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Manual Input -->
            <div class="col-md-4">
                <?php if ($class_data): ?>
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title mb-0">Manual Entry</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                
                                <div class="form-group">
                                    <label for="student_id">Select Student</label>
                                    <select class="form-control" id="student_id" name="student_id" required>
                                        <option value="">-- Select Student --</option>
                                        <?php foreach ($class_students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>">
                                                <?php echo htmlspecialchars($student['full_name'] . ' (' . $student['student_id'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="qr_code_id">QR Code ID</label>
                                    <input type="text" class="form-control" id="qr_code_id" name="qr_code_id" placeholder="Enter QR Code" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-check"></i> Mark Present
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Today's Attendance -->
        <?php if ($class_data): ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Today's Attendance - <?php echo formatDate(date('Y-m-d')); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $today_attendance = $attendance->getClassAttendanceForDate($class_id, date('Y-m-d'));
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Student ID</th>
                                            <th>Status</th>
                                            <th>Time In</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($today_attendance)): ?>
                                            <?php foreach ($today_attendance as $rec): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($rec['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($rec['student_id']); ?></td>
                                                    <td><?php echo getStatusBadge($rec['status']); ?></td>
                                                    <td><?php echo formatDateTime($rec['time_in']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No attendance records yet</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        <?php if ($class_data): ?>
        const video = document.getElementById('video');
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const qrCodeIdInput = document.getElementById('qr_code_id');

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                scanQRCode();
            } catch (err) {
                console.error('Camera access denied:', err);
                alert('Camera access is required to scan QR codes');
            }
        }

        function scanQRCode() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    qrCodeIdInput.value = code.data;
                    // Auto-submit form
                    // document.querySelector('form').submit();
                }
            }
            requestAnimationFrame(scanQRCode);
        }

        startCamera();
        <?php endif; ?>
    </script>
</body>
</html>