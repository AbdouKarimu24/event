<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireAdmin();

$pdo = getDbConnection();
$message = '';
$error = '';
$queryResult = null;

// Handle SQL query execution
if ($_POST && isset($_POST['sql_query'])) {
    $sqlQuery = trim($_POST['sql_query']);
    
    if (!empty($sqlQuery)) {
        try {
            $startTime = microtime(true);
            
            // Basic safety checks - prevent dangerous operations
            $upperQuery = strtoupper($sqlQuery);
            $dangerousKeywords = ['DROP', 'TRUNCATE', 'ALTER DATABASE', 'CREATE DATABASE', 'GRANT', 'REVOKE'];
            
            $isDangerous = false;
            foreach ($dangerousKeywords as $keyword) {
                if (strpos($upperQuery, $keyword) !== false) {
                    $isDangerous = true;
                    break;
                }
            }
            
            if ($isDangerous) {
                $error = "Dangerous operation detected. Only SELECT, INSERT, UPDATE, DELETE, CREATE TABLE, and ALTER TABLE queries are allowed.";
            } else {
                $stmt = $pdo->prepare($sqlQuery);
                $stmt->execute();
                
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                
                // Check if it's a SELECT query
                if (stripos($upperQuery, 'SELECT') === 0) {
                    $queryResult = [
                        'type' => 'select',
                        'data' => $stmt->fetchAll(),
                        'rowCount' => $stmt->rowCount(),
                        'executionTime' => $executionTime
                    ];
                } else {
                    $queryResult = [
                        'type' => 'other',
                        'rowCount' => $stmt->rowCount(),
                        'executionTime' => $executionTime
                    ];
                }
                
                $message = "Query executed successfully in {$executionTime}ms. {$stmt->rowCount()} row(s) affected.";
            }
        } catch (PDOException $e) {
            $error = "SQL Error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a SQL query.";
    }
}

// Get table information
try {
    $tablesQuery = "
        SELECT 
            TABLE_NAME as table_name,
            TABLE_ROWS as row_count,
            ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as size_mb
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = ? 
        ORDER BY TABLE_NAME
    ";
    $tablesStmt = $pdo->prepare($tablesQuery);
    $tablesStmt->execute([DB_NAME]);
    $tables = $tablesStmt->fetchAll();
} catch (PDOException $e) {
    $tables = [];
    $error = "Could not fetch table information: " . $e->getMessage();
}

// Get table data if requested
$tableData = null;
$selectedTable = $_GET['table'] ?? '';
if ($selectedTable && in_array($selectedTable, array_column($tables, 'table_name'))) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `$selectedTable` LIMIT 100");
        $stmt->execute();
        $tableData = $stmt->fetchAll();
        
        // Get column information
        $columnsStmt = $pdo->prepare("DESCRIBE `$selectedTable`");
        $columnsStmt->execute();
        $columns = $columnsStmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Could not fetch table data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Administration - EventZon</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sql-editor {
            font-family: 'Courier New', monospace;
            min-height: 150px;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
        }
        .table-structure {
            font-size: 0.9rem;
        }
        .query-samples {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .query-samples button {
            margin: 2px;
            padding: 5px 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="../index.php" class="logo">EventZon Database Admin</a>
                <ul class="nav-links">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="bookings.php">Bookings</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="database.php" class="active">Database Admin</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-database"></i> Database Administration</h2>
                <p class="text-muted mb-0">phpMyAdmin-like interface for XAMPP MySQL database</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- SQL Query Editor -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-code"></i> SQL Query Executor</h4>
                    </div>
                    <div class="card-body">
                        <!-- Sample Queries -->
                        <div class="query-samples">
                            <strong>Sample Queries:</strong><br>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setSample('users')">Users Query</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setSample('events')">Events Query</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setSample('bookings')">Bookings Query</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setSample('analytics')">Analytics Query</button>
                        </div>

                        <form method="POST" action="database.php">
                            <div class="form-group">
                                <textarea name="sql_query" class="form-control sql-editor" 
                                          placeholder="Enter your SQL query here..." id="sqlEditor"><?= htmlspecialchars($_POST['sql_query'] ?? '') ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Execute Query
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearEditor()">
                                    <i class="fas fa-trash"></i> Clear
                                </button>
                            </div>
                        </form>

                        <!-- Query Results -->
                        <?php if ($queryResult): ?>
                            <div class="mt-4">
                                <h5>Query Results</h5>
                                <?php if ($queryResult['type'] === 'select' && !empty($queryResult['data'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <?php foreach (array_keys($queryResult['data'][0]) as $column): ?>
                                                        <th><?= htmlspecialchars($column) ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($queryResult['data'] as $row): ?>
                                                    <tr>
                                                        <?php foreach ($row as $value): ?>
                                                            <td><?= $value === null ? '<em>NULL</em>' : htmlspecialchars($value) ?></td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php elseif ($queryResult['type'] === 'select'): ?>
                                    <p class="text-muted">No results returned.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Database Tables -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-table"></i> Database Tables</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tables)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Table</th>
                                            <th>Rows</th>
                                            <th>Size (MB)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tables as $table): ?>
                                            <tr>
                                                <td>
                                                    <a href="database.php?table=<?= urlencode($table['table_name']) ?>" 
                                                       class="text-decoration-none">
                                                        <?= htmlspecialchars($table['table_name']) ?>
                                                    </a>
                                                </td>
                                                <td><?= number_format($table['row_count'] ?? 0) ?></td>
                                                <td><?= $table['size_mb'] ?? '0.00' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No tables found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Database Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> Database Info</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Database:</strong></td>
                                <td><?= htmlspecialchars(DB_NAME) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Host:</strong></td>
                                <td><?= htmlspecialchars(DB_HOST) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Engine:</strong></td>
                                <td>MySQL</td>
                            </tr>
                            <tr>
                                <td><strong>Tables:</strong></td>
                                <td><?= count($tables) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Safety Notice -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4><i class="fas fa-shield-alt text-warning"></i> Safety Notice</h4>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>⚠️ Important:</strong><br>
                            • Always backup before running UPDATE/DELETE<br>
                            • Test queries on small datasets first<br>
                            • Dangerous operations are blocked<br>
                            • Use transactions for complex operations
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Data View -->
        <?php if ($selectedTable && $tableData !== null): ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-table"></i> Table: <?= htmlspecialchars($selectedTable) ?></h4>
                            <p class="text-muted mb-0">Showing first 100 rows</p>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($tableData)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <?php foreach (array_keys($tableData[0]) as $column): ?>
                                                    <th><?= htmlspecialchars($column) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tableData as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td><?= $value === null ? '<em class="text-muted">NULL</em>' : htmlspecialchars($value) ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Table is empty.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 EventZon Database Admin. Built for XAMPP.</p>
        </div>
    </footer>

    <script>
        function setSample(type) {
            const editor = document.getElementById('sqlEditor');
            const samples = {
                'users': 'SELECT id, email, first_name, last_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 10;',
                'events': 'SELECT id, title, category, venue, event_date, price, status FROM events WHERE status = "active" ORDER BY event_date DESC LIMIT 10;',
                'bookings': 'SELECT b.id, b.attendee_name, b.total_amount, b.status, e.title as event_title FROM bookings b JOIN events e ON b.event_id = e.id ORDER BY b.created_at DESC LIMIT 10;',
                'analytics': 'SELECT category, COUNT(*) as event_count, AVG(price) as avg_price FROM events GROUP BY category ORDER BY event_count DESC;'
            };
            editor.value = samples[type] || '';
        }

        function clearEditor() {
            document.getElementById('sqlEditor').value = '';
        }
    </script>
</body>
</html>