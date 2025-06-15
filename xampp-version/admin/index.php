<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireAdmin();

$pdo = getDbConnection();

// Get statistics
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$eventCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status = 'confirmed'")->fetchColumn();

// Recent events
$recentEvents = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent bookings
$recentBookings = $pdo->query("
    SELECT b.*, e.title as event_title, u.first_name, u.last_name 
    FROM bookings b 
    JOIN events e ON b.event_id = e.id 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EventZon</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="../index.php" class="logo">EventZon Admin</a>
                <ul class="nav-links">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="bookings.php">Bookings</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="database.php">Database Admin</a></li>
                    <li><a href="../index.php">View Site</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
            </div>
            <div class="card-body">
                <p>Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>! Here's an overview of your event booking system.</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h3><?= number_format($userCount) ?></h3>
                        <p class="text-muted">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar fa-2x text-success mb-2"></i>
                        <h3><?= number_format($eventCount) ?></h3>
                        <p class="text-muted">Total Events</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-ticket-alt fa-2x text-warning mb-2"></i>
                        <h3><?= number_format($bookingCount) ?></h3>
                        <p class="text-muted">Total Bookings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-money-bill fa-2x text-danger mb-2"></i>
                        <h3><?= number_format($revenue) ?> XAF</h3>
                        <p class="text-muted">Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Events -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-calendar-plus"></i> Recent Events</h4>
                        <a href="events.php" class="btn btn-primary btn-sm">Manage Events</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentEvents)): ?>
                            <p class="text-muted">No events found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEvents as $event): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($event['title']) ?></td>
                                                <td><?= date('M d, Y', strtotime($event['event_date'])) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $event['status'] === 'active' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($event['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-ticket-alt"></i> Recent Bookings</h4>
                        <a href="bookings.php" class="btn btn-primary btn-sm">Manage Bookings</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentBookings)): ?>
                            <p class="text-muted">No bookings found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Event</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBookings as $booking): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                                                <td><?= htmlspecialchars($booking['event_title']) ?></td>
                                                <td><?= number_format($booking['total_amount']) ?> <?= htmlspecialchars($booking['currency']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-bolt"></i> Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="events.php?action=create" class="btn btn-success btn-block">
                                    <i class="fas fa-plus"></i> Add New Event
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="bookings.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-list"></i> View All Bookings
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="users.php" class="btn btn-info btn-block">
                                    <i class="fas fa-users"></i> Manage Users
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="database.php" class="btn btn-warning btn-block">
                                    <i class="fas fa-database"></i> Database Admin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 EventZon Admin Panel. Built for XAMPP with PHP & MySQL.</p>
        </div>
    </footer>
</body>
</html>