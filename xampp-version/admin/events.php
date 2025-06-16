<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireAdmin();

$pdo = getDbConnection();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_event') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $event_date = $_POST['event_date'] ?? '';
        $venue = $_POST['venue'] ?? '';
        $city = $_POST['city'] ?? '';
        $category = $_POST['category'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $available_tickets = (int)($_POST['available_tickets'] ?? 0);
        $image_url = $_POST['image_url'] ?? '';
        
        if (!empty($title) && !empty($event_date) && !empty($venue)) {
            $stmt = $pdo->prepare("
                INSERT INTO events (title, description, event_date, venue, city, category, price, available_tickets, image_url, organizer_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            
            if ($stmt->execute([$title, $description, $event_date, $venue, $city, $category, $price, $available_tickets, $image_url, $_SESSION['user_id']])) {
                $success_message = "Event created successfully!";
            } else {
                $error_message = "Failed to create event.";
            }
        } else {
            $error_message = "Please fill in all required fields.";
        }
    }
    
    if ($action === 'update_event') {
        $event_id = (int)$_POST['event_id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $event_date = $_POST['event_date'] ?? '';
        $venue = $_POST['venue'] ?? '';
        $city = $_POST['city'] ?? '';
        $category = $_POST['category'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $available_tickets = (int)($_POST['available_tickets'] ?? 0);
        $image_url = $_POST['image_url'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        $stmt = $pdo->prepare("
            UPDATE events 
            SET title=?, description=?, event_date=?, venue=?, city=?, category=?, price=?, available_tickets=?, image_url=?, status=? 
            WHERE id=?
        ");
        
        if ($stmt->execute([$title, $description, $event_date, $venue, $city, $category, $price, $available_tickets, $image_url, $status, $event_id])) {
            $success_message = "Event updated successfully!";
        } else {
            $error_message = "Failed to update event.";
        }
    }
    
    if ($action === 'delete_event') {
        $event_id = (int)$_POST['event_id'];
        
        // Check if event has bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $booking_count = $stmt->fetchColumn();
        
        if ($booking_count > 0) {
            $error_message = "Cannot delete event with existing bookings. Cancel bookings first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            if ($stmt->execute([$event_id])) {
                $success_message = "Event deleted successfully!";
            } else {
                $error_message = "Failed to delete event.";
            }
        }
    }
}

// Get events with pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_status = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ? OR venue LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_category)) {
    $where_conditions[] = "category = ?";
    $params[] = $filter_category;
}

if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM events $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_events = $count_stmt->fetchColumn();
$total_pages = ceil($total_events / $limit);

// Get events
$sql = "SELECT e.*, u.first_name, u.last_name, 
               (SELECT COUNT(*) FROM bookings WHERE event_id = e.id) as booking_count
        FROM events e 
        LEFT JOIN users u ON e.organizer_id = u.id 
        $where_clause 
        ORDER BY e.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get editing event if specified
$editing_event = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editing_event = $stmt->fetch();
}

$categories = ['music', 'business', 'technology', 'arts', 'sports', 'food', 'education', 'health', 'entertainment', 'other'];
$cities = ['Yaoundé', 'Douala', 'Bamenda', 'Bafoussam', 'Garoua', 'Maroua', 'Ngaoundéré', 'Bertoua', 'Kribi', 'Limbe'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - EventZon Admin</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin-header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Manage Events</h1>
            <button onclick="toggleEventForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>Add New Event
            </button>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Event Form -->
        <div id="eventForm" class="bg-white rounded-lg shadow mb-8 <?php echo $editing_event ? '' : 'hidden'; ?>">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold"><?php echo $editing_event ? 'Edit Event' : 'Add New Event'; ?></h2>
            </div>
            <div class="p-6">
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="action" value="<?php echo $editing_event ? 'update_event' : 'create_event'; ?>">
                    <?php if ($editing_event): ?>
                        <input type="hidden" name="event_id" value="<?php echo $editing_event['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Title *</label>
                        <input type="text" name="title" required
                               value="<?php echo htmlspecialchars($editing_event['title'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($editing_event['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Date *</label>
                        <input type="datetime-local" name="event_date" required
                               value="<?php echo $editing_event ? date('Y-m-d\TH:i', strtotime($editing_event['event_date'])) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Venue *</label>
                        <input type="text" name="venue" required
                               value="<?php echo htmlspecialchars($editing_event['venue'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select City</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo $city; ?>" <?php echo ($editing_event['city'] ?? '') === $city ? 'selected' : ''; ?>>
                                    <?php echo $city; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>" <?php echo ($editing_event['category'] ?? '') === $category ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price (XAF)</label>
                        <input type="number" name="price" min="0" step="0.01"
                               value="<?php echo $editing_event['price'] ?? '0'; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Available Tickets</label>
                        <input type="number" name="available_tickets" min="0"
                               value="<?php echo $editing_event['available_tickets'] ?? '100'; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <?php if ($editing_event): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                            <option value="active" <?php echo $editing_event['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $editing_event['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="cancelled" <?php echo $editing_event['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                        <input type="url" name="image_url"
                               value="<?php echo htmlspecialchars($editing_event['image_url'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2 flex space-x-4">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                            <?php echo $editing_event ? 'Update Event' : 'Create Event'; ?>
                        </button>
                        <button type="button" onclick="toggleEventForm()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" name="search" placeholder="Search events..."
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>" <?php echo $filter_category === $category ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Events List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold">Events (<?php echo $total_events; ?>)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Venue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tickets</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                        <?php if ($event['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($event['title']); ?>"
                                                 class="w-full h-full object-cover rounded-lg">
                                        <?php else: ?>
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($event['category'] ?? 'No category'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('M d, Y H:i', strtotime($event['event_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($event['venue']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($event['city'] ?? ''); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo number_format($event['price']); ?> XAF
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $event['available_tickets']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $event['booking_count']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $event['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                              ($event['status'] === 'inactive' ? 'bg-yellow-100 text-yellow-800' : 
                                               'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="?edit=<?php echo $event['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../index.php?page=event-details&id=<?php echo $event['id']; ?>" 
                                       class="text-green-600 hover:text-green-900" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $limit, $total_events); ?> of <?php echo $total_events; ?> events
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>&<?php echo http_build_query(array_filter($_GET, function($key) { return $key !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($key) { return $key !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&<?php echo http_build_query(array_filter($_GET, function($key) { return $key !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleEventForm() {
        const form = document.getElementById('eventForm');
        form.classList.toggle('hidden');
        
        if (!form.classList.contains('hidden')) {
            form.scrollIntoView({ behavior: 'smooth' });
        }
    }
    </script>
</body>
</html>