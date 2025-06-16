<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$type = $_GET['type'] ?? 'bookings';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

try {
    switch ($type) {
        case 'events':
            exportEvents($pdo, $output, $start_date, $end_date);
            break;
        case 'bookings':
            exportBookings($pdo, $output, $start_date, $end_date);
            break;
        case 'revenue':
            exportRevenue($pdo, $output, $start_date, $end_date);
            break;
        case 'users':
            exportUsers($pdo, $output, $start_date, $end_date);
            break;
        default:
            throw new Exception('Invalid report type');
    }
} catch (Exception $e) {
    // Clear any output and send error
    if (ob_get_level()) {
        ob_clean();
    }
    header('Content-Type: text/plain');
    echo 'Error generating report: ' . $e->getMessage();
}

fclose($output);

function exportEvents($pdo, $output, $start_date, $end_date) {
    // CSV headers
    fputcsv($output, [
        'ID', 'Title', 'Category', 'Description', 'Venue', 'City', 'Region',
        'Date', 'Price', 'Available Tickets', 'Organizer', 'Created At'
    ]);
    
    $sql = "
        SELECT e.*, u.name as organizer_name 
        FROM events e 
        LEFT JOIN users u ON e.organizer_id = u.id 
        WHERE 1=1
    ";
    $params = [];
    
    if ($start_date) {
        $sql .= " AND e.date >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $sql .= " AND e.date <= ?";
        $params[] = $end_date . ' 23:59:59';
    }
    
    $sql .= " ORDER BY e.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['title'],
            $row['category'],
            $row['description'],
            $row['venue'],
            $row['city'],
            $row['region'],
            $row['date'],
            $row['price'],
            $row['available_tickets'],
            $row['organizer_name'] ?: 'N/A',
            $row['created_at']
        ]);
    }
}

function exportBookings($pdo, $output, $start_date, $end_date) {
    // CSV headers
    fputcsv($output, [
        'Booking ID', 'Event Title', 'Attendee Name', 'Attendee Email', 'Attendee Phone',
        'Quantity', 'Total Amount', 'Status', 'Ticket Number', 'Booking Date'
    ]);
    
    $sql = "
        SELECT b.*, e.title as event_title 
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE 1=1
    ";
    $params = [];
    
    if ($start_date) {
        $sql .= " AND b.created_at >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $sql .= " AND b.created_at <= ?";
        $params[] = $end_date . ' 23:59:59';
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['event_title'],
            $row['attendee_name'],
            $row['attendee_email'],
            $row['attendee_phone'] ?: 'N/A',
            $row['quantity'],
            $row['total_amount'],
            $row['status'],
            $row['ticket_number'] ?: 'N/A',
            $row['created_at']
        ]);
    }
}

function exportRevenue($pdo, $output, $start_date, $end_date) {
    // CSV headers
    fputcsv($output, [
        'Event Title', 'Event Date', 'Total Bookings', 'Confirmed Bookings',
        'Total Revenue', 'Confirmed Revenue', 'Average Ticket Price'
    ]);
    
    $sql = "
        SELECT 
            e.title,
            e.date,
            COUNT(b.id) as total_bookings,
            COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as confirmed_bookings,
            SUM(b.total_amount) as total_revenue,
            SUM(CASE WHEN b.status = 'confirmed' THEN b.total_amount ELSE 0 END) as confirmed_revenue,
            AVG(b.total_amount / b.quantity) as avg_ticket_price
        FROM events e 
        LEFT JOIN bookings b ON e.id = b.event_id 
        WHERE 1=1
    ";
    $params = [];
    
    if ($start_date) {
        $sql .= " AND e.date >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $sql .= " AND e.date <= ?";
        $params[] = $end_date . ' 23:59:59';
    }
    
    $sql .= " GROUP BY e.id, e.title, e.date ORDER BY e.date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['title'],
            $row['date'],
            $row['total_bookings'] ?: 0,
            $row['confirmed_bookings'] ?: 0,
            number_format($row['total_revenue'] ?: 0, 2),
            number_format($row['confirmed_revenue'] ?: 0, 2),
            number_format($row['avg_ticket_price'] ?: 0, 2)
        ]);
    }
}

function exportUsers($pdo, $output, $start_date, $end_date) {
    // CSV headers
    fputcsv($output, [
        'User ID', 'Name', 'Email', 'Role', 'Total Bookings', 'Total Spent',
        'Registration Date'
    ]);
    
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.email,
            u.role,
            COUNT(b.id) as total_bookings,
            SUM(CASE WHEN b.status = 'confirmed' THEN b.total_amount ELSE 0 END) as total_spent,
            u.created_at
        FROM users u 
        LEFT JOIN bookings b ON u.id = b.user_id 
        WHERE 1=1
    ";
    $params = [];
    
    if ($start_date) {
        $sql .= " AND u.created_at >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $sql .= " AND u.created_at <= ?";
        $params[] = $end_date . ' 23:59:59';
    }
    
    $sql .= " GROUP BY u.id, u.name, u.email, u.role, u.created_at ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['role'],
            $row['total_bookings'] ?: 0,
            number_format($row['total_spent'] ?: 0, 2),
            $row['created_at']
        ]);
    }
}
?>