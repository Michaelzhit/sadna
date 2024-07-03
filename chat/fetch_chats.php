<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../config.php';

// Get the from_user_id and to_user_id from the query parameters
$fromUserId = $_SESSION['user_id'];
$toUserId = isset($_GET['to_user_id']) ? intval($_GET['to_user_id']) : 0;

// Fetch the chat messages
$query = "SELECT * FROM Messages WHERE (from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) ORDER BY timestamp ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $fromUserId, $toUserId, $toUserId, $fromUserId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($messages);
?>
