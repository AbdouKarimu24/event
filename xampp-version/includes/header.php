<?php
$flash = get_flash_message();
?>
<header class="bg-white shadow-sm border-b">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <a href="index.php?page=home" class="text-2xl font-bold text-indigo-600">
                    EventZon
                </a>
            </div>
            
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    <a href="index.php?page=home" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="index.php?page=events" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Events</a>
                    <?php if (is_logged_in()): ?>
                        <a href="index.php?page=dashboard" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <a href="index.php?page=cart" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                        <div class="relative group">
                            <button class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                                <a href="index.php?page=dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                <a href="auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="index.php?page=login" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="index.php?page=register" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">Register</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" class="mobile-menu-button bg-gray-800 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="mobile-menu hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="index.php?page=home" class="text-gray-600 hover:text-indigo-600 block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="index.php?page=events" class="text-gray-600 hover:text-indigo-600 block px-3 py-2 rounded-md text-base font-medium">Events</a>
                <?php if (is_logged_in()): ?>
                    <a href="index.php?page=dashboard" class="text-gray-600 hover:text-indigo-600 block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    <a href="index.php?page=cart" class="text-gray-600 hover:text-indigo-600 block px-3 py-2 rounded-md text-base font-medium">Cart</a>
                    <a href="auth/logout.php" class="text-gray-600 hover:text-indigo-600 block px-3 py-2 rounded-md text-base font-medium">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="text-gray-600 hover:text-indigo-600 block px-3 py-2 rounded-md text-base font-medium">Login</a>
                    <a href="index.php?page=register" class="text-gray-600 hover:text-indigo-600 block px-3 py-2 rounded-md text-base font-medium">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<?php if ($flash): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="alert alert-<?php echo $flash['type']; ?> p-4 rounded-md mb-4 <?php 
        echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 
             ($flash['type'] === 'error' ? 'bg-red-100 text-red-700 border border-red-400' : 
             'bg-blue-100 text-blue-700 border border-blue-400'); 
    ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
</div>
<?php endif; ?>