<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Include database configuration
include '../config.php';

$userId = $_SESSION['user_id'];

// Fetch the list of unique chat partners
$query = "
    SELECT 
        U.user_id, U.first_name, U.last_name, U.profile_picture, M.latest_message, M.timestamp 
    FROM 
        Users U 
    INNER JOIN 
        (SELECT 
            CASE 
                WHEN from_user_id = ? THEN to_user_id 
                ELSE from_user_id 
            END AS chat_partner, 
            MAX(timestamp) as timestamp, 
            (SELECT content FROM Messages WHERE (from_user_id = chat_partner AND to_user_id = ?)
             OR (from_user_id = ? AND to_user_id = chat_partner) ORDER BY timestamp DESC LIMIT 1) AS latest_message
         FROM 
            Messages 
         WHERE 
            from_user_id = ? OR to_user_id = ? 
         GROUP BY 
            chat_partner) M 
    ON 
        U.user_id = M.chat_partner 
    ORDER BY 
        M.timestamp DESC";
        
$stmt = $conn->prepare($query);
$stmt->bind_param("iiiii", $userId, $userId, $userId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

$chats = [];
while ($row = $result->fetch_assoc()) {
    $chats[] = $row;
}

$stmt->close();
$conn->close();
?>
<?php include '../header_footer/header.php'; ?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>הצ'אטים שלי</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .chat-list {
            margin-top: 20px;
        }
        .chat-item {
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .profile-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            margin-left: 15px;
        }
        .chat-info {
            flex: 1;
        }
        .chat-info h5 {
            margin: 0;
            font-size: 1rem;
        }
        .chat-info p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #666;
        }
        .chat-info small {
            color: #999;
        }
    </style>
</head>
<body>
    <header class="bg-light py-3 mb-4">
        <div class="container">
            <h1 class="text-center">הצ'אטים שלי</h1>
        </div>
    </header>
    <main class="container">
        <div class="chat-list">
            <?php foreach ($chats as $chat): ?>
                <div class="chat-item">
                    <?php if (!empty($chat['profile_picture'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($chat['profile_picture']); ?>" class="profile-img" alt="תמונת פרופיל של <?php echo htmlspecialchars($chat['first_name'] . ' ' . $chat['last_name']); ?>">
                    <?php else: ?>
                        <img src="../../images/defaultImage.png" class="profile-img" alt="תמונת פרופיל ברירת מחדל">
                    <?php endif; ?>
                    <div class="chat-info">
                        <h5><?php echo htmlspecialchars($chat['first_name'] . ' ' . $chat['last_name']); ?></h5>
                        <p><?php echo html_entity_decode($chat['latest_message']); ?></p>
                        <small><?php echo htmlspecialchars($chat['timestamp']); ?></small>
                    </div>
                    <a href="chat.php?to_user_id=<?php echo htmlspecialchars($chat['user_id']); ?>" class="btn btn-primary">פתח צ'אט</a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include '../header_footer/footer.php'; ?>
