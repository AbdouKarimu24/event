<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$event_id = $_GET['id'] ?? null;
$event = null;

// If editing, fetch event data
if ($event_id) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: admin.php?page=events');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $venue = $_POST['venue'];
    $city = $_POST['city'];
    $region = $_POST['region'];
    $date = $_POST['date'];
    $price = floatval($_POST['price']);
    $available_tickets = intval($_POST['available_tickets']);
    $image_url = $_POST['image_url'];
    
    try {
        if ($event_id) {
            // Update existing event
            $stmt = $pdo->prepare("
                UPDATE events SET 
                title = ?, description = ?, category = ?, venue = ?, 
                city = ?, region = ?, date = ?, price = ?, 
                available_tickets = ?, image_url = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $description, $category, $venue, 
                $city, $region, $date, $price, 
                $available_tickets, $image_url, $event_id
            ]);
            $message = "Event updated successfully!";
        } else {
            // Create new event
            $stmt = $pdo->prepare("
                INSERT INTO events (
                    title, description, category, venue, city, region, 
                    date, price, available_tickets, image_url, organizer_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $description, $category, $venue, $city, $region, 
                $date, $price, $available_tickets, $image_url, $_SESSION['user_id']
            ]);
            $message = "Event created successfully!";
        }
        
        header('Location: admin.php?page=events&success=' . urlencode($message));
        exit();
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event_id ? 'Edit Event' : 'Create Event' ?> - EventZon Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">EventZon Admin</h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php?page=dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin.php?page=events">
                                <i class="bi bi-calendar-event"></i> Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php?page=bookings">
                                <i class="bi bi-people"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php?page=reports">
                                <i class="bi bi-bar-chart"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $event_id ? 'Edit Event' : 'Create New Event' ?></h1>
                    <a href="admin.php?page=events" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Events
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Event Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($event['title'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category *</label>
                                                <select class="form-select" id="category" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="music" <?= ($event['category'] ?? '') === 'music' ? 'selected' : '' ?>>Music</option>
                                                    <option value="business" <?= ($event['category'] ?? '') === 'business' ? 'selected' : '' ?>>Business</option>
                                                    <option value="technology" <?= ($event['category'] ?? '') === 'technology' ? 'selected' : '' ?>>Technology</option>
                                                    <option value="arts" <?= ($event['category'] ?? '') === 'arts' ? 'selected' : '' ?>>Arts</option>
                                                    <option value="sports" <?= ($event['category'] ?? '') === 'sports' ? 'selected' : '' ?>>Sports</option>
                                                    <option value="food" <?= ($event['category'] ?? '') === 'food' ? 'selected' : '' ?>>Food</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="date" class="form-label">Event Date & Time *</label>
                                                <input type="datetime-local" class="form-control" id="date" name="date" 
                                                       value="<?= $event ? date('Y-m-d\TH:i', strtotime($event['date'])) : '' ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="venue" class="form-label">Venue *</label>
                                                <input type="text" class="form-control" id="venue" name="venue" 
                                                       value="<?= htmlspecialchars($event['venue'] ?? '') ?>" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="city" class="form-label">City *</label>
                                                <select class="form-select" id="city" name="city" required>
                                                    <option value="">Select City</option>
                                                    <option value="Yaoundé" <?= ($event['city'] ?? '') === 'Yaoundé' ? 'selected' : '' ?>>Yaoundé</option>
                                                    <option value="Douala" <?= ($event['city'] ?? '') === 'Douala' ? 'selected' : '' ?>>Douala</option>
                                                    <option value="Bamenda" <?= ($event['city'] ?? '') === 'Bamenda' ? 'selected' : '' ?>>Bamenda</option>
                                                    <option value="Bafoussam" <?= ($event['city'] ?? '') === 'Bafoussam' ? 'selected' : '' ?>>Bafoussam</option>
                                                    <option value="Garoua" <?= ($event['city'] ?? '') === 'Garoua' ? 'selected' : '' ?>>Garoua</option>
                                                    <option value="Maroua" <?= ($event['city'] ?? '') === 'Maroua' ? 'selected' : '' ?>>Maroua</option>
                                                    <option value="Ngaoundéré" <?= ($event['city'] ?? '') === 'Ngaoundéré' ? 'selected' : '' ?>>Ngaoundéré</option>
                                                    <option value="Bertoua" <?= ($event['city'] ?? '') === 'Bertoua' ? 'selected' : '' ?>>Bertoua</option>
                                                    <option value="Ebolowa" <?= ($event['city'] ?? '') === 'Ebolowa' ? 'selected' : '' ?>>Ebolowa</option>
                                                    <option value="Kribi" <?= ($event['city'] ?? '') === 'Kribi' ? 'selected' : '' ?>>Kribi</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="region" class="form-label">Region *</label>
                                        <select class="form-select" id="region" name="region" required>
                                            <option value="">Select Region</option>
                                            <option value="Centre" <?= ($event['region'] ?? '') === 'Centre' ? 'selected' : '' ?>>Centre</option>
                                            <option value="Littoral" <?= ($event['region'] ?? '') === 'Littoral' ? 'selected' : '' ?>>Littoral</option>
                                            <option value="North-West" <?= ($event['region'] ?? '') === 'North-West' ? 'selected' : '' ?>>North-West</option>
                                            <option value="West" <?= ($event['region'] ?? '') === 'West' ? 'selected' : '' ?>>West</option>
                                            <option value="North" <?= ($event['region'] ?? '') === 'North' ? 'selected' : '' ?>>North</option>
                                            <option value="Far North" <?= ($event['region'] ?? '') === 'Far North' ? 'selected' : '' ?>>Far North</option>
                                            <option value="Adamawa" <?= ($event['region'] ?? '') === 'Adamawa' ? 'selected' : '' ?>>Adamawa</option>
                                            <option value="East" <?= ($event['region'] ?? '') === 'East' ? 'selected' : '' ?>>East</option>
                                            <option value="South" <?= ($event['region'] ?? '') === 'South' ? 'selected' : '' ?>>South</option>
                                            <option value="South-West" <?= ($event['region'] ?? '') === 'South-West' ? 'selected' : '' ?>>South-West</option>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">Price (XAF) *</label>
                                                <input type="number" class="form-control" id="price" name="price" 
                                                       value="<?= $event['price'] ?? '0' ?>" min="0" step="100" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="available_tickets" class="form-label">Available Tickets *</label>
                                                <input type="number" class="form-control" id="available_tickets" name="available_tickets" 
                                                       value="<?= $event['available_tickets'] ?? '100' ?>" min="1" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image_url" class="form-label">Event Image URL</label>
                                        <input type="url" class="form-control" id="image_url" name="image_url" 
                                               value="<?= htmlspecialchars($event['image_url'] ?? '') ?>" 
                                               placeholder="https://example.com/image.jpg">
                                        <div class="form-text">Enter a URL for the event image (optional)</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Event Preview</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="eventPreview" class="mb-3">
                                                <?php if ($event && $event['image_url']): ?>
                                                    <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                                                         class="img-fluid rounded mb-2" alt="Event Image">
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <h6 id="previewTitle"><?= htmlspecialchars($event['title'] ?? 'Event Title') ?></h6>
                                            <p class="text-muted small" id="previewCategory">
                                                <?= $event['category'] ? ucfirst($event['category']) : 'Category' ?>
                                            </p>
                                            <p class="small">
                                                <i class="bi bi-geo-alt"></i> 
                                                <span id="previewLocation">
                                                    <?= htmlspecialchars(($event['venue'] ?? 'Venue') . ', ' . ($event['city'] ?? 'City')) ?>
                                                </span>
                                            </p>
                                            <p class="small">
                                                <i class="bi bi-calendar"></i> 
                                                <span id="previewDate">
                                                    <?= $event ? date('M j, Y g:i A', strtotime($event['date'])) : 'Date & Time' ?>
                                                </span>
                                            </p>
                                            <p class="fw-bold text-success" id="previewPrice">
                                                <?= number_format($event['price'] ?? 0) ?> XAF
                                            </p>
                                        </div>
                                    </div>

                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6>Quick Tips</h6>
                                            <ul class="small text-muted mb-0">
                                                <li>Use high-quality images (recommended: 800x400px)</li>
                                                <li>Write clear, engaging descriptions</li>
                                                <li>Set realistic ticket quantities</li>
                                                <li>Double-check date and time</li>
                                                <li>Choose appropriate categories</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> 
                                            <?= $event_id ? 'Update Event' : 'Create Event' ?>
                                        </button>
                                        <a href="admin.php?page=events" class="btn btn-outline-secondary">
                                            <i class="bi bi-x"></i> Cancel
                                        </a>
                                        <?php if ($event_id): ?>
                                            <a href="../pages/event-details.php?id=<?= $event_id ?>" 
                                               class="btn btn-outline-info" target="_blank">
                                                <i class="bi bi-eye"></i> Preview
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Live preview updates
        document.getElementById('title').addEventListener('input', function() {
            document.getElementById('previewTitle').textContent = this.value || 'Event Title';
        });

        document.getElementById('category').addEventListener('change', function() {
            document.getElementById('previewCategory').textContent = this.value ? 
                this.value.charAt(0).toUpperCase() + this.value.slice(1) : 'Category';
        });

        document.getElementById('venue').addEventListener('input', updateLocation);
        document.getElementById('city').addEventListener('change', updateLocation);

        function updateLocation() {
            const venue = document.getElementById('venue').value || 'Venue';
            const city = document.getElementById('city').value || 'City';
            document.getElementById('previewLocation').textContent = venue + ', ' + city;
        }

        document.getElementById('date').addEventListener('change', function() {
            if (this.value) {
                const date = new Date(this.value);
                const options = { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                };
                document.getElementById('previewDate').textContent = date.toLocaleDateString('en-US', options);
            } else {
                document.getElementById('previewDate').textContent = 'Date & Time';
            }
        });

        document.getElementById('price').addEventListener('input', function() {
            const price = parseFloat(this.value) || 0;
            document.getElementById('previewPrice').textContent = 
                new Intl.NumberFormat().format(price) + ' XAF';
        });

        document.getElementById('image_url').addEventListener('input', function() {
            const preview = document.getElementById('eventPreview');
            if (this.value) {
                preview.innerHTML = `<img src="${this.value}" class="img-fluid rounded mb-2" alt="Event Image" 
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px; display: none;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                    </div>`;
            } else {
                preview.innerHTML = `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                    </div>`;
            }
        });

        // Auto-select region based on city
        document.getElementById('city').addEventListener('change', function() {
            const cityToRegion = {
                'Yaoundé': 'Centre',
                'Douala': 'Littoral',
                'Bamenda': 'North-West',
                'Bafoussam': 'West',
                'Garoua': 'North',
                'Maroua': 'Far North',
                'Ngaoundéré': 'Adamawa',
                'Bertoua': 'East',
                'Ebolowa': 'South',
                'Kribi': 'South'
            };
            
            const region = cityToRegion[this.value];
            if (region) {
                document.getElementById('region').value = region;
            }
        });
    </script>
</body>
</html>