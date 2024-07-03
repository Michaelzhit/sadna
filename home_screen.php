<?php
session_start();
include 'config.php';

// Initialize filter variables
$filterServiceRegion = isset($_GET['serviceRegion']) ? $_GET['serviceRegion'] : '';
$filterHostCity = isset($_GET['host_city']) ? $_GET['host_city'] : '';
$filterHostRegion = isset($_GET['host_region']) ? $_GET['host_region'] : '';
$filterHostType = isset($_GET['host_type']) ? $_GET['host_type'] : '';
$filterAnimalType = isset($_GET['animal_type']) ? $_GET['animal_type'] : '';

$hostQuery = "SELECT H.phone, H.type_of_property, H.additional_animals, H.about, H.photo, U.user_id, U.first_name, U.last_name, U.email
              FROM HostAds H
              JOIN Users U ON H.user_id = U.user_id
              WHERE 1=1";

$params = [];
$types = '';

if (!empty($filterHostCity)) {
    $hostQuery .= " AND H.city = ?";
    $params[] = $filterHostCity;
    $types .= 's';
}

if (!empty($filterHostRegion)) {
    $hostQuery .= " AND H.region = ?";
    $params[] = $filterHostRegion;
    $types .= 's';
}

if (!empty($filterHostType)) {
    $hostQuery .= " AND H.type_of_property = ?";
    $params[] = $filterHostType;
    $types .= 's';
}

if (!empty($filterAnimalType)) {
    $hostQuery .= " AND H.animal_type = ?";
    $params[] = $filterAnimalType;
    $types .= 's';
}

$hostStmt = $conn->prepare($hostQuery);
if ($types) {
    $hostStmt->bind_param($types, ...$params);
}

$hostStmt->execute();
$hostResult = $hostStmt->get_result();

$hosts = [];
if ($hostResult->num_rows > 0) {
    while ($row = $hostResult->fetch_assoc()) {
        $hosts[] = $row;
    }
}

$conn->close();
?>
<?php include 'header_footer/header.php'; ?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>דף הבית</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <style>
        body {
            background: url('../homePageImgAnimal.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            color: #333;
        }
        .form-label {
            font-size: 0.875rem;
            margin-bottom: .25rem;
        }
        .form-control, .form-select {
            border-radius: .25rem;
            margin-bottom: .55rem;
            padding: .375rem;
            border: 1px solid #ced4da;
        }
        .btn-submit {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: .375rem 1rem;
            border-radius: .25rem;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 0.5rem;
        }
        .form-wrapper {
            max-width: 500px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 1.5rem;
            border-radius: 0.5rem;
            background-color: #fff;
        }
        .container {
            margin-top: 2rem;
        }
        .white-text {
            color: #fff;
        }
         .white-bullet {
        list-style-type: disc;
        color: white;
    }
    .white-bullet li::marker {
        color: white;
    }
    </style>
</head>
<body>
    <main class="container">
        <h2 class="white-text">ברוכים הבאים</h2>
        <br> 
        <h4 class="white-text">מי אנחנו? </h4>
        
<ul class="white-bullet" style="margin-left: 20px;">
    <li>
        <h5 class="white-text">אנחנו מסייעים לכם למצוא בקלות בתי אומנה שיעניקו בית חם לחיות המחמד בזמנים הקשים</h5>
    </li>
    <li>
        <h5 class="white-text">אנו מציעים חיבור למובילי בעלי חיים שיעזרו לכם לשנע את החברים הכי טובים שלכם אל היעד</h5>
    </li>
</ul>
        
        <p class="white-text">על רקע המצב במדינה, אנשים רבים נאלצים להתמודד עם חיפושים אחר פתרונות מידיים עבור בע"ח שלהם. חיילי מילואים הנקראים לשירות בהתראה קצרה ומשפחות המפונות מבתיהן נתקלים בקושי למצוא מענה הולם לצרכי בע"ח תחת לחץ הזמן והנסיבות. מתוך הבנת המצוקה, נולד הצורך בפלטפורמה ייעודית שתרכז במקום אחד מתנדבים המוכנים לסייע באומנה או בהובלת בע"ח ותאפשר יצירת קשר מיידי עימם, על מנת לתת מענה הולם וזמין.
        </p>
        
        <h2 class="white-text">רשימת המובילים</h2>
        <div class="form-wrapper mb-4">
            <form method="GET" action="transporters_screen.php">
                <div class="form-group">
                    <label for="serviceRegion" class="form-label">אזור שירות</label>
                    <select class="form-select" id="serviceRegion" name="serviceRegion" required>
                        <option selected disabled value="">בחר...</option>
                        <option value="מרכז" <?php echo $filterServiceRegion == 'מרכז' ? 'selected' : ''; ?>>מרכז</option>
                        <option value="דרום" <?php echo $filterServiceRegion == 'דרום' ? 'selected' : ''; ?>>דרום</option>
                        <option value="צפון" <?php echo $filterServiceRegion == 'צפון' ? 'selected' : ''; ?>>צפון</option>
                        <option value="שפלה" <?php echo $filterServiceRegion == 'שפלה' ? 'selected' : ''; ?>>שפלה</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-submit">חיפוש</button>
                </div>
            </form>
        </div>

        <h2 class="white-text">רשימת המארחים</h2>
        <div class="form-wrapper mb-4">
            <form method="GET" action="hosts_screen.php">
                <div class="form-group">
                    <label for="host_region" class="form-label">אזור</label>
                    <select class="form-select" id="host_region" name="host_region">
                        <option selected disabled value="">בחר...</option>
                        <option value="מרכז" <?php echo $filterHostRegion == 'מרכז' ? 'selected' : ''; ?>>מרכז</option>
                        <option value="דרום" <?php echo $filterHostRegion == 'דרום' ? 'selected' : ''; ?>>דרום</option>
                        <option value="צפון" <?php echo $filterHostRegion == 'צפון' ? 'selected' : ''; ?>>צפון</option>
                        <option value="שפלה" <?php echo $filterHostRegion == 'שפלה' ? 'selected' : ''; ?>>שפלה</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="host_type" class="form-label">סוג מגורים</label>
                    <select name="host_type" id="host_type" class="form-select">
                        <option selected disabled value="">בחר...</option>
                        <option value="דירה בלי חצר" <?php echo $filterHostType == 'apartment' ? 'selected' : ''; ?>>דירה בלי חצר</option>
                        <option value="דירה עם חצר" <?php echo $filterHostType == 'house' ? 'selected' : ''; ?>>דירה עם חצר</option>
                        <option value="בית פרטי" <?php echo $filterHostType == 'studio' ? 'selected' : ''; ?>>בית פרטי</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="animal_type" class="form-label">סוג חיה</label>
                    <select name="animal_type" id="animal_type" class="form-select">
                        <option selected disabled value="">בחר...</option>
                        <option value="כלב" <?php echo $filterAnimalType == 'dog' ? 'selected' : ''; ?>>כלב</option>
                        <option value="חתול" <?php echo $filterAnimalType == 'cat' ? 'selected' : ''; ?>>חתול</option>
                        <option value="אחר" <?php echo $filterAnimalType == 'bird' ? 'selected' : ''; ?>>אחר</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-submit">חיפוש</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">הרשמה הצליחה</h5>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">סגור</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check if the registration was successful
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('registration') === 'success') {
                $('#successModal').modal('show');
            }
        });
    </script>
</body>
</html>
<?php include 'header_footer/footer.php'; ?>
