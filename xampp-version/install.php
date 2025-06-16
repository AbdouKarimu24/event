<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventZon XAMPP Installation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px 10px 0; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>EventZon XAMPP Installation</h1>
    
    <?php
    $step = $_GET['step'] ?? 'check';
    
    if ($step === 'check') {
        echo '<div class="info"><strong>Step 1:</strong> Checking XAMPP environment...</div>';
        
        // Check if we can connect to MySQL
        try {
            $pdo = new PDO("mysql:host=localhost;charset=utf8", "root", "");
            echo '<div class="success">✓ MySQL connection successful</div>';
            
            // Check if database exists
            $result = $pdo->query("SHOW DATABASES LIKE 'eventzon'");
            if ($result->rowCount() > 0) {
                echo '<div class="info">Database "eventzon" already exists</div>';
            } else {
                echo '<div class="info">Database "eventzon" will be created</div>';
            }
            
            echo '<a href="?step=install" class="btn">Continue Installation</a>';
            
        } catch (PDOException $e) {
            echo '<div class="error">❌ MySQL connection failed: ' . $e->getMessage() . '</div>';
            echo '<div class="info">';
            echo '<strong>Please ensure:</strong><br>';
            echo '1. XAMPP is running<br>';
            echo '2. MySQL service is started<br>';
            echo '3. MySQL is running on default port 3306<br>';
            echo '</div>';
        }
        
    } elseif ($step === 'install') {
        echo '<div class="info"><strong>Step 2:</strong> Installing database and sample data...</div>';
        
        try {
            // Connect to MySQL
            $pdo = new PDO("mysql:host=localhost;charset=utf8", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS eventzon");
            echo '<div class="success">✓ Database created</div>';
            
            // Use the database
            $pdo->exec("USE eventzon");
            
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
                FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE SET NULL
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
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS cart_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                event_id INT NOT NULL,
                quantity INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
            );
            ";
            
            $pdo->exec($sql);
            echo '<div class="success">✓ Tables created</div>';
            
            // Check if sample data exists
            $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            if ($userCount == 0) {
                // Insert sample users
                $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
                $userPass = password_hash('password', PASSWORD_DEFAULT);
                
                $pdo->exec("INSERT INTO users (email, name, password, role) VALUES
                    ('admin@eventzon.cm', 'EventZon Admin', '$adminPass', 'admin'),
                    ('organizer1@cameroon.cm', 'Cameroon Cultural Center', '$userPass', 'user'),
                    ('organizer2@yaounde.cm', 'Yaounde Events Ltd', '$userPass', 'user'),
                    ('organizer3@douala.cm', 'Douala Business Hub', '$userPass', 'user')
                ");
                
                echo '<div class="success">✓ Sample users created</div>';
                
                // Insert sample events
                $events = [
                    ['Festival Ngondo 2024', 'Traditional Sawa water festival with cultural dances and exhibitions', '2024-12-15 10:00:00', 'Rive Wouri, Douala', 5000, 2000, 'arts', 'Douala', 'Littoral', 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3', 2],
                    ['Cameroon Tech Summit 2024', 'Leading technology conference for Central Africa', '2024-11-20 09:00:00', 'Hilton Yaounde', 25000, 500, 'technology', 'Yaounde', 'Centre', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87', 3],
                    ['Bamenda Cultural Festival', 'Northwest Region cultural celebration', '2024-10-25 14:00:00', 'Bamenda Commercial Avenue', 3000, 1500, 'arts', 'Bamenda', 'Northwest', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d', 2],
                    ['Coffee & Cocoa Expo', 'International trade exhibition', '2024-11-10 08:00:00', 'Palais des Congrès, Yaounde', 15000, 800, 'business', 'Yaounde', 'Centre', 'https://images.unsplash.com/photo-1447933601403-0c6688de566e', 3],
                    ['Mount Cameroon Race', 'Annual mountain race to the summit', '2024-02-10 06:00:00', 'Buea Mountain Club', 12000, 300, 'sports', 'Buea', 'Southwest', 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256', 2],
                    ['Kribi Jazz Festival', 'International jazz festival on the coast', '2024-12-28 18:00:00', 'Kribi Beach Resort', 8000, 1200, 'music', 'Kribi', 'South', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f', 2],
                    ['Bafoussam Agribusiness Conference', 'Agricultural innovation conference', '2024-09-15 09:00:00', 'Centre de Conférences Bafoussam', 10000, 600, 'business', 'Bafoussam', 'West', 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b', 4],
                    ['Limbe Arts Festival', 'Southwest cultural arts festival', '2024-08-17 16:00:00', 'Limbe Botanic Garden', 4000, 1000, 'arts', 'Limbe', 'Southwest', 'https://images.unsplash.com/photo-1471919743851-c4df8b6ee133', 2],
                    ['Garoua Business Forum', 'Northern business networking event', '2024-10-05 08:30:00', 'Hôtel Relais Saint-Hubert', 20000, 400, 'business', 'Garoua', 'North', 'https://images.unsplash.com/photo-1515187029135-18ee286d815b', 4],
                    ['Makossa Music Festival', 'Celebration of Cameroon music', '2024-07-14 19:00:00', 'Palais des Sports de Douala', 7500, 3000, 'music', 'Douala', 'Littoral', 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a', 2]
                ];
                
                $stmt = $pdo->prepare("INSERT INTO events (title, description, date, venue, price, available_tickets, category, city, region, image_url, organizer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($events as $event) {
                    $stmt->execute($event);
                }
                
                echo '<div class="success">✓ 10 sample events created</div>';
                
                // Create sample bookings
                $pdo->exec("INSERT INTO bookings (user_id, event_id, quantity, total_amount, status, attendee_name, attendee_email, attendee_phone, ticket_number) VALUES
                    (1, 1, 2, 10000, 'confirmed', 'John Doe', 'john@example.com', '+237123456789', 'TK001'),
                    (2, 2, 1, 25000, 'confirmed', 'Marie Ngozi', 'marie@example.com', '+237987654321', 'TK002'),
                    (3, 3, 3, 9000, 'pending', 'Paul Biya Jr', 'paul@example.com', '+237555123456', 'TK003')
                ");
                
                echo '<div class="success">✓ Sample bookings created</div>';
            } else {
                echo '<div class="info">Sample data already exists</div>';
            }
            
            echo '<div class="success"><h2>🎉 Installation Complete!</h2></div>';
            
            echo '<div class="info">';
            echo '<h3>Login Credentials:</h3>';
            echo '<strong>Admin:</strong> admin@eventzon.cm / admin123<br>';
            echo '<strong>User:</strong> organizer1@cameroon.cm / password';
            echo '</div>';
            
            echo '<a href="index.php" class="btn btn-success">Go to Homepage</a>';
            echo '<a href="admin/index.php" class="btn btn-warning">Admin Panel</a>';
            
        } catch (PDOException $e) {
            echo '<div class="error">❌ Installation failed: ' . $e->getMessage() . '</div>';
            echo '<a href="?step=check" class="btn">Try Again</a>';
        }
    }
    ?>
    
    <hr>
    <h3>Features Included:</h3>
    <ul>
        <li>✅ Enhanced checkout with payment methods</li>
        <li>✅ Booking dashboard with QR codes</li>
        <li>✅ Ticket download functionality</li>
        <li>✅ Complete admin panel</li>
        <li>✅ Event management system</li>
        <li>✅ CSV export capabilities</li>
        <li>✅ 10 authentic Cameroon events</li>
    </ul>
</body>
</html>