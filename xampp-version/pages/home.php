<?php
// Get featured events
$featured_events = get_events(['limit' => 6]);
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Discover Amazing Events in Cameroon
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-indigo-100">
                From concerts to conferences, find your next unforgettable experience
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto">
                <a href="index.php?page=events" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Browse Events
                </a>
                <?php if (!is_logged_in()): ?>
                <a href="index.php?page=register" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition">
                    Get Started
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="bg-white py-12 -mt-8 relative z-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="page" value="events">
                <div>
                    <input type="text" name="search" placeholder="Search events..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        <?php foreach (get_categories() as $category): ?>
                            <option value="<?php echo $category; ?>"><?php echo ucfirst($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="city" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">All Cities</option>
                        <?php foreach (get_cities() as $city): ?>
                            <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Featured Events -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Events</h2>
            <p class="text-lg text-gray-600">Don't miss these amazing upcoming events</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach (array_slice($featured_events, 0, 6) as $event): ?>
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
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                        <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo date('M j, Y', strtotime($event['date'])); ?>
                        </div>
                        <div class="text-lg font-bold text-indigo-600">
                            <?php echo format_currency($event['price']); ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" 
                           class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition inline-block text-center">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="index.php?page=events" class="bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 transition">
                View All Events
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose EventZon?</h2>
            <p class="text-lg text-gray-600">Your trusted platform for event discovery and booking</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-2xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Easy Discovery</h3>
                <p class="text-gray-600">Find events by category, location, or date with our powerful search filters.</p>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Secure Booking</h3>
                <p class="text-gray-600">Safe and secure ticket booking with instant confirmation and QR codes.</p>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-mobile-alt text-2xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Mobile Friendly</h3>
                <p class="text-gray-600">Access your tickets anywhere, anytime with our mobile-optimized platform.</p>
            </div>
        </div>
    </div>
</section>