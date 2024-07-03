<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: home_screen.php");
    exit();
}

// Include database configuration
include 'config.php';

// Get user ID from session
$userId = $_SESSION['user_id'];

// Handle delete transporter ad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_ad_id'])) {
    $deleteAdId = $_POST['delete_ad_id'];
    $deleteStmt = $conn->prepare("DELETE FROM TransporterAds WHERE transporter_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $deleteAdId, $userId);
    if ($deleteStmt->execute()) {
        // Ad deleted successfully
        header("Location: profile_screen.php");
        exit();
    } else {
        $deleteError = "שגיאה במחיקת המודעה.";
    }
    $deleteStmt->close();
}

// Handle update transporter ad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_ad_id'])) {
    $editAdId = $_POST['edit_ad_id'];
    $phone = $_POST['edit_phone'];
    $serviceRegion = $_POST['edit_service_region'];
    $about = $_POST['edit_about'];

    
    if (isset($_FILES['edit_photo']) && $_FILES['edit_photo']['error'] == 0) {
        $photo = file_get_contents($_FILES['edit_photo']['tmp_name']);
        $updateStmt = $conn->prepare("UPDATE TransporterAds SET phone = ?, service_region = ?, about = ?, photo = ? WHERE transporter_id = ? AND user_id = ?");
        $updateStmt->bind_param("ssssii", $phone, $serviceRegion, $about, $photo, $editAdId, $userId);
    } else {
        $updateStmt = $conn->prepare("UPDATE TransporterAds SET phone = ?, service_region = ?, about = ? WHERE transporter_id = ? AND user_id = ?");
        $updateStmt->bind_param("sssii", $phone, $serviceRegion, $about, $editAdId, $userId);
    }

    if ($updateStmt->execute()) {
        // Ad updated successfully
        header("Location: profile_screen.php");
        exit();
    } else {
        $updateError = "שגיאה בעדכון המודעה.";
    }
    $updateStmt->close();
}

// Handle update host ad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_host_id'])) {
    $editHostId = $_POST['edit_host_id'];
    $phone = $_POST['edit_host_phone'];
    $propertyType = $_POST['edit_property_type'];
    $animalType = $_POST['edit_animal_type'];
    $additionalAnimals = isset($_POST['edit_additional_animals']) ? 1 : 0;
    $about = $_POST['edit_host_about'];

    // Handle image upload
    if (isset($_FILES['edit_host_photo']) && $_FILES['edit_host_photo']['error'] == 0) {
        $photo = file_get_contents($_FILES['edit_host_photo']['tmp_name']);
        $updateStmt = $conn->prepare("UPDATE HostAds SET phone = ?, type_of_property = ?, type_of_animal = ?, additional_animals = ?, about = ?, photo = ? WHERE host_id = ? AND user_id = ?");
        $updateStmt->bind_param("sssissii", $phone, $propertyType, $animalType, $additionalAnimals, $about, $photo, $editHostId, $userId);
    } else {
        $updateStmt = $conn->prepare("UPDATE HostAds SET phone = ?, type_of_property = ?, type_of_animal = ?, additional_animals = ?, about = ? WHERE host_id = ? AND user_id = ?");
        $updateStmt->bind_param("sssissi", $phone, $propertyType, $animalType, $additionalAnimals, $about, $editHostId, $userId);
    }

    if ($updateStmt->execute()) {
        // Ad updated successfully
        header("Location: profile_screen.php");
        exit();
    } else {
        $updateError = "שגיאה בעדכון המודעה.";
    }
    $updateStmt->close();
}

// Handle delete animal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_animal_id'])) {
    $deleteAnimalId = $_POST['delete_animal_id'];
    $deleteStmt = $conn->prepare("DELETE FROM Animals WHERE animal_id = ? AND owner_id = ?");
    $deleteStmt->bind_param("ii", $deleteAnimalId, $userId);
    if ($deleteStmt->execute()) {
        // Animal deleted successfully
        header("Location: profile_screen.php");
        exit();
    } else {
        $deleteError = "שגיאה במחיקת החיה.";
    }
    $deleteStmt->close();
}

// Handle delete host ad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_host_id'])) {
    $deleteHostId = $_POST['delete_host_id'];
    $deleteStmt = $conn->prepare("DELETE FROM HostAds WHERE host_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $deleteHostId, $userId);
    if ($deleteStmt->execute()) {
        // Host ad deleted successfully
        header("Location: profile_screen.php");
        exit();
    } else {
        $deleteError = "שגיאה במחיקת המודעה.";
    }
    $deleteStmt->close();
}

// Prepare SQL statement to fetch user data
$stmt = $conn->prepare("SELECT email, first_name, last_name, profile_picture FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($email, $firstName, $lastName, $profilePicture);
$stmt->fetch();
$stmt->close();

// Fetch user's pets
$pets = [];
$petStmt = $conn->prepare("SELECT animal_id, type, breed, name, photo FROM Animals WHERE owner_id = ?");
$petStmt->bind_param("i", $userId);
$petStmt->execute();
$petStmt->bind_result($animalId, $petType, $petBreed, $petName, $petPhoto);
while ($petStmt->fetch()) {
    $pets[] = [
        'animal_id' => $animalId,
        'type' => $petType,
        'breed' => $petBreed,
        'name' => $petName,
        'photo' => $petPhoto
    ];
}
$petStmt->close();

// Fetch user's transporter ads
$transporterAds = [];
$adStmt = $conn->prepare("SELECT transporter_id, phone, service_region, about, photo FROM TransporterAds WHERE user_id = ?");
$adStmt->bind_param("i", $userId);
$adStmt->execute();
$adStmt->bind_result($transporterId, $phone, $serviceRegion, $about, $photo);
while ($adStmt->fetch()) {
    $transporterAds[] = [
        'transporter_id' => $transporterId,
        'phone' => $phone,
        'service_region' => $serviceRegion,
        'about' => $about,
        'photo' => $photo
    ];
}
$adStmt->close();

// Fetch user's host ads
$hostAds = [];
$hostAdStmt = $conn->prepare("SELECT host_id, phone, service_region, type_of_property, type_of_animal, additional_animals, about, photo FROM HostAds WHERE user_id = ?");
$hostAdStmt->bind_param("i", $userId);
$hostAdStmt->execute();
$hostAdStmt->bind_result($hostId, $phone, $serviceRegion, $propertyType, $animalType, $additionalAnimals, $about, $photo);
while ($hostAdStmt->fetch()) {
    $hostAds[] = [
        'host_id' => $hostId,
        'phone' => $phone,
        'service_region' => $serviceRegion,
        'type_of_property' => $propertyType,
        'type_of_animal' => $animalType,
        'additional_animals' => $additionalAnimals,
        'about' => $about,
        'photo' => $photo
    ];
}
$hostAdStmt->close();

$conn->close();
?>

<?php include 'header_footer/header.php'; ?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>פרופיל</h1>
        </div>
        <div class="profile-section text-center">
            <?php if (!empty($profilePicture)): ?>
                <img id="profilePicture" src="data:image/jpeg;base64,<?php echo base64_encode($profilePicture); ?>" alt="תמונת פרופיל" class="rounded-circle" onclick="document.getElementById('profilePictureInput').click()">
            <?php else: ?>
                <img id="profilePicture" src="../images/defaultImage.png" alt="תמונת פרופיל" class="rounded-circle" onclick="document.getElementById('profilePictureInput').click()">
            <?php endif; ?>
            <form id="profilePictureForm" action="" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" id="profilePictureInput" name="profilePicture" accept="image/*" style="display: none;" onchange="document.getElementById('profilePictureForm').submit()">
            </form>
            <div class="user-info">
                <p><strong>אימייל:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>שם פרטי:</strong> <?php echo htmlspecialchars($firstName); ?></p>
                <p><strong>שם משפחה:</strong> <?php echo htmlspecialchars($lastName); ?></p>
            </div>
            <?php if (isset($uploadError)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo $uploadError; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($deleteError)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo $deleteError; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($updateError)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo $updateError; ?>
                </div>
            <?php endif; ?>
            <hr>
        </div>

        <!-- Pets Section -->
        <div class="pets-section mt-4">
            <div class="d-grid gap-2 mb-3">
                <a href="./add_transporter_host_pet/add_animal_screen.php" class="btn btn-primary"><i class="fas fa-paw"></i> הוסף חיה</a>
            </div>
            <h3>החיות שלך</h3>
            <div class="row">
                <?php foreach ($pets as $pet): ?>
                    <div class="col-md-4 pet-card">
                        <div class="card">
                            <?php if (!empty($pet['photo'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($pet['photo']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="card-img-top">
                            <?php else: ?>
                                <img src="../images/catdogUSER.png" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="card-img-top">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($pet['name']); ?></h5>
                                <p class="card-text"><strong>סוג:</strong> <?php echo htmlspecialchars($pet['type']); ?></p>
                                <p class="card-text"><strong>גזע:</strong> <?php echo !empty($pet['breed']) ? htmlspecialchars($pet['breed']) : '-'; ?></p>
                                <form method="POST" action="" class="mt-2">
                                    <input type="hidden" name="delete_animal_id" value="<?php echo $pet['animal_id']; ?>">
                                    <button type="submit" class="btn btn-danger">מחק</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Transporter Ads Section -->
        <div class="transporter-ads-section mt-4">
            <div class="d-grid gap-2 mb-3">
                <a href="add_transporter_host_pet/add_transporter_screen.php" class="btn btn-primary"><i class="fas fa-car"></i> הוסף מודעת מוביל</a>
            </div>
            <h3>מודעות המוביל שלך</h3>
            <div class="row">
                <?php foreach ($transporterAds as $ad): ?>
                    <div class="col-md-4 transporter-ad-card">
                        <div class="card">
                            <div class="card-body">
                                <?php if (!empty($ad['photo'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($ad['photo']); ?>" alt="תמונת מודעה" class="img-thumbnail">
                                <?php else: ?>
                                    <img src="../images/catdogUSER.png" alt="תמונת מודעה" class="img-thumbnail">
                                <?php endif; ?>
                                <p class="card-title"><strong>אזור:</strong> <?php echo htmlspecialchars($ad['service_region']); ?></p>
                                <p class="card-text"><strong>טלפון:</strong> <?php echo htmlspecialchars($ad['phone']); ?></p>
                                <p class="card-text"><strong>אודות:</strong> <?php echo htmlspecialchars($ad['about']); ?></p>

                                <!-- Edit and Delete Buttons -->
                                <div class="d-flex justify-content-between mt-2">
                                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $ad['transporter_id']; ?>">ערוך</button>
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="delete_ad_id" value="<?php echo $ad['transporter_id']; ?>">
                                        <button type="submit" class="btn btn-danger">מחק</button>
                                    </form>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?php echo $ad['transporter_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $ad['transporter_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel<?php echo $ad['transporter_id']; ?>">ערוך מודעה</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="" enctype="multipart/form-data">
                                                    <input type="hidden" name="edit_ad_id" value="<?php echo $ad['transporter_id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="edit_phone<?php echo $ad['transporter_id']; ?>" class="form-label">טלפון</label>
                                                        <input type="text" class="form-control" id="edit_phone<?php echo $ad['transporter_id']; ?>" name="edit_phone" value="<?php echo htmlspecialchars($ad['phone']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_service_region<?php echo $ad['transporter_id']; ?>" class="form-label">אזור שירות</label>
                                                        <select class="form-select" id="edit_service_region<?php echo $ad['transporter_id']; ?>" name="edit_service_region" required>
                                                            <option selected disabled value="">בחר...</option>
                                                            <option value="מרכז" <?php echo ($ad['service_region'] == 'מרכז') ? 'selected' : ''; ?>>מרכז</option>
                                                            <option value="דרום" <?php echo ($ad['service_region'] == 'דרום') ? 'selected' : ''; ?>>דרום</option>
                                                            <option value="צפון" <?php echo ($ad['service_region'] == 'צפון') ? 'selected' : ''; ?>>צפון</option>
                                                            <option value="שפלה" <?php echo ($ad['service_region'] == 'שפלה') ? 'selected' : ''; ?>>שפלה</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_about<?php echo $ad['transporter_id']; ?>" class="form-label">אודות</label>
                                                        <textarea class="form-control" id="edit_about<?php echo $ad['transporter_id']; ?>" name="edit_about" rows="3" required><?php echo htmlspecialchars($ad['about']); ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_photo<?php echo $ad['transporter_id']; ?>" class="form-label">תמונה</label>
                                                        <input type="file" class="form-control" id="edit_photo<?php echo $ad['transporter_id']; ?>" name="edit_photo" accept="image/*">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">שמור</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

<!-- Host Ads Section -->
<div class="host-ads-section mt-4">
    <div class="d-grid gap-2 mb-3">
        <a href="./add_transporter_host_pet/add_host_screen.php" class="btn btn-primary"><i class="fas fa-home"></i> הוסף מודעת אירוח</a>
    </div>
    <h3>מודעות האירוח שלך</h3>
    <div class="row">
        <?php foreach ($hostAds as $ad): ?>
            <div class="col-md-4 host-ad-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($ad['type_of_property']); ?></h5>
                        <p class="card-text"><strong>אזור שירות:</strong> <?php echo htmlspecialchars($ad['service_region']); ?></p>
                        <p class="card-text"><strong>טלפון:</strong> <?php echo htmlspecialchars($ad['phone']); ?></p>
                        <p class="card-text"><strong>סוג החיה שמוכן לארח:</strong> <?php echo htmlspecialchars($ad['type_of_animal']); ?></p>
                        <p class="card-text"><strong>בע"ח נוספים:</strong> <?php echo $ad['additional_animals'] ? 'כן' : 'לא'; ?></p>
                        <p class="card-text"><strong>אודות:</strong> <?php echo htmlspecialchars($ad['about']); ?></p>
                        <?php if (!empty($ad['photo'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($ad['photo']); ?>" alt="תמונת אירוח" class="img-thumbnail">
                        <?php else: ?>
                            <img src="../images/defaultImage.png" alt="תמונת אירוח" class="img-thumbnail">
                        <?php endif; ?>

                        <!-- Edit and Delete Buttons -->
                        <div class="d-flex justify-content-between mt-2">
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editHostModal<?php echo $ad['host_id']; ?>">ערוך</button>
                            <form method="POST" action="">
                                <input type="hidden" name="delete_host_id" value="<?php echo $ad['host_id']; ?>">
                                <button type="submit" class="btn btn-danger">מחק</button>
                            </form>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editHostModal<?php echo $ad['host_id']; ?>" tabindex="-1" aria-labelledby="editHostModalLabel<?php echo $ad['host_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editHostModalLabel<?php echo $ad['host_id']; ?>">ערוך מודעה</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="edit_host_id" value="<?php echo $ad['host_id']; ?>">
                                            <div class="mb-3">
                                                <label for="edit_host_phone<?php echo $ad['host_id']; ?>" class="form-label">טלפון</label>
                                                <input type="text" class="form-control" id="edit_host_phone<?php echo $ad['host_id']; ?>" name="edit_host_phone" value="<?php echo htmlspecialchars($ad['phone']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_service_region<?php echo $ad['host_id']; ?>" class="form-label">אזור שירות</label>
                                                <select class="form-select" id="edit_service_region<?php echo $ad['host_id']; ?>" name="edit_service_region" required>
                                                    <option selected disabled value="">בחר...</option>
                                                    <option value="מרכז" <?php echo ($ad['service_region'] == 'מרכז') ? 'selected' : ''; ?>>מרכז</option>
                                                    <option value="דרום" <?php echo ($ad['service_region'] == 'דרום') ? 'selected' : ''; ?>>דרום</option>
                                                    <option value="צפון" <?php echo ($ad['service_region'] == 'צפון') ? 'selected' : ''; ?>>צפון</option>
                                                    <option value="שפלה" <?php echo ($ad['service_region'] == 'שפלה') ? 'selected' : ''; ?>>שפלה</option>
                                                </select>
                                            </div>
                                            <label for="edit_property_type<?php echo $ad['host_id']; ?>" class="form-label">סוג מגורים</label>
                                                <select class="form-select" id="edit_property_type<?php echo $ad['host_id']; ?>" name="edit_property_type" required>
                                                    <option selected disabled value="">בחר...</option>
                                                    <option value="דירה עם חצר" <?php echo ($ad['type_of_property'] == 'דירה עם חצר') ? 'selected' : ''; ?>>דירה עם חצר</option>
                                                    <option value="דירה בלי חצר" <?php echo ($ad['type_of_property'] == 'דירה בלי חצר') ? 'selected' : ''; ?>>דירה בלי חצר</option>
                                                    <option value="בית פרטי" <?php echo ($ad['type_of_property'] == 'בית פרטי') ? 'selected' : ''; ?>>בית פרטי</option>
                                                </select>
                                            <label for="edit_animal_type<?php echo $ad['host_id']; ?>" class="form-label">סוג החיה שמוכן לארח</label>
                                                <select class="form-select" id="edit_animal_type<?php echo $ad['host_id']; ?>" name="edit_animal_type" required>
                                                    <option selected disabled value="">בחר...</option>
                                                    <option value="כלב" <?php echo ($ad['type_of_animal'] == 'כלב') ? 'selected' : ''; ?>>כלב</option>
                                                    <option value="חתול" <?php echo ($ad['type_of_animal'] == 'חתול') ? 'selected' : ''; ?>>חתול</option>
                                                    <option value="אחר" <?php echo ($ad['type_of_animal'] == 'אחר') ? 'selected' : ''; ?>>אחר</option>
                                                </select>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="edit_additional_animals<?php echo $ad['host_id']; ?>" name="edit_additional_animals" <?php echo $ad['additional_animals'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="edit_additional_animals<?php echo $ad['host_id']; ?>">בע"ח נוספים</label>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_host_about<?php echo $ad['host_id']; ?>" class="form-label">אודות</label>
                                                <textarea class="form-control" id="edit_host_about<?php echo $ad['host_id']; ?>" name="edit_host_about" rows="3" required><?php echo htmlspecialchars($ad['about']); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_host_photo<?php echo $ad['host_id']; ?>" class="form-label">תמונה</label>
                                                <input type="file" class="form-control" id="edit_host_photo<?php echo $ad['host_id']; ?>" name="edit_host_photo" accept="image/*">
                                            </div>
                                            <button type="submit" class="btn btn-primary">שמור</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add any jQuery interactivity here if needed
        });
    </script>
</body>
</html>

<?php include 'header_footer/footer.php'; ?>