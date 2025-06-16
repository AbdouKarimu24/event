<?php
// Complete XAMPP EventZon Setup Script
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // XAMPP default MySQL port
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eventzon');

try {
    // Connect to MySQL server first
    $pdo_server = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8", DB_USER, DB_PASS);
    $pdo_server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo_server->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "<h1>EventZon XAMPP Setup</h1>";
    echo "<p>Database connection successful!</p>";
    
    // Create tables
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        avatar_url VARCHAR(500),
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        date DATETIME NOT NULL,
        venue VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        available_tickets INT NOT NULL,
        category VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        region VARCHAR(100) NOT NULL,
        image_url VARCHAR(500),
        organizer_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (organizer_id) REFERENCES users(id)
    );

    CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        quantity INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        attendee_name VARCHAR(255) NOT NULL,
        attendee_email VARCHAR(255) NOT NULL,
        attendee_phone VARCHAR(20),
        ticket_number VARCHAR(50) UNIQUE,
        qr_code TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id)
    );

    CREATE TABLE IF NOT EXISTS cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        quantity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id)
    );
    ";
    
    $pdo->exec($sql);
    echo "<p>Tables created successfully!</p>";
    
    // Check if data already exists
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $event_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    
    if ($user_count == 0) {
        // Insert sample users
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $user_password = password_hash('password', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (email, name, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin@eventzon.cm', 'EventZon Admin', $admin_password, 'admin']);
        $admin_id = $pdo->lastInsertId();
        
        $stmt->execute(['organizer1@cameroon.cm', 'Cameroon Cultural Center', $user_password, 'user']);
        $org1_id = $pdo->lastInsertId();
        
        $stmt->execute(['organizer2@yaounde.cm', 'Yaounde Events Ltd', $user_password, 'user']);
        $org2_id = $pdo->lastInsertId();
        
        $stmt->execute(['organizer3@douala.cm', 'Douala Business Hub', $user_password, 'user']);
        $org3_id = $pdo->lastInsertId();
        
        echo "<p>Sample users created!</p>";
        
        // Insert 10 authentic Cameroon events
        if ($event_count == 0) {
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
            
            echo "<p>10 authentic Cameroon events added!</p>";
            
            // Create sample bookings
            $event_ids = $pdo->query("SELECT id FROM events LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($event_ids) >= 3) {
                $booking_stmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id, quantity, total_amount, status, attendee_name, attendee_email, attendee_phone, ticket_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $booking_stmt->execute([$admin_id, $event_ids[0], 2, 10000, 'confirmed', 'John Doe', 'john@example.com', '+237123456789', 'TK' . time() . '001']);
                $booking_stmt->execute([$org1_id, $event_ids[1], 1, 25000, 'confirmed', 'Marie Ngozi', 'marie@example.com', '+237987654321', 'TK' . time() . '002']);
                $booking_stmt->execute([$org2_id, $event_ids[2], 3, 9000, 'pending', 'Paul Biya Jr', 'paul@example.com', '+237555123456', 'TK' . time() . '003']);
                
                echo "<p>Sample bookings created!</p>";
            }
        }
    } else {
        echo "<p>Data already exists - skipping sample data creation.</p>";
    }
    
    echo "<div style='background: #f0f9ff; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<h3>Login Credentials:</h3>";
    echo "<div style='background: #1e40af; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Admin Account:</strong><br>";
    echo "Email: admin@eventzon.cm<br>";
    echo "Password: admin123";
    echo "</div>";
    
    echo "<div style='background: #059669; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Sample User Account:</strong><br>";
    echo "Email: organizer1@cameroon.cm<br>";
    echo "Password: password";
    echo "</div>";
    
    echo "<h3>Quick Links:</h3>";
    echo "<p>";
    echo "<a href='index.php' style='background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px; display: inline-block;'>🏠 Homepage</a>";
    echo "<a href='admin/index.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>👤 Admin Panel</a>";
    echo "</p>";
    
    echo "<h3>Features Available:</h3>";
    echo "<ul>";
    echo "<li>✅ Enhanced checkout process with payment methods</li>";
    echo "<li>✅ Booking history dashboard with QR codes</li>";
    echo "<li>✅ Ticket download functionality</li>";
    echo "<li>✅ Complete admin panel with analytics</li>";
    echo "<li>✅ Event management (CRUD operations)</li>";
    echo "<li>✅ Booking management and reporting</li>";
    echo "<li>✅ CSV export functionality</li>";
    echo "<li>✅ 10 authentic Cameroon events</li>";
    echo "</ul>";
    echo "</div>";
    
    // Update database config file
    $config_content = "<?php
// Database configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eventzon');

try {
    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";port=\" . DB_PORT . \";dbname=\" . DB_NAME . \";charset=utf8\", DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    die(\"Database connection failed: \" . \$e->getMessage());
}

// Function for admin panel compatibility
function getDbConnection() {
    global \$pdo;
    return \$pdo;
}
?>";
    
    file_put_contents('config/database.php', $config_content);
    echo "<p>✅ Database configuration updated!</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; color: #dc2626; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h2>❌ Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Make sure XAMPP is running and MySQL is started on port 3307!</strong></p>";
    echo "</div>";
}
?>