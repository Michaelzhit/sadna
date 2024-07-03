<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Include database configuration
include '../config.php';

// Set UTF-8 encoding for the database connection
mysqli_set_charset($conn, "utf8mb4");

// Get user ID from session
$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['transporterPhone'];
    $serviceRegion = $_POST['serviceRegion'];
    $aboutTransporter = $_POST['aboutTransporter'];
    $defaultImage = '../../images/defaultImage.png'; 

    // Handle file upload or use default image
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    } else {
        $photo = file_get_contents($defaultImage);
    }

    // Insert into TransporterAds table
    $stmt = $conn->prepare("INSERT INTO TransporterAds (user_id, phone, service_region, about, photo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssb", $userId, $phone, $serviceRegion, $aboutTransporter, $photo);

    if ($photo !== NULL) {
        $stmt->send_long_data(4, $photo);
    }

    if ($stmt->execute()) {
        header("Location: /pages/profile_screen.php"); 
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<?php include '../header_footer/header.php'; ?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>הוסף מודעת מוביל</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body {
            direction: rtl;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center mt-4">
            <h1>הוסף מודעת מוביל</h1>
        </div>
        <div class="form-section mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm">
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="transporterPhone" class="form-label">מספר טלפון</label>
                                <input type="text" class="form-control" id="transporterPhone" name="transporterPhone" required>
                            </div>
                            <div class="mb-3">
                                <label for="serviceRegion" class="form-label">אזור שירות</label>
                                <select class="form-select" id="serviceRegion" name="serviceRegion" required>
                                    <option selected disabled value="">בחר...</option>
                                    <option value="מרכז">מרכז</option>
                                    <option value="דרום">דרום</option>
                                    <option value="צפון">צפון</option>
                                    <option value="שפלה">שפלה</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="aboutTransporter" class="form-label">אודות עצמי (מוביל)</label>
                                <textarea class="form-control" id="aboutTransporter" name="aboutTransporter" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">העלה תמונה</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">הוסף מודעת מוביל</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php include '../header_footer/footer.php'; ?>
