<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'delete_event':
                $event_id = (int)$_POST['event_id'];
                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$event_id]);
                echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
                break;
                
            case 'update_booking_status':
                $booking_id = (int)$_POST['booking_id'];
                $status = $_POST['status'];
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->execute([$status, $booking_id]);
                echo json_encode(['success' => true, 'message' => 'Booking status updated']);
                break;
                
            case 'get_analytics':
                $analytics = getAnalytics($pdo);
                echo json_encode($analytics);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

function getAnalytics($pdo) {
    $analytics = [];
    
    // Total events
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events");
    $analytics['total_events'] = $stmt->fetch()['total'];
    
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $analytics['total_bookings'] = $stmt->fetch()['total'];
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM bookings WHERE status = 'confirmed'");
    $analytics['total_revenue'] = $stmt->fetch()['total'] ?: 0;
    
    // Confirmed bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'");
    $analytics['confirmed_bookings'] = $stmt->fetch()['total'];
    
    // Events by category
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM events GROUP BY category ORDER BY count DESC");
    $analytics['events_by_category'] = $stmt->fetchAll();
    
    // Recent bookings
    $stmt = $pdo->query("
        SELECT b.*, e.title as event_title 
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        ORDER BY b.created_at DESC 
        LIMIT 5
    ");
    $analytics['recent_bookings'] = $stmt->fetchAll();
    
    // Monthly revenue
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
               SUM(total_amount) as revenue 
        FROM bookings 
        WHERE status = 'confirmed' 
        GROUP BY month 
        ORDER BY month DESC 
        LIMIT 12
    ");
    $analytics['monthly_revenue'] = $stmt->fetchAll();
    
    return $analytics;
}

// Get data based on page
switch ($page) {
    case 'events':
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        
        $sql = "SELECT e.*, u.name as organizer_name FROM events e LEFT JOIN users u ON e.organizer_id = u.id WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($category) {
            $sql .= " AND e.category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();
        break;
        
    case 'bookings':
        $status_filter = $_GET['status'] ?? '';
        $event_filter = $_GET['event'] ?? '';
        
        $sql = "
            SELECT b.*, e.title as event_title, e.date as event_date 
            FROM bookings b 
            JOIN events e ON b.event_id = e.id 
            WHERE 1=1
        ";
        $params = [];
        
        if ($status_filter) {
            $sql .= " AND b.status = ?";
            $params[] = $status_filter;
        }
        
        if ($event_filter) {
            $sql .= " AND b.event_id = ?";
            $params[] = $event_filter;
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();
        
        // Get events for filter dropdown
        $stmt = $pdo->query("SELECT id, title FROM events ORDER BY title");
        $events_for_filter = $stmt->fetchAll();
        break;
        
    case 'dashboard':
    default:
        $analytics = getAnalytics($pdo);
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventZon Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">EventZon Admin</h5>
                        <small class="text-light">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>" href="?page=dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page === 'events' ? 'active' : '' ?>" href="?page=events">
                                <i class="bi bi-calendar-event"></i> Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page === 'bookings' ? 'active' : '' ?>" href="?page=bookings">
                                <i class="bi bi-people"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page === 'reports' ? 'active' : '' ?>" href="?page=reports">
                                <i class="bi bi-bar-chart"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-light" href="../index.php">
                                <i class="bi bi-house"></i> View Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php
                        switch ($page) {
                            case 'events': echo 'Event Management'; break;
                            case 'bookings': echo 'Booking Management'; break;
                            case 'reports': echo 'Reports & Analytics'; break;
                            default: echo 'Dashboard'; break;
                        }
                        ?>
                    </h1>
                </div>

                <?php if ($page === 'dashboard'): ?>
                    <!-- Dashboard Content -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Events</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $analytics['total_events'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-calendar-event fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Bookings</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $analytics['total_bookings'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-people fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Confirmed</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $analytics['confirmed_bookings'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Revenue</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($analytics['total_revenue']) ?> XAF</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Events by Category</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($analytics['recent_bookings'] as $booking): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong><?= htmlspecialchars($booking['attendee_name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($booking['event_title']) ?></small>
                                            </div>
                                            <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Category Chart
                        const categoryData = <?= json_encode($analytics['events_by_category']) ?>;
                        const ctx = document.getElementById('categoryChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: categoryData.map(item => item.category),
                                datasets: [{
                                    data: categoryData.map(item => item.count),
                                    backgroundColor: [
                                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    </script>

                <?php elseif ($page === 'events'): ?>
                    <!-- Events Management -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="hidden" name="page" value="events">
                                <input type="text" name="search" class="form-control me-2" placeholder="Search events..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <select name="category" class="form-select me-2">
                                    <option value="">All Categories</option>
                                    <option value="music" <?= ($_GET['category'] ?? '') === 'music' ? 'selected' : '' ?>>Music</option>
                                    <option value="business" <?= ($_GET['category'] ?? '') === 'business' ? 'selected' : '' ?>>Business</option>
                                    <option value="technology" <?= ($_GET['category'] ?? '') === 'technology' ? 'selected' : '' ?>>Technology</option>
                                    <option value="arts" <?= ($_GET['category'] ?? '') === 'arts' ? 'selected' : '' ?>>Arts</option>
                                    <option value="sports" <?= ($_GET['category'] ?? '') === 'sports' ? 'selected' : '' ?>>Sports</option>
                                    <option value="food" <?= ($_GET['category'] ?? '') === 'food' ? 'selected' : '' ?>>Food</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="event-form.php" class="btn btn-success">
                                <i class="bi bi-plus"></i> Add New Event
                            </a>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th>Venue</th>
                                            <th>Price</th>
                                            <th>Tickets</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td><?= $event['id'] ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($event['title']) ?></strong>
                                                    <?php if ($event['organizer_name']): ?>
                                                        <br><small class="text-muted">by <?= htmlspecialchars($event['organizer_name']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= ucfirst($event['category']) ?></span>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($event['date'])) ?></td>
                                                <td><?= htmlspecialchars($event['venue']) ?>, <?= htmlspecialchars($event['city']) ?></td>
                                                <td><?= number_format($event['price']) ?> XAF</td>
                                                <td><?= $event['available_tickets'] ?></td>
                                                <td>
                                                    <a href="event-form.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button onclick="deleteEvent(<?= $event['id'] ?>)" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <a href="../pages/event-details.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-info" target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'bookings'): ?>
                    <!-- Bookings Management -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <form method="GET" class="d-flex">
                                <input type="hidden" name="page" value="bookings">
                                <select name="status" class="form-select me-2">
                                    <option value="">All Status</option>
                                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <select name="event" class="form-select me-2">
                                    <option value="">All Events</option>
                                    <?php foreach ($events_for_filter as $event): ?>
                                        <option value="<?= $event['id'] ?>" <?= ($_GET['event'] ?? '') == $event['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($event['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <button onclick="exportBookings()" class="btn btn-outline-success">
                                <i class="bi bi-download"></i> Export CSV
                            </button>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Attendee</th>
                                            <th>Event</th>
                                            <th>Date</th>
                                            <th>Quantity</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td>#<?= $booking['id'] ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($booking['attendee_name']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($booking['attendee_email']) ?></small>
                                                    <?php if ($booking['attendee_phone']): ?>
                                                        <br><small><?= htmlspecialchars($booking['attendee_phone']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($booking['event_title']) ?></td>
                                                <td><?= date('M j, Y', strtotime($booking['created_at'])) ?></td>
                                                <td><?= $booking['quantity'] ?></td>
                                                <td><?= number_format($booking['total_amount']) ?> XAF</td>
                                                <td>
                                                    <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                                        <?= ucfirst($booking['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button onclick="updateBookingStatus(<?= $booking['id'] ?>, 'confirmed')" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                <?= $booking['status'] === 'confirmed' ? 'disabled' : '' ?>>
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                        <button onclick="updateBookingStatus(<?= $booking['id'] ?>, 'pending')" 
                                                                class="btn btn-sm btn-outline-warning"
                                                                <?= $booking['status'] === 'pending' ? 'disabled' : '' ?>>
                                                            <i class="bi bi-clock"></i>
                                                        </button>
                                                        <button onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                <?= $booking['status'] === 'cancelled' ? 'disabled' : '' ?>>
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'reports'): ?>
                    <!-- Reports -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Generate Reports</h6>
                                </div>
                                <div class="card-body">
                                    <form id="reportForm">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="reportType" class="form-label">Report Type</label>
                                                <select id="reportType" class="form-select">
                                                    <option value="events">Events Report</option>
                                                    <option value="bookings">Bookings Report</option>
                                                    <option value="revenue">Revenue Report</option>
                                                    <option value="users">Users Report</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="startDate" class="form-label">Start Date</label>
                                                <input type="date" id="startDate" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="endDate" class="form-label">End Date</label>
                                                <input type="date" id="endDate" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" onclick="generateReport()" class="btn btn-primary d-block">
                                                    <i class="bi bi-file-earmark-text"></i> Generate Report
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue Trend</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="h4 text-primary"><?= $analytics['total_events'] ?></div>
                                            <div class="small">Total Events</div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="h4 text-success"><?= $analytics['total_bookings'] ?></div>
                                            <div class="small">Total Bookings</div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="h4 text-info"><?= $analytics['confirmed_bookings'] ?></div>
                                            <div class="small">Confirmed</div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="h4 text-warning"><?= number_format($analytics['total_revenue'] / 1000, 1) ?>K</div>
                                            <div class="small">Revenue (XAF)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Revenue Chart
                        const revenueData = <?= json_encode(array_reverse($analytics['monthly_revenue'])) ?>;
                        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                        new Chart(revenueCtx, {
                            type: 'line',
                            data: {
                                labels: revenueData.map(item => item.month),
                                datasets: [{
                                    label: 'Revenue (XAF)',
                                    data: revenueData.map(item => item.revenue),
                                    borderColor: '#36A2EB',
                                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    </script>

                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // AJAX Functions
        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                fetch('admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax=1&action=delete_event&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function updateBookingStatus(bookingId, status) {
            if (confirm(`Are you sure you want to mark this booking as ${status}?`)) {
                fetch('admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax=1&action=update_booking_status&booking_id=${bookingId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function exportBookings() {
            window.open('export.php?type=bookings', '_blank');
        }

        function generateReport() {
            const reportType = document.getElementById('reportType').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            const params = new URLSearchParams({
                type: reportType,
                start_date: startDate,
                end_date: endDate
            });
            
            window.open(`export.php?${params.toString()}`, '_blank');
        }
    </script>
</body>
</html>