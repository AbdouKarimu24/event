<?php
if (!is_logged_in()) {
    redirect('login');
}

$user_bookings = get_user_bookings($_SESSION['user_id']);
$total_bookings = count($user_bookings);
$total_spent = array_sum(array_column($user_bookings, 'total_amount'));
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p class="text-lg text-gray-600">Manage your bookings and discover new events</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i class="fas fa-ticket-alt text-indigo-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $total_bookings; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Spent</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo format_currency($total_spent); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Upcoming Events</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $upcoming = array_filter($user_bookings, function($booking) {
                            return strtotime($booking['date']) > time();
                        });
                        echo count($upcoming);
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Quick Actions</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="index.php?page=events" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-search text-indigo-600 text-xl mr-3"></i>
                    <span class="font-medium text-gray-900">Browse Events</span>
                </a>
                <a href="index.php?page=cart" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-shopping-cart text-indigo-600 text-xl mr-3"></i>
                    <span class="font-medium text-gray-900">View Cart</span>
                </a>
                <?php if (is_admin()): ?>
                <a href="index.php?page=admin" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-cog text-indigo-600 text-xl mr-3"></i>
                    <span class="font-medium text-gray-900">Admin Panel</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Your Bookings</h2>
        </div>
        
        <?php if (empty($user_bookings)): ?>
            <div class="p-6 text-center">
                <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No bookings yet</h3>
                <p class="text-gray-600 mb-6">Start exploring events and make your first booking!</p>
                <a href="index.php?page=events" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Browse Events
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($user_bookings as $booking): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($booking['title']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Ticket: <?php echo htmlspecialchars($booking['ticket_number']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo format_date($booking['date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($booking['venue']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $booking['quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo format_currency($booking['total_amount']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                              ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="index.php?page=event-details&id=<?php echo $booking['event_id']; ?>" 
                                       class="text-indigo-600 hover:text-indigo-900">View Event</a>
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <span class="text-gray-300">|</span>
                                        <a href="index.php?action=download_ticket&booking_id=<?php echo $booking['id']; ?>" 
                                           class="text-green-600 hover:text-green-900" target="_blank">
                                            <i class="fas fa-download mr-1"></i>Download
                                        </a>
                                        <span class="text-gray-300">|</span>
                                        <a href="#" onclick="showQRCode(<?php echo $booking['id']; ?>)" 
                                           class="text-purple-600 hover:text-purple-900">
                                            <i class="fas fa-qrcode mr-1"></i>QR Code
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-300">|</span>
                                        <span class="text-gray-500">Pending Payment</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket QR Code</h3>
            <div id="qrCodeContainer" class="flex justify-center mb-4">
                <!-- QR Code will be inserted here -->
            </div>
            <p class="text-sm text-gray-600 mb-4">Show this QR code at the event entrance</p>
            <div class="flex justify-center space-x-4">
                <button onclick="downloadQRCode()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    <i class="fas fa-download mr-2"></i>Download QR
                </button>
                <button onclick="closeQRModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showQRCode(bookingId) {
    // Generate QR code using a simple library or service
    const qrCodeData = `EVENTZON-TICKET-${bookingId}-${Date.now()}`;
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrCodeData)}`;
    
    document.getElementById('qrCodeContainer').innerHTML = `
        <img src="${qrCodeUrl}" alt="QR Code" class="border border-gray-300 rounded">
    `;
    
    document.getElementById('qrModal').classList.remove('hidden');
}

function closeQRModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

function downloadQRCode() {
    const qrImage = document.querySelector('#qrCodeContainer img');
    if (qrImage) {
        const link = document.createElement('a');
        link.download = 'ticket-qr-code.png';
        link.href = qrImage.src;
        link.click();
    }
}

// Close modal when clicking outside
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});
</script>