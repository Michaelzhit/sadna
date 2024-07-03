<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>התנדבות למען בעלי חיים</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            max-height: 7vh;
        }
        .container {
            padding-top: 5px;
            max-width: 800px;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 5px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
    margin-left: 10px;
    width: 60px;
    height: auto;
    background-color: transparent;
    mix-blend-mode: multiply;
            }
        .navbar-brand i {
            margin-left: 10px;
        }
        .navbar-nav {
            margin-right: auto; 
        }
        .navbar-nav .nav-item {
            margin-left: 15px;
        }
        .navbar-nav .nav-link {
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .navbar-nav .nav-link:hover {
            background-color: #e9ecef;
        }
        .navbar-nav .active .nav-link {
            background-color: #007bff;
            color: #fff;
        }
        .nav-link.disabled {
            pointer-events: none;
            color: #6c757d;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/pages/home_screen.php">
            <img src="/logo1.svg" alt="Logo">
            <i class="fas fa-user-circle"></i>
            <?php
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['first_name'])) {
                echo 'ברוך הבא, ' . htmlspecialchars($_SESSION['first_name']);
            } else {
                echo 'ברוך הבא, אורח';
            }
            ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/pages/transporters_screen.php">מובילים</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pages/hosts_screen.php">מארחי אומנה</a>
                </li>
                <li class="nav-item position-relative">
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<a class="nav-link" href="/pages/profile_screen.php">פרופיל</a>';
                    } else {
                        echo '<a class="nav-link disabled" href="#">פרופיל</a>';
                    }
                    ?>
                <li class="nav-item position-relative">
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<a class="nav-link" href="/pages/chat/my_chats.php">צ\'אטים</a>';
                    } else {
                        echo '<a class="nav-link disabled" href="#">צ\'אטים</a>';
                    }
                    ?>
                    <span id="unread-messages-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;"></span>
                </li>
                <li class="nav-item">
                    <?php
                    if (isset($_SESSION['first_name'])) {
                        echo '<a class="nav-link" href="/pages/register_login/logout.php">התנתקות</a>';
                    } else {
                        echo '<a class="nav-link" href="/pages/register_login/login.php">התחברות</a>';
                    }
                    ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
    function checkNewMessages() {
        <?php
        if (isset($_SESSION['user_id'])) {
            echo '
            $.ajax({
                url: "/pages/header_footer/check_new_messages.php",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.new_messages > 0) {
                        $("#unread-messages-badge").text(data.new_messages).show();
                    } else {
                        $("#unread-messages-badge").hide();
                    }
                }
            });
            ';
        }
        ?>
    }

    $(document).ready(function() {
        checkNewMessages();
        setInterval(checkNewMessages, 2000); // Check for new messages every 2 seconds
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>