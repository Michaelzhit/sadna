<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include database configuration
    include '../config.php';

    // Get form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password']; 

    // Check if the email already exists
    $checkStmt = $conn->prepare("SELECT email FROM Users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $registrationError = "חשבון עם דוא\"ל זה כבר קיים.";
    } else {
        // Prepare the SQL statement to insert user data
        $stmt = $conn->prepare("INSERT INTO Users (email, first_name, last_name, password, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $firstName, $lastName, $password, $phone);

        if ($stmt->execute()) {
            // Get the last inserted ID
            $userId = $stmt->insert_id;

            // Handle profile picture upload or use default image
            if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
                // Read the image file content into a variable
                $profilePicture = file_get_contents($_FILES['profilePicture']['tmp_name']);
            } else {
                $profilePicture = file_get_contents('../../images/defaultImage.png'); // Use default image
            }

            // Update the user's profile picture in the database
            $updateStmt = $conn->prepare("UPDATE Users SET profile_picture = ? WHERE user_id = ?");
            $updateStmt->bind_param("bi", $profilePicture, $userId); // 'b' for BLOB
            $updateStmt->send_long_data(0, $profilePicture);
            $updateStmt->execute();
            $updateStmt->close();

            // Set session variables
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;

            // Redirect to the home page with registration success
            header("Location: ../home_screen.php?registration=success");
            exit();
            
        } else {
            $registrationError = "שגיאה: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}
?>

<?php include '../header_footer/header.php'; ?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>הרשמה</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-title {
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .text-center a {
            text-decoration: none;
        }
        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="height: 100vh;">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center">הרשמה לחשבון</h3>
                        <?php if (isset($registrationError)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $registrationError; ?>
                            </div>
                        <?php endif; ?>
                        <form id="registerForm" action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">שם פרטי</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastName" class="form-label">שם משפחה</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">כתובת דוא"ל</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">מספר טלפון</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="profilePicture" class="form-label">תמונת פרופיל (אופציונלי)</label>
                                <input type="file" class="form-control" id="profilePicture" name="profilePicture" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">סיסמה</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">אימות סיסמה</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">הרשם</button>
                            </div>
                            <div class="mt-3 text-center">
                                כבר יש לך חשבון? <a href="login.php">התחבר כאן</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(event) {
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();
                
                if (password !== confirmPassword) {
                    alert('הסיסמאות אינן תואמות.');
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
