<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
include '../config.php';

// Get user ID from session
$userId = $_SESSION['user_id'];

// Handle add animal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['animalTypeAdd'], $_POST['breed'], $_POST['animalName'])) {
    $animalType = $_POST['animalTypeAdd'];
    $breed = $_POST['breed'];
    $animalName = $_POST['animalName'];
    $animalPhoto = null;

    // Handle animal photo upload
    if (isset($_FILES['animalPhoto']) && $_FILES['animalPhoto']['error'] == UPLOAD_ERR_OK) {
        $animalPhoto = file_get_contents($_FILES['animalPhoto']['tmp_name']);
    } else {
        $animalPhoto = file_get_contents('../images/catdogUSER.png'); // Use default image
    }

    // Insert the animal into the database with the photo
    $stmt = $conn->prepare("INSERT INTO Animals (type, breed, name, photo, owner_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $animalType, $breed, $animalName, $animalPhoto, $userId);
    $stmt->send_long_data(3, $animalPhoto);

    if ($stmt->execute()) {
        // Redirect to profile page to show the new animal
        header("Location: ../profile_screen.php");
        exit();
    } else {
        $addAnimalError = "שגיאה בהוספת חיה.";
        error_log("Error inserting animal: " . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>
<?php include '../header_footer/header.php'; ?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>הוסף חיה</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="path/to/your/styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header text-center mt-4">
            <h1>הוסף חיה</h1>
        </div>
        <div class="form-section mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="animalTypeAdd" class="form-label">סוג החיה</label>
                                <select class="form-select" id="animalTypeAdd" name="animalTypeAdd" required>
                                    <option selected disabled value="">בחר...</option>
                                    <option value="כלב">כלב</option>
                                    <option value="חתול">חתול</option>
                                    <option value="אחר">אחר</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="breed" class="form-label">גזע</label>
                                <input type="text" class="form-control" id="breed" name="breed">
                            </div>
                            <div class="mb-3">
                                <label for="animalName" class="form-label">שם החיה</label>
                                <input type="text" class="form-control" id="animalName" name="animalName" required>
                            </div>
                            <div class="mb-3">
                                <label for="animalPhoto" class="form-label">תמונת חיה</label>
                                <input type="file" class="form-control" id="animalPhoto" name="animalPhoto" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">הוסף חיה</button>
                        </form>
                        <?php if (isset($addAnimalError)): ?>
                            <div class="alert alert-danger mt-3" role="alert">
                                <?php echo $addAnimalError; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include '../header_footer/footer.php'; ?>