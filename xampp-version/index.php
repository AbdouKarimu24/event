<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get current page
$page = $_GET['page'] ?? 'home';
$allowed_pages = ['home', 'events', 'event-details', 'cart', 'dashboard', 'login', 'register'];
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($page); ?> - EventZon</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <?php
        $page_file = "pages/{$page}.php";
        if (file_exists($page_file)) {
            include $page_file;
        } else {
            include 'pages/404.php';
        }
        ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>