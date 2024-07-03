<?php
session_start();

// Reference to the login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Include the database configuration file
include '../config.php';

// Get the from_user_id and to_user_id from the URL parameters
$fromUserId = $_SESSION['user_id'];
$toUserId = isset($_GET['to_user_id']) ? intval($_GET['to_user_id']) : 0;

// Retrieve the username from the database and store it in the session
$stmt = $conn->prepare("SELECT first_name, last_name FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $toUserId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$toUserName = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['to_user_name'] = $toUserName;
$stmt->close();

// Mark messages as read when the user opens the chat
$updateQuery = "UPDATE Messages SET is_read = 1 WHERE from_user_id = ? AND to_user_id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("ii", $toUserId, $fromUserId);
$updateStmt->execute();
$updateStmt->close();

// Handling a new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']);
    $messageType = $_POST['type'] ?? 'text';
    $query = "INSERT INTO Messages (from_user_id, to_user_id, content, timestamp, type) VALUES (?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $fromUserId, $toUserId, $message, $messageType);
    if ($stmt->execute()) {
        // Refresh to display the new message
        header("Location: chat.php?to_user_id=$toUserId");
        exit();
    } else {
        $error = "שליחת ההודעה נכשלה.";
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
    <title>שיחה עם <?php echo $_SESSION['to_user_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .chat-container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .chat-header {
            padding: 15px;
            background-color: #007bff;
            color: #fff;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            text-align: center;
        }
        .chat-messages {
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 20px;
        }
        .message.sent {
            text-align: left;
        }
        .message.received {
            text-align: right;
        }
        .message p {
            display: inline-block;
            padding: 10px;
            border-radius: 20px;
            max-width: 70%;
        }
        .message.sent p {
            background-color: #007bff;
            color: #fff;
        }
        .message.received p {
            background-color: #f1f1f1;
        }
        .message.payment p {
            background-color: #ffcc00;
            color: #000;
        }
        .chat-input {
            display: flex;
            border-top: 1px solid #dee2e6;
        }
        .chat-input textarea {
            flex: 1;
            border: none;
            padding: 15px;
            border-radius: 0;
            resize: none;
        }
        .chat-input button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 15px;
        }
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="loading-spinner" id="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">טוען...</span>
        </div>
    </div>
    <div class="chat-container">
        <div class="chat-header">
            <h3>שיחה עם <?php echo $_SESSION['to_user_name']; ?></h3>
        </div>
        <div class="chat-messages" id="chat-messages">
            
        </div>
        <form class="chat-input" id="chat-form" action="" method="POST">
            <textarea name="message" placeholder="הקלד את ההודעה כאן..." required></textarea>
            <input type="hidden" name="type" value="text">
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
        <form class="chat-input" id="payment-form" action="send_payment.php" method="POST" style="display: none;">
            <input type="text" name="amount" placeholder="הזן סכום בשקלים" required>
            <input type="hidden" name="to_user_id" value="<?php echo $toUserId; ?>">
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
        <button id="send-payment" class="btn btn-warning" style="margin: 10px;">שלח קישור לתשלום</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function fetchMessages() {
        $.ajax({
            url: 'fetch_chats.php',
            method: 'GET',
            data: { to_user_id: <?php echo $toUserId; ?> },
            dataType: 'json',
            success: function(data) {
                const chatMessages = $('#chat-messages');
                chatMessages.empty();
                data.forEach(message => {
                    const messageClass = message.from_user_id == <?php echo $fromUserId; ?> ? 'sent' : 'received';
                    const typeClass = message.type === 'payment' ? 'payment' : '';
                    const messageItem = `
                        <div class="message ${messageClass} ${typeClass}">
                            <p>${message.content}</p>
                        </div>`;
                    chatMessages.append(messageItem);
                });
                chatMessages.scrollTop(chatMessages.prop("scrollHeight"));
            }
        });
    }

    $(document).ready(function() {
        fetchMessages();
        setInterval(fetchMessages, 2000); // Fetch messages every 2 seconds

        $('#chat-form').on('submit', function(event) {
            event.preventDefault();
            $.ajax({
                url: '',
                method: 'POST',
                data: $(this).serialize(),
                success: function() {
                    fetchMessages(); // Immediately fetch messages after sending a new one
                    $('textarea[name="message"]').val(''); // Clear the textarea
                }
            });
        });

        $('#send-payment').on('click', function() {
            $('#chat-form').hide();
            $('#payment-form').show();
        });

        $('#payment-form').on('submit', function(event) {
            event.preventDefault();
            $('#loading-spinner').show(); // Show the loading spinner
            const returnUrl = encodeURIComponent(window.location.href); // Get the current page URL

            $.ajax({
                url: 'send_payment.php',
                method: 'POST',
                data: $(this).serialize() + '&return_url=' + returnUrl, // Include the return URL
                success: function(response) {
                    const res = JSON.parse(response);
                    $('#loading-spinner').hide(); // Hide the loading spinner
                    if (res.status === 'success') {
                        fetchMessages(); // Immediately fetch messages after sending a new one
                        $('#payment-form').hide();
                        $('#chat-form').show();
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    $('#loading-spinner').hide(); // Hide the loading spinner on error
                    alert('אירעה שגיאה. אנא נסה שוב.');
                }
            });
        });
    });
</script>
</body>
</html>
<?php include '../header_footer/footer.php'; ?>
