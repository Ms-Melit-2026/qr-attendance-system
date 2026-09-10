<?php
// pages/student/mark-attendance.php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/session.php';
require_once '../../classes/Database.php';
require_once '../../classes/Student.php';
require_once '../../classes/QRCode.php';
require_once '../../classes/Attendance.php';
require_once '../../helpers/helpers.php';

requireRole('student');

$db = new Database($conn);
$student = new Student($db);
$qrcode = new QRCode($db);
$attendance = new Attendance($db);

// Get student info
$student_info = $student->getStudentByUserId($_SESSION['user_id']);
$student_classes = $student->getStudentClasses($student_info['id']);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;
    $qr_code_id = $_POST['qr_code_id'] ?? null;

    if (!$class_id || !$qr_code_id) {
        $error = 'Please select class and enter QR code';
    } else {
        // Verify QR code
        $qr_verification = $qrcode->verifyQRCode($qr_code_id);
        if (!$qr_verification['valid']) {
            $error = $qr_verification['message'];
        } else {
            // Record attendance
            $result = $attendance->recordTimeIn($student_info['id'], $class_id, $qr_verification['qr_code']['id']);
            if ($result['success']) {
                $message = $result['message'] . ' (' . ucfirst($result['status']) . ')';
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - <?php echo APP_NAME; ?></title>
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
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }
        #video {
            width: 100%;
            display: block;
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
        <h1 class="mb-4">Mark Your Attendance</h1>

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
            <!-- QR Scanner -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">QR Code Scanner</h5>
                    </div>
                    <div class="card-body">
                        <div class="camera-container">
                            <video id="video" playsinline></video>
                        </div>
                        <p class="text-center text-muted small mt-3">Point camera at QR code to mark attendance</p>
                    </div>
                </div>
            </div>

            <!-- Manual Entry -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Manual Entry</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="class_id">Select Class *</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($student_classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="qr_code_id">QR Code *</label>
                                <input type="text" class="form-control" id="qr_code_id" name="qr_code_id" placeholder="Scan or enter QR code" required autofocus>
                            </div>

                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> Submit Attendance
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Today's Status -->
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Today's Status</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $today_attendance = $attendance->getStudentAttendance(
                            $student_info['id'], 
                            $_POST['class_id'] ?? null, 
                            date('Y-m-d'), 
                            date('Y-m-d')
                        );
                        ?>
                        <?php if (!empty($today_attendance)): ?>
                            <div class="list-group">
                                <?php foreach ($today_attendance as $record): ?>
                                    <div class="list-group-item">
                                        <h6 class="mb-1">Marked at <?php echo formatDateTime($record['time_in']); ?></h6>
                                        <p class="mb-0"><?php echo getStatusBadge($record['status']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No attendance marked today</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        const video = document.getElementById('video');
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const qrCodeIdInput = document.getElementById('qr_code_id');
        const classSelect = document.getElementById('class_id');

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                scanQRCode();
            } catch (err) {
                console.error('Camera access denied:', err);
            }
        }

        function scanQRCode() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code && qrCodeIdInput.value !== code.data) {
                    qrCodeIdInput.value = code.data;
                }
            }
            requestAnimationFrame(scanQRCode);
        }

        startCamera();
    </script>
</body>
</html>