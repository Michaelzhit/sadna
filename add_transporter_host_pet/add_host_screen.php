<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Include database configuration
include '../config.php';

// Get user ID from session
$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $propertyType = $_POST['propertyType'];
    $animalType = $_POST['animalType'];
    $serviceRegion = $_POST['serviceRegion'];
    $aboutHost = $_POST['aboutHost'];
    $additionalAnimals = $_POST['additionalAnimals'] === 'yes' ? 1 : 0; // Translate 'yes'/'no' to 1/0
    $defaultImage = '../../images/defaultImage.jpg'; 

    // Handle file upload or use default image
    if (isset($_FILES['hostPhoto']) && $_FILES['hostPhoto']['error'] == UPLOAD_ERR_OK) {
        $hostPhoto = file_get_contents($_FILES['hostPhoto']['tmp_name']);
    } else {
        $hostPhoto = file_get_contents($defaultImage);
    }

    // Prepare SQL statement to insert data
    $stmt = $conn->prepare("INSERT INTO HostAds (user_id, phone, type_of_property, type_of_animal, service_region, about, additional_animals, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssis", $userId, $phone, $propertyType, $animalType, $serviceRegion, $aboutHost, $additionalAnimals, $hostPhoto);

    // Bind the binary data
    $stmt->send_long_data(7, $hostPhoto);

    if ($stmt->execute()) {
        header("Location: ../profile_screen.php"); 
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
    <title>הוספת מודעת מארח</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header text-center mt-4">
            <h1>הוספת מודעת מארח</h1>
        </div>
        <div class="form-section mt-4">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="phone" class="form-label">מספר טלפון</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="propertyType" class="form-label">סוג מגורים</label>
                    <select class="form-select" id="propertyType" name="propertyType" required>
                        <option selected disabled value="">בחר...</option>
                        <option value="דירה בלי חצר">דירה בלי חצר</option>
                        <option value="דירה עם חצר">דירה עם חצר</option>
                        <option value="בית פרטי">בית פרטי</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="animalType" class="form-label">סוג החיה שמוכן לארח</label>
                    <select class="form-select" id="animalType" name="animalType" required>
                        <option selected disabled value="">בחר...</option>
                        <option value="כלב">כלב</option>
                        <option value="חתול">חתול</option>
                        <option value="אחר">אחר</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="serviceRegion" class="form-label">אזור שירות</label>
                    <select class="form-select" id="serviceRegion" name="serviceRegion" required>
                        <option selected disabled value="">בחר...</option>
                        <option value="מרכז">מרכז</option>
                        <option value="דרום">דרום</option>
                        <option value="צפון">צפון</option>
                        <option value="שפלה">שפלה</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="aboutHost" class="form-label">על עצמי (מארח)</label>
                    <textarea class="form-control" id="aboutHost" name="aboutHost" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="additionalAnimals" class="form-label">בע"ח נוספים</label>
                    <select class="form-select" id="additionalAnimals" name="additionalAnimals" required>
                        <option selected disabled value="">בחר...</option>
                        <option value="yes">כן</option>
                        <option value="no">לא</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hostPhoto" class="form-label">העלאת תמונה</label>
                    <input type="file" class="form-control" id="hostPhoto" name="hostPhoto" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">הוסף מודעת מארח</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include '../header_footer/footer.php'; ?>
