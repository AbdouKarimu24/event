<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Initialize database
initializeDatabase();

// Get search parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$city = $_GET['city'] ?? '';
$sort = $_GET['sort'] ?? 'date';

// Build search query
$pdo = getDbConnection();
$where = ["status = 'active'"];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR description LIKE ? OR venue LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($category) {
    $where[] = "category = ?";
    $params[] = $category;
}

if ($city) {
    $where[] = "city = ?";
    $params[] = $city;
}

$orderBy = "ORDER BY ";
switch ($sort) {
    case 'price':
        $orderBy .= "price ASC";
        break;
    case 'popularity':
        $orderBy .= "available_tickets DESC";
        break;
    default:
        $orderBy .= "event_date ASC";
}

$whereClause = implode(' AND ', $where);
$sql = "SELECT e.*, u.first_name, u.last_name 
        FROM events e 
        LEFT JOIN users u ON e.organizer_id = u.id 
        WHERE $whereClause 
        $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get categories for filter
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM events WHERE status = 'active' ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get cities for filter
$citiesStmt = $pdo->query("SELECT DISTINCT city FROM events WHERE status = 'active' ORDER BY city");
$cities = $citiesStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventZon - Event Booking System</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo">EventZon</a>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="dashboard.php">My Bookings</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/index.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['first_name']) ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mt-4">
        <!-- Hero Section -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <h1>Welcome to EventZon</h1>
                <p class="text-muted">Discover and book amazing events in Cameroon</p>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-filters">
            <form method="GET" action="index.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="form-label">Search Events</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Search by title, description, or venue...">
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Category</label>
                        <select class="form-control form-select" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" 
                                        <?= $category === $cat ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($cat)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="form-label">City</label>
                        <select class="form-control form-select" name="city">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" 
                                        <?= $city === $c ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Sort By</label>
                        <select class="form-control form-select" name="sort">
                            <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Date</option>
                            <option value="price" <?= $sort === 'price' ? 'selected' : '' ?>>Price</option>
                            <option value="popularity" <?= $sort === 'popularity' ? 'selected' : '' ?>>Popularity</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Events Grid -->
        <div class="row">
            <?php if (empty($events)): ?>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h4>No Events Found</h4>
                            <p class="text-muted">Try adjusting your search criteria or check back later for new events.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card">
                            <?php if ($event['image_url']): ?>
                                <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($event['title']) ?>" 
                                     class="event-image">
                            <?php else: ?>
                                <div class="event-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5><?= htmlspecialchars($event['title']) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                                
                                <div class="mb-2">
                                    <span class="badge badge-primary"><?= ucfirst(htmlspecialchars($event['category'])) ?></span>
                                </div>
                                
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt text-muted"></i>
                                    <?= htmlspecialchars($event['venue']) ?>, <?= htmlspecialchars($event['city']) ?>
                                </div>
                                
                                <div class="mb-2">
                                    <i class="fas fa-calendar text-muted"></i>
                                    <?= date('M d, Y', strtotime($event['event_date'])) ?> at 
                                    <?= date('g:i A', strtotime($event['start_time'])) ?>
                                </div>
                                
                                <?php if ($event['first_name']): ?>
                                    <div class="mb-2">
                                        <i class="fas fa-user text-muted"></i>
                                        <?= htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="event-meta">
                                    <span class="event-price"><?= number_format($event['price']) ?> <?= htmlspecialchars($event['currency']) ?></span>
                                    <?php if ($event['available_tickets']): ?>
                                        <span class="badge badge-success"><?= $event['available_tickets'] ?> tickets left</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <a href="event_details.php?id=<?= $event['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (isLoggedIn()): ?>
                                    <a href="add_to_cart.php?event_id=<?= $event['id'] ?>" class="btn btn-success">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 EventZon. All rights reserved. Built with PHP & MySQL for XAMPP.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>