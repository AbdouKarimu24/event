<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireAdmin();

$pdo = getDbConnection();

// Export bookings as CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $filter_status = $_GET['status'] ?? '';
    $filter_event = $_GET['event'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR b.attendee_name LIKE ? OR b.ticket_number LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($filter_status)) {
        $where_conditions[] = "b.status = ?";
        $params[] = $filter_status;
    }
    
    if (!empty($filter_event)) {
        $where_conditions[] = "b.event_id = ?";
        $params[] = $filter_event;
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(b.booking_date) >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(b.booking_date) <= ?";
        $params[] = $date_to;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get bookings
    $sql = "SELECT b.ticket_number, b.booking_date, b.status, b.quantity, b.total_amount,
                   b.attendee_name, b.attendee_email, b.attendee_phone,
                   u.first_name, u.last_name, u.email as customer_email,
                   e.title as event_title, e.event_date, e.venue, e.city, e.category
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN events e ON b.event_id = e.id 
            $where_clause 
            ORDER BY b.booking_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="eventzon_bookings_' . date('Y-m-d') . '.csv"');
    
    // Create CSV content
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, [
        'Ticket Number',
        'Booking Date',
        'Status',
        'Customer First Name',
        'Customer Last Name',
        'Customer Email',
        'Attendee Name',
        'Attendee Email',
        'Attendee Phone',
        'Event Title',
        'Event Date',
        'Venue',
        'City',
        'Category',
        'Quantity',
        'Total Amount (XAF)'
    ]);
    
    // CSV data
    foreach ($bookings as $booking) {
        fputcsv($output, [
            $booking['ticket_number'],
            $booking['booking_date'],
            $booking['status'],
            $booking['first_name'],
            $booking['last_name'],
            $booking['customer_email'],
            $booking['attendee_name'],
            $booking['attendee_email'],
            $booking['attendee_phone'],
            $booking['event_title'],
            $booking['event_date'],
            $booking['venue'],
            $booking['city'],
            $booking['category'],
            $booking['quantity'],
            $booking['total_amount']
        ]);
    }
    
    fclose($output);
    exit;
}

// Generate comprehensive report
if (isset($_GET['report']) && $_GET['report'] === '1') {
    $start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
    $end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today
    
    // Get report data
    $report_data = [];
    
    // Overall statistics
    $stats_sql = "SELECT 
        COUNT(*) as total_bookings,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END) as total_revenue,
        SUM(quantity) as total_tickets,
        AVG(total_amount) as avg_booking_value
    FROM bookings 
    WHERE DATE(booking_date) BETWEEN ? AND ?";
    
    $stmt = $pdo->prepare($stats_sql);
    $stmt->execute([$start_date, $end_date]);
    $report_data['overall_stats'] = $stmt->fetch();
    
    // Top events by bookings
    $top_events_sql = "SELECT e.title, COUNT(*) as booking_count, SUM(b.total_amount) as revenue
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE DATE(b.booking_date) BETWEEN ? AND ?
        GROUP BY e.id, e.title 
        ORDER BY booking_count DESC 
        LIMIT 10";
    
    $stmt = $pdo->prepare($top_events_sql);
    $stmt->execute([$start_date, $end_date]);
    $report_data['top_events'] = $stmt->fetchAll();
    
    // Bookings by city
    $city_stats_sql = "SELECT e.city, COUNT(*) as booking_count, SUM(b.total_amount) as revenue
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE DATE(b.booking_date) BETWEEN ? AND ? AND e.city IS NOT NULL
        GROUP BY e.city 
        ORDER BY booking_count DESC";
    
    $stmt = $pdo->prepare($city_stats_sql);
    $stmt->execute([$start_date, $end_date]);
    $report_data['city_stats'] = $stmt->fetchAll();
    
    // Bookings by category
    $category_stats_sql = "SELECT e.category, COUNT(*) as booking_count, SUM(b.total_amount) as revenue
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE DATE(b.booking_date) BETWEEN ? AND ? AND e.category IS NOT NULL
        GROUP BY e.category 
        ORDER BY booking_count DESC";
    
    $stmt = $pdo->prepare($category_stats_sql);
    $stmt->execute([$start_date, $end_date]);
    $report_data['category_stats'] = $stmt->fetchAll();
    
    // Daily bookings trend
    $daily_trend_sql = "SELECT DATE(booking_date) as booking_date, 
                               COUNT(*) as bookings, 
                               SUM(total_amount) as revenue
        FROM bookings 
        WHERE DATE(booking_date) BETWEEN ? AND ?
        GROUP BY DATE(booking_date) 
        ORDER BY booking_date";
    
    $stmt = $pdo->prepare($daily_trend_sql);
    $stmt->execute([$start_date, $end_date]);
    $report_data['daily_trend'] = $stmt->fetchAll();
    
    // Set headers for HTML report
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="eventzon_report_' . $start_date . '_to_' . $end_date . '.html"');
    
    // Generate HTML report
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EventZon Report - <?php echo $start_date; ?> to <?php echo $end_date; ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">EventZon Analytics Report</h1>
                    <p class="text-gray-600 mt-2">Period: <?php echo date('F j, Y', strtotime($start_date)); ?> - <?php echo date('F j, Y', strtotime($end_date)); ?></p>
                    <p class="text-sm text-gray-500">Generated on <?php echo date('F j, Y \a\t g:i A'); ?></p>
                </div>
                
                <!-- Overall Statistics -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Overall Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-blue-50 rounded-lg p-6">
                            <div class="flex items-center">
                                <i class="fas fa-ticket-alt text-blue-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-blue-600">Total Bookings</p>
                                    <p class="text-2xl font-bold text-blue-900"><?php echo number_format($report_data['overall_stats']['total_bookings']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-green-50 rounded-lg p-6">
                            <div class="flex items-center">
                                <i class="fas fa-money-bill-wave text-green-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-green-600">Total Revenue</p>
                                    <p class="text-2xl font-bold text-green-900"><?php echo number_format($report_data['overall_stats']['total_revenue']); ?> XAF</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 rounded-lg p-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-purple-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-purple-600">Confirmed Bookings</p>
                                    <p class="text-2xl font-bold text-purple-900"><?php echo number_format($report_data['overall_stats']['confirmed_bookings']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 rounded-lg p-6">
                            <div class="flex items-center">
                                <i class="fas fa-chart-line text-yellow-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-yellow-600">Avg. Booking Value</p>
                                    <p class="text-2xl font-bold text-yellow-900"><?php echo number_format($report_data['overall_stats']['avg_booking_value']); ?> XAF</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Events -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Top Events by Bookings</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($report_data['top_events'] as $event): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($event['booking_count']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($event['revenue']); ?> XAF
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Performance by City -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Performance by City</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($report_data['city_stats'] as $city): ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($city['city']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo number_format($city['booking_count']); ?> bookings</p>
                            <p class="text-sm text-gray-600"><?php echo number_format($city['revenue']); ?> XAF revenue</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Performance by Category -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Performance by Category</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($report_data['category_stats'] as $category): ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900"><?php echo ucfirst(htmlspecialchars($category['category'])); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo number_format($category['booking_count']); ?> bookings</p>
                            <p class="text-sm text-gray-600"><?php echo number_format($category['revenue']); ?> XAF revenue</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Daily Trend -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Daily Booking Trend</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($report_data['daily_trend'] as $day): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo date('M j, Y', strtotime($day['booking_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($day['bookings']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($day['revenue']); ?> XAF
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="text-center text-sm text-gray-500 mt-8">
                    <p>This report was generated automatically by EventZon Admin Panel</p>
                    <p>For questions or support, contact the development team</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If no valid export type, redirect to bookings
header('Location: bookings.php');
exit;
?>