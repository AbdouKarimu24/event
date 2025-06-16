<?php
// Authentication functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

function logout_user() {
    session_destroy();
    header('Location: index.php?page=home');
    exit;
}

// Event functions
function get_events($filters = []) {
    global $pdo;
    
    $sql = "SELECT e.*, u.name as organizer_name FROM events e 
            LEFT JOIN users u ON e.organizer_id = u.id WHERE 1=1";
    $params = [];
    
    if (!empty($filters['search'])) {
        $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    
    if (!empty($filters['category'])) {
        $sql .= " AND e.category = ?";
        $params[] = $filters['category'];
    }
    
    if (!empty($filters['city'])) {
        $sql .= " AND e.city = ?";
        $params[] = $filters['city'];
    }
    
    if (!empty($filters['date'])) {
        $sql .= " AND DATE(e.date) = ?";
        $params[] = $filters['date'];
    }
    
    $sql .= " ORDER BY e.date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_event($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT e.*, u.name as organizer_name FROM events e 
                          LEFT JOIN users u ON e.organizer_id = u.id WHERE e.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function create_event($data) {
    global $pdo;
    
    $sql = "INSERT INTO events (title, description, date, venue, price, available_tickets, 
            category, city, region, image_url, organizer_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['title'], $data['description'], $data['date'], $data['venue'],
        $data['price'], $data['available_tickets'], $data['category'],
        $data['city'], $data['region'], $data['image_url'], $data['organizer_id']
    ]);
}

// Cart functions
function get_cart_items($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT c.*, e.title, e.price, e.image_url 
                          FROM cart_items c 
                          JOIN events e ON c.event_id = e.id 
                          WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function add_to_cart($user_id, $event_id, $quantity) {
    global $pdo;
    
    // Verify user exists
    $user_check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $user_check->execute([$user_id]);
    if (!$user_check->fetch()) {
        return false; // User doesn't exist
    }
    
    // Verify event exists
    $event_check = $pdo->prepare("SELECT id FROM events WHERE id = ?");
    $event_check->execute([$event_id]);
    if (!$event_check->fetch()) {
        return false; // Event doesn't exist
    }
    
    // Check if item already exists
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$user_id, $event_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?");
        return $stmt->execute([$quantity, $existing['id']]);
    } else {
        // Insert new item
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, event_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $event_id, $quantity]);
    }
}

function update_cart_item($cart_item_id, $quantity) {
    global $pdo;
    
    if ($quantity <= 0) {
        return remove_from_cart($cart_item_id);
    }
    
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    return $stmt->execute([$quantity, $cart_item_id]);
}

function remove_from_cart($cart_item_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
    return $stmt->execute([$cart_item_id]);
}

function clear_cart($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// Booking functions
function create_booking($data) {
    global $pdo;
    
    $ticket_number = 'TK' . time() . rand(1000, 9999);
    
    $sql = "INSERT INTO bookings (user_id, event_id, quantity, total_amount, 
            attendee_name, attendee_email, attendee_phone, ticket_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $data['user_id'], $data['event_id'], $data['quantity'],
        $data['total_amount'], $data['attendee_name'], $data['attendee_email'],
        $data['attendee_phone'], $ticket_number
    ]);
    
    if ($result) {
        return $pdo->lastInsertId();
    }
    return false;
}

function get_user_bookings($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT b.*, e.title, e.date, e.venue 
                          FROM bookings b 
                          JOIN events e ON b.event_id = e.id 
                          WHERE b.user_id = ? 
                          ORDER BY b.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Utility functions
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function format_currency($amount) {
    return 'XAF ' . number_format($amount, 0);
}

function format_date($date) {
    return date('M j, Y \a\t g:i A', strtotime($date));
}

function redirect($page) {
    header("Location: index.php?page={$page}");
    exit;
}

function show_message($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Categories and cities for Cameroon
function get_categories() {
    return ['music', 'business', 'technology', 'arts', 'sports', 'food'];
}

function get_cities() {
    return [
        'Douala', 'Yaounde', 'Bamenda', 'Bafoussam', 'Garoua', 'Maroua',
        'Ngaoundere', 'Bertoua', 'Ebolowa', 'Kribi', 'Limbe', 'Buea'
    ];
}

function get_regions() {
    return [
        'Centre', 'Littoral', 'West', 'Northwest', 'Southwest', 
        'North', 'Far North', 'Adamawa', 'East', 'South'
    ];
}

// Additional functions for admin functionality

function get_booking_with_event($booking_id, $user_id = null) {
    global $pdo;
    
    $sql = "
        SELECT b.*, e.title as event_title, e.date as event_date, e.venue, e.price
        FROM bookings b 
        JOIN events e ON b.event_id = e.id 
        WHERE b.id = ?
    ";
    
    $params = [$booking_id];
    
    if ($user_id) {
        $sql .= " AND b.user_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

function generate_ticket_pdf($booking) {
    // For XAMPP version, generate HTML ticket
    $html = "
    <div style='border: 2px solid #333; padding: 20px; font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto;'>
        <h2 style='text-align: center; color: #4F46E5; margin-bottom: 20px;'>EventZon Ticket</h2>
        <hr style='border: 1px solid #ddd;'>
        <div style='margin: 15px 0;'>
            <p><strong>Event:</strong> {$booking['event_title']}</p>
            <p><strong>Date:</strong> " . date('F j, Y g:i A', strtotime($booking['event_date'])) . "</p>
            <p><strong>Venue:</strong> {$booking['venue']}</p>
            <p><strong>Attendee:</strong> {$booking['attendee_name']}</p>
            <p><strong>Email:</strong> {$booking['attendee_email']}</p>
            <p><strong>Phone:</strong> {$booking['attendee_phone']}</p>
            <p><strong>Quantity:</strong> {$booking['quantity']} ticket(s)</p>
            <p><strong>Total Amount:</strong> " . number_format($booking['total_amount']) . " FCFA</p>
            <p><strong>Ticket Number:</strong> <code>{$booking['ticket_number']}</code></p>
            <p><strong>Status:</strong> " . ucfirst($booking['status']) . "</p>
        </div>
        <hr style='border: 1px solid #ddd;'>
        <p style='text-align: center; font-size: 12px; color: #666;'>Present this ticket at the venue entrance</p>
        <p style='text-align: center; font-size: 10px; color: #999;'>EventZon - Cameroon Event Management Platform</p>
    </div>";
    
    return $html;
}
    return $stmt->fetch();
}

function generate_ticket_pdf($booking) {
    // Simple HTML ticket generation for now
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Event Ticket</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .ticket { border: 2px solid #333; padding: 20px; max-width: 600px; }
            .header { text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
            .details { margin: 20px 0; }
            .qr-code { text-align: center; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='ticket'>
            <div class='header'>
                <h1>EventZon Ticket</h1>
                <h2>" . htmlspecialchars($booking['title']) . "</h2>
            </div>
            <div class='details'>
                <p><strong>Ticket Number:</strong> " . htmlspecialchars($booking['ticket_number']) . "</p>
                <p><strong>Attendee:</strong> " . htmlspecialchars($booking['attendee_name']) . "</p>
                <p><strong>Date:</strong> " . date('F j, Y \a\t g:i A', strtotime($booking['date'])) . "</p>
                <p><strong>Venue:</strong> " . htmlspecialchars($booking['venue']) . "</p>
                <p><strong>City:</strong> " . htmlspecialchars($booking['city']) . "</p>
                <p><strong>Quantity:</strong> " . $booking['quantity'] . " ticket(s)</p>
                <p><strong>Total Amount:</strong> " . number_format($booking['total_amount']) . " XAF</p>
            </div>
            <div class='qr-code'>
                <p>Show this ticket at the event entrance</p>
                <div style='border: 1px solid #ccc; padding: 20px; display: inline-block;'>
                    QR CODE: " . $booking['ticket_number'] . "
                </div>
            </div>
        </div>
    </body>
    </html>";
    
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="ticket_' . $booking['ticket_number'] . '.html"');
    echo $html;
}
?>