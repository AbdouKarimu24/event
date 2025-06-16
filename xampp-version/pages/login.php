<?php
if (is_logged_in()) {
    redirect('dashboard');
}

if ($_POST) {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            show_message('Login successful!', 'success');
            redirect('dashboard');
        } else {
            show_message('Invalid email or password', 'error');
        }
    } else {
        show_message('Please fill in all fields', 'error');
    }
}

$flash = get_flash_message();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sign in to your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="index.php?page=register" class="font-medium text-indigo-600 hover:text-indigo-500">
                    create a new account
                </a>
            </p>
        </div>
        
        <?php if ($flash): ?>
            <div class="rounded-md p-4 <?php echo $flash['type'] === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Email address"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Sign in
                </button>
            </div>
        </form>
        
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-50 text-gray-500">Demo Accounts</span>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <div class="bg-blue-50 p-3 rounded-md">
                    <p class="text-sm font-medium text-blue-900">Admin Account:</p>
                    <p class="text-xs text-blue-700">Email: admin@eventzon.cm</p>
                    <p class="text-xs text-blue-700">Password: admin123</p>
                </div>
                <div class="bg-green-50 p-3 rounded-md">
                    <p class="text-sm font-medium text-green-900">User Account:</p>
                    <p class="text-xs text-green-700">Email: organizer1@cameroon.cm</p>
                    <p class="text-xs text-green-700">Password: password</p>
                </div>
            </div>
        </div>
    </div>
</div>