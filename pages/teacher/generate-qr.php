<?php
// pages/teacher/generate-qr.php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/session.php';
require_once '../../classes/Database.php';
require_once '../../classes/Teacher.php';
require_once '../../classes/QRCode.php';
require_once '../../helpers/helpers.php';

requireRole('teacher');

$db = new Database($conn);
$teacher = new Teacher($db);
$qrcode = new QRCode($db);

// Get teacher info
$teacher_info = $teacher->getTeacherByUserId($_SESSION['user_id']);
$classes = $teacher->getTeacherClasses($teacher_info['id']);

$qr_data = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;
    $validity_minutes = $_POST['validity_minutes'] ?? 15;

    if (!$class_id) {
        $error = 'Please select a class';
    } else {
        $result = $qrcode->createQRCode($class_id, $validity_minutes);
        if ($result['success']) {
            $qr_data = $result;
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR Code - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
        #qrcode {
            padding: 20px;
            background: white;
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
                    <i class="fas fa-home"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h1 class="mb-4">Generate QR Code for Attendance</h1>

        <?php if (!empty($error) ?? false): ?>
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
            <!-- QR Generation Form -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Generate New QR Code</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="class_id">Select Class *</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?php echo $c['id']; ?>">
                                            <?php echo htmlspecialchars($c['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="validity_minutes">QR Code Validity (minutes)</label>
                                <input type="number" class="form-control" id="validity_minutes" name="validity_minutes" value="15" min="1" max="120">
                                <small class="form-text text-muted">How long the QR code will be valid for scanning</small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-qrcode"></i> Generate QR Code
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- QR Code Display -->
            <div class="col-md-6">
                <?php if ($qr_data): ?>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">QR Code Generated</h5>
                        </div>
                        <div class="card-body text-center">
                            <div id="qrcode"></div>
                            
                            <div class="mt-3">
                                <p><strong>QR Code ID:</strong><br>
                                <code><?php echo htmlspecialchars($qr_data['qr_code_id']); ?></code></p>
                                
                                <p><strong>Expires At:</strong><br>
                                <?php echo formatDateTime($qr_data['expires_at']); ?></p>
                                
                                <p class="text-muted small">Students can scan this QR code to mark their attendance</p>
                            </div>

                            <button class="btn btn-info btn-sm" onclick="printQRCode()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="downloadQRCode()">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-qrcode" style="font-size: 64px; color: #ccc;"></i>
                            <p class="text-muted mt-3">Generate a QR code to display here</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        <?php if ($qr_data): ?>
        // Generate QR code
        var qrcode = new QRCode(document.getElementById('qrcode'), {
            text: "<?php echo htmlspecialchars($qr_data['qr_code_id']); ?>",
            width: 300,
            height: 300,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        function printQRCode() {
            var qrcodeDiv = document.getElementById('qrcode');
            var printWindow = window.open('', '', 'height=400,width=600');
            printWindow.document.write('<html><head><title>QR Code</title></head><body>');
            printWindow.document.write(qrcodeDiv.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }

        function downloadQRCode() {
            var canvas = document.querySelector('#qrcode canvas');
            var link = document.createElement('a');
            link.href = canvas.toDataURL();
            link.download = 'qrcode_<?php echo $qr_data["qr_code_id"]; ?>.png';
            link.click();
        }
        <?php endif; ?>
    </script>
</body>
</html>