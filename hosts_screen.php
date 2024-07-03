<?php
session_start();
include 'config.php';
// Set UTF-8 encoding for the database connection
mysqli_set_charset($conn, "utf8mb4");


// Initialize filter variables
$filterHostCity = isset($_GET['host_city']) ? $_GET['host_city'] : '';
$filterHostRegion = isset($_GET['host_region']) ? $_GET['host_region'] : '';
$filterHostType = isset($_GET['host_type']) ? $_GET['host_type'] : '';
$filterAnimalType = isset($_GET['animal_type']) ? $_GET['animal_type'] : '';

// Fetch host data with filtering
$hostQuery = "SELECT H.phone, H.type_of_property, H.type_of_animal, H.additional_animals, H.service_region, H.about, H.photo, U.user_id, U.first_name, U.last_name, U.email
              FROM HostAds H
              JOIN Users U ON H.user_id = U.user_id
              WHERE 1=1";

// Initialize params and types
$params = [];
$types = '';

// Exclude the logged-in user's ads if a user is logged in
if (isset($_SESSION['user_id'])) {
    $hostQuery .= " AND H.user_id != ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

if (!empty($filterHostCity)) {
    $hostQuery .= " AND U.city = ?";
    $params[] = $filterHostCity;
    $types .= 's';
}

if (!empty($filterHostRegion)) {
    $hostQuery .= " AND H.service_region = ?";
    $params[] = $filterHostRegion;
    $types .= 's';
}

if (!empty($filterHostType)) {
    $hostQuery .= " AND H.type_of_property = ?";
    $params[] = $filterHostType;
    $types .= 's';
}

if (!empty($filterAnimalType)) {
    $hostQuery .= " AND H.type_of_animal = ?";
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
    <title>רשימת מארחים</title>
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
        .rating {
            margin-bottom: 10px;
            color: #ffc107;
        }
        .more-text {
            display: none;
        }
        .check-pet {
            display: block;
            margin-top: 10px;
        }
        .check-pet.has-animals {
            color: #fd7e14; 
        }
        .check-pet.no-animals {
            color: #28a745; 
        }
        .host-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .host-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .host-info img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-top: -60px;
            margin-bottom: 10px;
        }
        .host-info div {
            flex: 1;
        }
        .badge {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            padding: 5px 10px;
            margin-right: 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <header class="bg-light py-3 mb-4">
        <div class="container">
            <h1 class="text-center">רשימת מארחים</h1>
        </div>
    </header>
    <main class="container">
        <div class="row">
            <?php foreach ($hosts as $host): ?>
                <?php
                $userId = $host['user_id'];
                $hostPhoto = $host['photo'];
                $hasAdditionalAnimals = $host['additional_animals'] ? 'has-animals' : 'no-animals';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="host-info">
                                <?php if (!empty($hostPhoto)): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($hostPhoto); ?>" alt="תמונת אירוח">
                                <?php else: ?>
                                    <img src="../images/defaultImage.png" alt="תמונת אירוח">
                                <?php endif; ?>
                                <div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($host['first_name'] . ' ' . $host['last_name']); ?></h5>
                                </div>
                            </div>
                             <p class="card-text"><strong>טלפון:</strong> <?php echo htmlspecialchars($host['phone']); ?></p>
                            <p class="card-text"><strong>אזור שירות:</strong><?php echo htmlspecialchars($host['service_region']); ?></p>
                            <p class="card-text"><strong>סוג בית:</strong> <?php echo htmlspecialchars($host['type_of_property']); ?></p>
                            <p class="card-text"><strong>סוג חיה:</strong> <?php echo htmlspecialchars($host['type_of_animal']); ?></p>
                            <p class="check-pet <?php echo $hasAdditionalAnimals; ?>">
                                <i class="fas fa-paw"></i> 
                                <?php echo $host['additional_animals'] ? 'יש חיות נוספות' : 'אין חיות נוספות'; ?>
                            </p>
                            <p class="card-text">
                                <strong>על עצמי:</strong>
                                <?php 
                                $about = htmlspecialchars($host['about'], ENT_QUOTES, 'UTF-8'); 
                                if (strlen($about) > 100) {
                                    echo substr($about, 0, 100) . '<span class="more-text">' . substr($about, 100) . '</span> <span class="details">קרא עוד</span>';
                                } else {
                                    echo $about;
                                }
                                ?>
                            </p>
                            <button class="btn btn-primary contact-btn" data-user-id="<?php echo htmlspecialchars($host['user_id'], ENT_QUOTES, 'UTF-8'); ?>">צור קשר</button>
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
                        if (confirm('עליך להתחבר כדי ליצור קשר עם המארח. האם ברצונך לעבור לעמוד ההתחברות?')) {
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
