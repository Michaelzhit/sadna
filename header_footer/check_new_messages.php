<?php
session_start();

// Include database configuration
include '../config.php';

// Get the current user ID from the session
$userId = $_SESSION['user_id'];

// Query to check for new messages
$query = "SELECT COUNT(*) AS new_messages FROM Messages WHERE to_user_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$newMessageCount = $row['new_messages'];

$stmt->close();
$conn->close();

// Return the count of new messages as JSON
echo json_encode(['new_messages' => $newMessageCount]);
?>
