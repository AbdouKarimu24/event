<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center">
                <a href="index.php" class="text-xl font-bold text-indigo-600">EventZon Admin</a>
                <nav class="ml-8">
                    <div class="flex space-x-6">
                        <a href="index.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="events.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-calendar mr-1"></i>Events
                        </a>
                        <a href="bookings.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-ticket-alt mr-1"></i>Bookings
                        </a>
                        <a href="users.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-users mr-1"></i>Users
                        </a>
                        <a href="reports.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-chart-bar mr-1"></i>Reports
                        </a>
                    </div>
                </nav>
            </div>
            <div class="flex items-center space-x-4">
                <a href="../index.php" class="text-gray-700 hover:text-indigo-600">
                    <i class="fas fa-external-link-alt mr-1"></i>View Site
                </a>
                <div class="relative">
                    <button class="flex items-center text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-user-circle text-xl mr-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?></span>
                        <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                </div>
                <a href="../logout.php" class="text-gray-700 hover:text-red-600">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</header>