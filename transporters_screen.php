<?php
session_start();
include 'config.php';

// Set UTF-8 encoding for the database connection
mysqli_set_charset($conn, "utf8mb4");

// Initialize filter variables
$filterCity = isset($_GET['city']) ? $_GET['city'] : '';
$filterServiceRegion = isset($_GET['serviceRegion']) ? $_GET['serviceRegion'] : '';

// Fetch transporter data with filtering
$transporterQuery = "SELECT T.phone, T.service_region, T.about, T.photo, U.user_id, U.first_name, U.last_name
                     FROM TransporterAds T
                     JOIN Users U ON T.user_id = U.user_id
                     WHERE 1=1";

// Initialize params and types
$params = [];
$types = '';

// Exclude the logged-in user's ads if a user is logged in
if (isset($_SESSION['user_id'])) {
    $transporterQuery .= " AND T.user_id != ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

if (!empty($filterCity)) {
    $transporterQuery .= " AND U.city = ?";
    $params[] = $filterCity;
    $types .= 's';
}

if (!empty($filterServiceRegion)) {
    $transporterQuery .= " AND T.service_region = ?";
    $params[] = $filterServiceRegion;
    $types .= 's';
}

$stmt = $conn->prepare($transporterQuery);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$transporterResult = $stmt->get_result();

$transporters = [];
if ($transporterResult->num_rows > 0) {
    while ($row = $transporterResult->fetch_assoc()) {
        $transporters[] = $row;
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
    <title>רשימת מובילים</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
        }
        .card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border-radius: 15px;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-top: -60px;
            margin-bottom: 10px;
        }
        .card-body {
            padding: 2rem;
            text-align: center;
        }
        .card-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .card-text {
            font-size: 0.9rem;
        }
        .contact-btn {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
        }
        .details {
            color: #007bff;
            cursor: pointer;
        }
        .more-text {
            display: none;
        }
    </style>
</head>
<body>
    <header class="bg-light py-3 mb-4">
        <div class="container">
            <h1 class="text-center">רשימת מובילים</h1>
        </div>
    </header>
    <main class="container">
        <div class="row">
            <?php foreach ($transporters as $transporter): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($transporter['photo'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($transporter['photo']); ?>" class="profile-img" alt="תמונת פרופיל של <?php echo htmlentities($transporter['first_name'] . ' ' . $transporter['last_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php else: ?>
                                <img src="../images/defaultImage.png" class="profile-img" alt="תמונת פרופיל">
                            <?php endif; ?>
                            <h5 class="card-title"><?php echo htmlentities($transporter['first_name'] . ' ' . $transporter['last_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="card-text"><strong>טלפון:</strong> <?php echo htmlentities($transporter['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="card-text"><strong>אזור שירות:</strong> <?php echo htmlentities($transporter['service_region'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="card-text">
                                <strong>על עצמי:</strong>
                                <?php 
                                $about = htmlentities($transporter['about'], ENT_QUOTES, 'UTF-8'); 
                                if (strlen($about) > 100) {
                                    echo mb_substr($about, 0, 100) . '<span class="more-text">' . mb_substr($about, 100) . '</span> <span class="details">קרא עוד</span>';
                                } else {
                                    echo $about;
                                }
                                ?>
                            </p>
                            <button class="btn btn-primary contact-btn" data-user-id="<?php echo htmlentities($transporter['user_id'], ENT_QUOTES, 'UTF-8'); ?>">צור קשר</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.details').forEach(function (detail) {
                detail.addEventListener('click', function () {
                    this.previousElementSibling.classList.toggle('more-text');
                    this.textContent = this.textContent === 'קרא עוד' ? 'קרא פחות' : 'קרא עוד';
                });
            });

            document.querySelectorAll('.contact-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        if (confirm('עליך להתחבר כדי ליצור קשר עם המוביל')) {
                            window.location.href = '/pages/register_login/login.php';
                        }
                    <?php else: ?>
                        const userId = this.getAttribute('data-user-id');
                        window.location.href = `/pages/chat/chat.php?to_user_id=${userId}`;
                    <?php endif; ?>
                });
            });
        });
    </script>
</body>
</html>
<?php include 'header_footer/footer.php'; ?>
