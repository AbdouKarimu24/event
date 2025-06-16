<?php
// Installation script for EventZon database
// Run this file once to create the database and tables

// Database configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eventzon');

echo "<h2>EventZon Database Installation</h2>";

try {
    // First, connect without specifying database to create it
    echo "Connecting to MySQL server...<br>";
    $pdo_no_db = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8", DB_USER, DB_PASS);
    $pdo_no_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    echo "Creating database 'eventzon'...<br>";
    $pdo_no_db->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "✓ Database created successfully!<br><br>";
    
    // Now connect to the specific database
    echo "Connecting to eventzon database...<br>";
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create tables
    echo "Creating tables...<br>";
    
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

    CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        user_id INT,
        data TEXT,
        expires TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
    ";
    
    $pdo->exec($sql);
    echo "✓ All tables created successfully!<br><br>";
    
    // Insert sample admin user
    echo "Creating admin user...<br>";
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, name, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin@eventzon.cm', 'Admin User', $admin_password, 'admin']);
    echo "✓ Admin user created (email: admin@eventzon.cm, password: admin123)<br><br>";
    
    echo "<div style='color: green; font-weight: bold;'>✓ Installation completed successfully!</div>";
    echo "<p><a href='index.php'>Go to EventZon Homepage</a></p>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Installation failed: " . $e->getMessage() . "</div>";
    echo "<p>Please check your XAMPP MySQL service is running on port 3307.</p>";
}
?>