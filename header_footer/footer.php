<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        footer {
            background-color: rgba(255, 255, 255, 0.8); 
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); 
        }
        .footer-text {
            background-color: rgba(0, 0, 0, 0.05); 
            padding: 0.5rem;
        }
        .footer-text a {
            color: #333; 
            text-decoration: none;
        }
        .footer-text a:hover {
            text-decoration: underline; /* Underline on hover for better visibility */
        }
    </style>
</head>
<body>
    <footer class="mt-auto">
        <div class="text-center footer-text">
            &copy; <?php echo date("Y"); ?> UserProfileApp. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>