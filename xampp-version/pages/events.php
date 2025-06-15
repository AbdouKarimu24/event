<?php
// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$city = $_GET['city'] ?? '';
$date = $_GET['date'] ?? '';

// Get filtered events
$filters = array_filter([
    'search' => $search,
    'category' => $category,
    'city' => $city,
    'date' => $date
]);

$events = get_events($filters);
$total_events = count($events);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Discover Events</h1>
        <p class="text-lg text-gray-600">Find amazing events happening across Cameroon</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="hidden" name="page" value="events">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search events..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    <?php foreach (get_categories() as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Cities</option>
                    <?php foreach (get_cities() as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo $city === $c ? 'selected' : ''; ?>>
                            <?php echo $c; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="index.php?page=events" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="mb-6">
        <p class="text-gray-600">
            Found <?php echo $total_events; ?> event<?php echo $total_events !== 1 ? 's' : ''; ?>
            <?php if ($search || $category || $city || $date): ?>
                matching your search
            <?php endif; ?>
        </p>
    </div>

    <!-- Events Grid -->
    <?php if (empty($events)): ?>
        <div class="text-center py-16">
            <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No events found</h3>
            <p class="text-gray-600 mb-6">Try adjusting your search criteria or browse all events</p>
            <a href="index.php?page=events" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                View All Events
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        <?php if ($event['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-calendar-alt text-4xl text-gray-400"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                <?php echo ucfirst($event['category']); ?>
                            </span>
                            <span class="text-sm text-gray-500"><?php echo $event['city']; ?></span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($event['title']); ?>
                        </h3>
                        
                        <p class="text-gray-600 text-sm mb-4">
                            <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                        </p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-calendar mr-2"></i>
                                <?php echo format_date($event['date']); ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-ticket-alt mr-2"></i>
                                <?php echo $event['available_tickets']; ?> tickets available
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="text-lg font-bold text-indigo-600">
                                <?php echo format_currency($event['price']); ?>
                            </div>
                            <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" 
                               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>