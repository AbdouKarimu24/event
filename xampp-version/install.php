<?php
// EventZon XAMPP Installation Script
session_start();

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Step 1: Check requirements
if ($step == 1) {
    $php_version = version_compare(PHP_VERSION, '7.4.0', '>=');
    $pdo_available = extension_loaded('pdo');
    $pdo_mysql = extension_loaded('pdo_mysql');
    
    if (!$php_version) $errors[] = 'PHP 7.4 or higher required. Current: ' . PHP_VERSION;
    if (!$pdo_available) $errors[] = 'PDO extension required';
    if (!$pdo_mysql) $errors[] = 'PDO MySQL extension required';
}

// Step 2: Database setup
if ($step == 2 && $_POST) {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'eventzon';
    
    try {
        // Test connection
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name");
        $pdo->exec("USE $db_name");
        
        // Create tables
        $sql = file_get_contents('config/database_schema.sql');
        if ($sql) {
            $pdo->exec($sql);
            $success[] = 'Database and tables created successfully';
            
            // Update config file
            $config_content = "<?php\ndefine('DB_HOST', '$db_host');\ndefine('DB_USER', '$db_user');\ndefine('DB_PASS', '$db_pass');\ndefine('DB_NAME', '$db_name');\n?>";
            file_put_contents('config/db_config.php', $config_content);
            
        } else {
            $errors[] = 'Could not load database schema';
        }
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

// Step 3: Admin user creation
if ($step == 3 && $_POST) {
    require_once 'config/database.php';
    
    $admin_name = $_POST['admin_name'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';
    
    if (empty($admin_name) || empty($admin_email) || empty($admin_password)) {
        $errors[] = 'All fields are required';
    } else {
        try {
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            if ($stmt->execute([$admin_name, $admin_email, $hashed_password])) {
                $success[] = 'Admin user created successfully';
                
                // Create installation complete marker
                file_put_contents('.installed', date('Y-m-d H:i:s'));
            } else {
                $errors[] = 'Failed to create admin user';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventZon Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">EventZon Installation</h2>
                <p class="text-gray-600">Step <?php echo $step; ?> of 3</p>
            </div>
            
            <?php if ($errors): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php foreach ($success as $msg): ?>
                        <p><?php echo htmlspecialchars($msg); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">System Requirements</h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="<?php echo $php_version ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $php_version ? '✓' : '✗'; ?>
                            </span>
                            <span class="ml-2">PHP 7.4+ (Current: <?php echo PHP_VERSION; ?>)</span>
                        </div>
                        <div class="flex items-center">
                            <span class="<?php echo $pdo_available ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $pdo_available ? '✓' : '✗'; ?>
                            </span>
                            <span class="ml-2">PDO Extension</span>
                        </div>
                        <div class="flex items-center">
                            <span class="<?php echo $pdo_mysql ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $pdo_mysql ? '✓' : '✗'; ?>
                            </span>
                            <span class="ml-2">PDO MySQL Extension</span>
                        </div>
                    </div>
                    
                    <?php if (empty($errors)): ?>
                        <div class="mt-6">
                            <a href="?step=2" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 inline-block text-center">
                                Continue to Database Setup
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            
            <?php elseif ($step == 2): ?>
                <form method="POST" class="bg-white rounded-lg shadow p-6 space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Database Configuration</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Host</label>
                        <input type="text" name="db_host" value="localhost" required
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database User</label>
                        <input type="text" name="db_user" value="root" required
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Password</label>
                        <input type="password" name="db_pass" 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Name</label>
                        <input type="text" name="db_name" value="eventzon" required
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">
                        Setup Database
                    </button>
                </form>
                
                <?php if ($success): ?>
                    <div class="text-center">
                        <a href="?step=3" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                            Continue to Admin Setup
                        </a>
                    </div>
                <?php endif; ?>
            
            <?php elseif ($step == 3): ?>
                <form method="POST" class="bg-white rounded-lg shadow p-6 space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Create Admin User</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="admin_name" required
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="admin_email" required
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="admin_password" required minlength="6"
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">
                        Create Admin User
                    </button>
                </form>
                
                <?php if ($success): ?>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <h3 class="text-lg font-semibold text-green-600 mb-4">Installation Complete!</h3>
                        <p class="text-gray-600 mb-4">EventZon has been successfully installed.</p>
                        <a href="index.php" class="bg-indigo-600 text-white py-2 px-6 rounded hover:bg-indigo-700">
                            Go to Application
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>