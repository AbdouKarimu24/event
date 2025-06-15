<?php
$event_id = (int)($_GET['id'] ?? 0);
if (!$event_id) {
    redirect('events');
}

$event = get_event($event_id);
if (!$event) {
    redirect('404');
}

// Handle add to cart
if ($_POST && $_POST['action'] === 'add_to_cart') {
    if (!is_logged_in()) {
        show_message('Please login to add items to cart', 'error');
        redirect('login');
    }
    
    $quantity = max(1, min(10, (int)$_POST['quantity']));
    
    if (add_to_cart($_SESSION['user_id'], $event_id, $quantity)) {
        show_message('Event added to cart successfully!', 'success');
    } else {
        show_message('Failed to add event to cart', 'error');
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="index.php?page=home" class="hover:text-indigo-600">Home</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><a href="index.php?page=events" class="hover:text-indigo-600">Events</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-gray-900"><?php echo htmlspecialchars($event['title']); ?></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Event Details -->
        <div class="lg:col-span-2">
            <!-- Event Image -->
            <div class="h-64 md:h-96 bg-gray-200 rounded-lg mb-6 overflow-hidden">
                <?php if ($event['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-6xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Event Info -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                        <?php echo ucfirst($event['category']); ?>
                    </span>
                    <span class="text-sm text-gray-500"><?php echo $event['city']; ?>, <?php echo $event['region']; ?></span>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    <?php echo htmlspecialchars($event['title']); ?>
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2 text-indigo-600"></i>
                        <span><?php echo format_date($event['date']); ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                        <span><?php echo htmlspecialchars($event['venue']); ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-ticket-alt mr-2 text-indigo-600"></i>
                        <span><?php echo $event['available_tickets']; ?> tickets available</span>
                    </div>
                </div>

                <?php if ($event['organizer_name']): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Organized by</h3>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-indigo-600"></i>
                        </div>
                        <span class="text-gray-900"><?php echo htmlspecialchars($event['organizer_name']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">About this event</h3>
                    <div class="prose max-w-none text-gray-700">
                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                    </div>
                </div>
            </div>

            <!-- Event Location -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Location</h3>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-map-marker-alt text-indigo-600 mt-1"></i>
                    <div>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($event['venue']); ?></p>
                        <p class="text-gray-600"><?php echo $event['city']; ?>, <?php echo $event['region']; ?> Region, Cameroon</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow sticky top-8">
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-indigo-600 mb-2">
                            <?php echo format_currency($event['price']); ?>
                        </div>
                        <p class="text-sm text-gray-600">per ticket</p>
                    </div>

                    <?php if ($event['available_tickets'] > 0): ?>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add_to_cart">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <select name="quantity" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <?php for ($i = 1; $i <= min(10, $event['available_tickets']); $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> ticket<?php echo $i > 1 ? 's' : ''; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <?php if (is_logged_in()): ?>
                                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-medium">
                                    <i class="fas fa-cart-plus mr-2"></i>
                                    Add to Cart
                                </button>
                            <?php else: ?>
                                <a href="index.php?page=login" class="block w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-medium text-center">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Login to Book
                                </a>
                            <?php endif; ?>
                        </form>

                        <div class="mt-6 space-y-2 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                                <span>Secure booking</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-mobile-alt mr-2 text-green-600"></i>
                                <span>Mobile tickets</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-2 text-green-600"></i>
                                <span>Instant confirmation</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4 bg-red-50 border border-red-200 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mb-2"></i>
                            <p class="text-red-800 font-medium">Sold Out</p>
                            <p class="text-red-600 text-sm">This event is no longer available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Share Event -->
            <div class="bg-white rounded-lg shadow mt-6 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this event</h3>
                <div class="flex space-x-3">
                    <a href="#" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded text-center text-sm hover:bg-blue-700 transition">
                        <i class="fab fa-facebook-f mr-1"></i> Facebook
                    </a>
                    <a href="#" class="flex-1 bg-blue-400 text-white px-4 py-2 rounded text-center text-sm hover:bg-blue-500 transition">
                        <i class="fab fa-twitter mr-1"></i> Twitter
                    </a>
                    <a href="#" class="flex-1 bg-green-600 text-white px-4 py-2 rounded text-center text-sm hover:bg-green-700 transition">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Events -->
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">You might also like</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php 
            $related_events = get_events(['category' => $event['category'], 'limit' => 3]);
            foreach ($related_events as $related_event): 
                if ($related_event['id'] == $event_id) continue;
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="h-48 bg-gray-200 flex items-center justify-center">
                    <?php if ($related_event['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($related_event['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($related_event['title']); ?>"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-calendar-alt text-4xl text-gray-400"></i>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <?php echo htmlspecialchars($related_event['title']); ?>
                    </h3>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500"><?php echo $related_event['city']; ?></span>
                        <span class="text-lg font-bold text-indigo-600">
                            <?php echo format_currency($related_event['price']); ?>
                        </span>
                    </div>
                    <div class="mt-4">
                        <a href="index.php?page=event-details&id=<?php echo $related_event['id']; ?>" 
                           class="block w-full bg-indigo-600 text-white py-2 rounded text-center hover:bg-indigo-700 transition">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>