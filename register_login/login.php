<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include database configuration
    include '../config.php';

    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password']; 
    
    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        // Bind result variables
        $stmt->bind_result($userId, $firstName, $lastName, $dbPassword);
        $stmt->fetch();

        // Verify password
        if ($password === $dbPassword) {
            // Password is correct, start a new session
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;

            // Redirect to the profile page
            header("Location: ../home_screen.php");
            exit();
        } else {
            // Invalid password
            $loginError = "אימייל או סיסמה שגויים.";
        }
    } else {
        // No user found with that email
        $loginError = "אימייל או סיסמה שגויים.";
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
    <title>התחברות</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="height: 100vh;">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center">התחברות לחשבון</h3>
                        <?php if (isset($loginError)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $loginError; ?>
                            </div>
                        <?php endif; ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">כתובת אימייל</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">סיסמה</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">התחבר</button>
                            </div>
                            <div class="mt-3 text-center">
                                אין לך חשבון? <a href="register.php">הירשם עכשיו</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include '../header_footer/footer.php'; ?>
