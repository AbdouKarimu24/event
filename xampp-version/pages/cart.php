<?php
if (!is_logged_in()) {
    redirect('login');
}

$cart_items = get_cart_items($_SESSION['user_id']);
$total_amount = 0;

// Handle cart actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_quantity') {
        $cart_item_id = (int)$_POST['cart_item_id'];
        $quantity = max(1, (int)$_POST['quantity']);
        
        if (update_cart_item($cart_item_id, $quantity)) {
            show_message('Cart updated successfully', 'success');
            redirect('cart');
        } else {
            show_message('Failed to update cart', 'error');
        }
    }
    
    if ($action === 'remove_item') {
        $cart_item_id = (int)$_POST['cart_item_id'];
        
        if (remove_from_cart($cart_item_id)) {
            show_message('Item removed from cart', 'success');
            redirect('cart');
        } else {
            show_message('Failed to remove item', 'error');
        }
    }
    
    if ($action === 'checkout') {
        // Process checkout
        $attendee_name = sanitize_input($_POST['attendee_name'] ?? '');
        $attendee_email = sanitize_input($_POST['attendee_email'] ?? '');
        $attendee_phone = sanitize_input($_POST['attendee_phone'] ?? '');
        
        if (empty($attendee_name) || empty($attendee_email)) {
            show_message('Please fill in all required fields', 'error');
        } else {
            // Create bookings for each cart item
            $booking_success = true;
            foreach ($cart_items as $item) {
                $booking_data = [
                    'user_id' => $_SESSION['user_id'],
                    'event_id' => $item['event_id'],
                    'quantity' => $item['quantity'],
                    'total_amount' => $item['price'] * $item['quantity'],
                    'attendee_name' => $attendee_name,
                    'attendee_email' => $attendee_email,
                    'attendee_phone' => $attendee_phone
                ];
                
                if (!create_booking($booking_data)) {
                    $booking_success = false;
                    break;
                }
            }
            
            if ($booking_success) {
                clear_cart($_SESSION['user_id']);
                show_message('Booking confirmed! Check your email for tickets.', 'success');
                redirect('dashboard');
            } else {
                show_message('Booking failed. Please try again.', 'error');
            }
        }
    }
}

foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="text-center py-16">
            <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
            <p class="text-gray-600 mb-6">Add some events to your cart to get started!</p>
            <a href="index.php?page=events" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                Browse Events
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Cart Items</h2>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                                             class="w-full h-full object-cover rounded-lg">
                                    <?php else: ?>
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo format_currency($item['price']); ?> per ticket
                                    </p>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <form method="POST" class="flex items-center space-x-2">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                        <label class="text-sm text-gray-600">Qty:</label>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="10" 
                                               class="w-16 px-2 py-1 border border-gray-300 rounded text-center"
                                               onchange="this.form.submit()">
                                    </form>
                                    
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800" 
                                                onclick="return confirm('Remove this item from cart?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="text-lg font-medium text-gray-900">
                                    <?php echo format_currency($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Checkout -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow sticky top-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal</span>
                                <span><?php echo format_currency($total_amount); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Processing Fee</span>
                                <span>XAF 0</span>
                            </div>
                            <div class="border-t pt-2">
                                <div class="flex justify-between font-medium">
                                    <span>Total</span>
                                    <span><?php echo format_currency($total_amount); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="checkout">
                            
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Attendee Information</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <input type="text" name="attendee_name" required
                                       placeholder="Enter full name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input type="email" name="attendee_email" required
                                       placeholder="Enter email address"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input type="tel" name="attendee_phone" required
                                       placeholder="Enter phone number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">Payment Method</h3>
                            
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="payment_method" value="mobile_money" checked class="mr-3">
                                    <i class="fas fa-mobile-alt text-green-600 mr-2"></i>
                                    <span>Mobile Money (MTN/Orange)</span>
                                </label>
                                
                                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="payment_method" value="bank_transfer" class="mr-3">
                                    <i class="fas fa-university text-blue-600 mr-2"></i>
                                    <span>Bank Transfer</span>
                                </label>
                                
                                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="payment_method" value="cash" class="mr-3">
                                    <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                                    <span>Pay at Venue</span>
                                </label>
                            </div>
                            
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                                <div class="flex">
                                    <i class="fas fa-info-circle text-yellow-600 mr-2 mt-1"></i>
                                    <div class="text-sm text-yellow-800">
                                        <p><strong>Payment Instructions:</strong></p>
                                        <p>After clicking "Complete Booking", you will receive payment instructions via email. Your booking will be confirmed once payment is received.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-medium">
                                <i class="fas fa-credit-card mr-2"></i>
                                Complete Booking
                            </button>
                        </form>
                        
                        <div class="mt-4 text-xs text-gray-500 text-center">
                            <p>By completing your booking, you agree to our terms and conditions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>