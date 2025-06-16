<?php
// Complete installation script for EventZon XAMPP
session_start();
require_once 'config/database.php';

// Check if already installed
$check_tables = $pdo->query("SHOW TABLES LIKE 'events'");
if ($check_tables->rowCount() > 0) {
    $events_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($events_count >= 10) {
        echo "EventZon is already installed with sample data!<br>";
        echo "<a href='index.php'>Go to Homepage</a> | <a href='admin/index.php'>Admin Panel</a>";
        exit;
    }
}

try {
    // Insert sample users including admin
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $user_password = password_hash('password', PASSWORD_DEFAULT);
    
    $pdo->exec("DELETE FROM users WHERE email IN ('admin@eventzon.cm', 'organizer1@cameroon.cm', 'organizer2@yaounde.cm', 'organizer3@douala.cm')");
    
    $stmt = $pdo->prepare("INSERT INTO users (email, name, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin@eventzon.cm', 'EventZon Admin', $admin_password, 'admin']);
    $admin_id = $pdo->lastInsertId();
    
    $stmt->execute(['organizer1@cameroon.cm', 'Cameroon Cultural Center', $user_password, 'user']);
    $org1_id = $pdo->lastInsertId();
    
    $stmt->execute(['organizer2@yaounde.cm', 'Yaounde Events Ltd', $user_password, 'user']);
    $org2_id = $pdo->lastInsertId();
    
    $stmt->execute(['organizer3@douala.cm', 'Douala Business Hub', $user_password, 'user']);
    $org3_id = $pdo->lastInsertId();
    
    // Insert 10 authentic Cameroon events
    $events = [
        [
            'Festival Ngondo 2024',
            'The annual traditional festival of the Sawa people celebrating water spirits and cultural heritage. Features traditional dances, canoe races, and cultural exhibitions along the Wouri River.',
            '2024-12-15 10:00:00',
            'Rive Wouri, Douala',
            5000,
            2000,
            'arts',
            'Douala',
            'Littoral',
            'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3',
            $org1_id
        ],
        [
            'Cameroon Tech Summit 2024',
            'Leading technology conference bringing together innovators, entrepreneurs, and tech leaders across Central Africa. Featuring keynotes on AI, fintech, and digital transformation.',
            '2024-11-20 09:00:00',
            'Hilton Yaounde',
            25000,
            500,
            'technology',
            'Yaounde',
            'Centre',
            'https://images.unsplash.com/photo-1540575467063-178a50c2df87',
            $org2_id
        ],
        [
            'Bamenda Ring Road Cultural Festival',
            'Celebration of the diverse cultures of the Northwest Region featuring traditional music, dance, and local crafts from the Grassfields communities.',
            '2024-10-25 14:00:00',
            'Bamenda Commercial Avenue',
            3000,
            1500,
            'arts',
            'Bamenda',
            'Northwest',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d',
            $org1_id
        ],
        [
            'Cameroon Coffee & Cocoa Expo',
            'International trade exhibition showcasing Cameroons premium coffee and cocoa products. Network with farmers, traders, and international buyers.',
            '2024-11-10 08:00:00',
            'Palais des Congrès, Yaounde',
            15000,
            800,
            'business',
            'Yaounde',
            'Centre',
            'https://images.unsplash.com/photo-1447933601403-0c6688de566e',
            $org2_id
        ],
        [
            'Mount Cameroon Race of Hope',
            'Annual mountain race to the summit of Mount Cameroon, West Africas highest peak. International athletics event with categories for professionals and amateurs.',
            '2024-02-10 06:00:00',
            'Buea Mountain Club',
            12000,
            300,
            'sports',
            'Buea',
            'Southwest',
            'https://images.unsplash.com/photo-1551698618-1dfe5d97d256',
            $org1_id
        ],
        [
            'Kribi Jazz Festival',
            'International jazz festival on the beautiful Atlantic coast featuring local and international artists. Three days of music, food, and beach activities.',
            '2024-12-28 18:00:00',
            'Kribi Beach Resort',
            8000,
            1200,
            'music',
            'Kribi',
            'South',
            'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f',
            $org1_id
        ],
        [
            'Bafoussam Agribusiness Conference',
            'Regional conference focusing on agricultural innovation and agribusiness opportunities in the Western Highlands. Features farmer cooperatives and modern farming techniques.',
            '2024-09-15 09:00:00',
            'Centre de Conférences Bafoussam',
            10000,
            600,
            'business',
            'Bafoussam',
            'West',
            'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b',
            $org3_id
        ],
        [
            'Limbe Festival of Arts and Culture',
            'Coastal cultural festival celebrating the artistic heritage of the Southwest Region. Traditional art exhibitions, music performances, and cultural workshops.',
            '2024-08-17 16:00:00',
            'Limbe Botanic Garden',
            4000,
            1000,
            'arts',
            'Limbe',
            'Southwest',
            'https://images.unsplash.com/photo-1471919743851-c4df8b6ee133',
            $org1_id
        ],
        [
            'Garoua Business Forum',
            'Northern Cameroons premier business networking event connecting entrepreneurs, investors, and government officials. Focus on cross-border trade and regional development.',
            '2024-10-05 08:30:00',
            'Hôtel Relais Saint-Hubert',
            20000,
            400,
            'business',
            'Garoua',
            'North',
            'https://images.unsplash.com/photo-1515187029135-18ee286d815b',
            $org3_id
        ],
        [
            'Makossa Music Festival Douala',
            'Celebration of Cameroons iconic Makossa music genre featuring legendary artists and new talent. Three-day festival with concerts, workshops, and music history exhibitions.',
            '2024-07-14 19:00:00',
            'Palais des Sports de Douala',
            7500,
            3000,
            'music',
            'Douala',
            'Littoral',
            'https://images.unsplash.com/photo-1501386761578-eac5c94b800a',
            $org1_id
        ]
    ];
    
    $event_stmt = $pdo->prepare("INSERT INTO events (title, description, date, venue, price, available_tickets, category, city, region, image_url, organizer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($events as $event) {
        $event_stmt->execute($event);
    }
    
    // Create sample bookings for demonstration
    $booking_stmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id, quantity, total_amount, status, attendee_name, attendee_email, attendee_phone, ticket_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Get some event IDs for sample bookings
    $event_ids = $pdo->query("SELECT id FROM events LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($event_ids) >= 3) {
        $booking_stmt->execute([$admin_id, $event_ids[0], 2, 10000, 'confirmed', 'John Doe', 'john@example.com', '+237123456789', 'TK' . time() . '001']);
        $booking_stmt->execute([$org1_id, $event_ids[1], 1, 25000, 'confirmed', 'Marie Ngozi', 'marie@example.com', '+237987654321', 'TK' . time() . '002']);
        $booking_stmt->execute([$org2_id, $event_ids[2], 3, 9000, 'pending', 'Paul Biya Jr', 'paul@example.com', '+237555123456', 'TK' . time() . '003']);
    }
    
    echo "<h1>EventZon Installation Complete!</h1>";
    echo "<h2>Sample Data Installed:</h2>";
    echo "<ul>";
    echo "<li>✓ 4 User accounts created</li>";
    echo "<li>✓ 10 Authentic Cameroon events added</li>";
    echo "<li>✓ Sample bookings created</li>";
    echo "<li>✓ Admin panel configured</li>";
    echo "</ul>";
    
    echo "<h2>Login Credentials:</h2>";
    echo "<h3>Admin Account:</h3>";
    echo "<p>Email: <strong>admin@eventzon.cm</strong><br>";
    echo "Password: <strong>admin123</strong></p>";
    
    echo "<h3>Sample User Accounts:</h3>";
    echo "<p>Email: <strong>organizer1@cameroon.cm</strong><br>";
    echo "Password: <strong>password</strong></p>";
    
    echo "<h2>Quick Links:</h2>";
    echo "<p><a href='index.php' style='background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Visit Homepage</a>";
    echo "<a href='admin/index.php' style='background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Admin Panel</a></p>";
    
    echo "<h2>Events Added:</h2>";
    echo "<ol>";
    echo "<li>Festival Ngondo 2024 (Douala)</li>";
    echo "<li>Cameroon Tech Summit 2024 (Yaounde)</li>";
    echo "<li>Bamenda Ring Road Cultural Festival (Bamenda)</li>";
    echo "<li>Cameroon Coffee & Cocoa Expo (Yaounde)</li>";
    echo "<li>Mount Cameroon Race of Hope (Buea)</li>";
    echo "<li>Kribi Jazz Festival (Kribi)</li>";
    echo "<li>Bafoussam Agribusiness Conference (Bafoussam)</li>";
    echo "<li>Limbe Festival of Arts and Culture (Limbe)</li>";
    echo "<li>Garoua Business Forum (Garoua)</li>";
    echo "<li>Makossa Music Festival Douala (Douala)</li>";
    echo "</ol>";
    
    echo "<h2>Admin Panel Features:</h2>";
    echo "<ul>";
    echo "<li>✓ Complete event management (add/edit/delete)</li>";
    echo "<li>✓ Booking management with status updates</li>";
    echo "<li>✓ CSV export functionality</li>";
    echo "<li>✓ Analytics reports by date, event, or user</li>";
    echo "<li>✓ User management</li>";
    echo "<li>✓ Dashboard with statistics</li>";
    echo "</ul>";
    
    echo "<h2>User Features:</h2>";
    echo "<ul>";
    echo "<li>✓ Enhanced checkout process with payment options</li>";
    echo "<li>✓ Booking history dashboard</li>";
    echo "<li>✓ Ticket download (HTML format)</li>";
    echo "<li>✓ QR code generation for tickets</li>";
    echo "<li>✓ Shopping cart functionality</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "Installation failed: " . $e->getMessage();
}
?>