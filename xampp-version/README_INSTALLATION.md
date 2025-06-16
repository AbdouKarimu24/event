# EventZon XAMPP Installation Guide

## Prerequisites
- XAMPP installed with Apache and MySQL running
- MySQL configured to run on port 3307 (as shown in your XAMPP Control Panel)

## Installation Steps

1. **Copy Files**
   - Copy the entire `xampp-version` folder to your XAMPP `htdocs` directory
   - Rename it to `eventzon` (optional, for cleaner URL)
   - Path should be: `C:\xampp\htdocs\eventzon\` (Windows) or `/opt/lampp/htdocs/eventzon/` (Linux)

2. **Start XAMPP Services**
   - Make sure Apache is running on ports 80, 443
   - Make sure MySQL is running on port 3307
   - Verify in XAMPP Control Panel that both services show "Running"

3. **Install Database**
   - Open your browser and go to: `http://localhost/eventzon/install_database.php`
   - This will create the database and all required tables
   - Creates an admin user: email: `admin@eventzon.cm`, password: `admin123`

4. **Access the Application**
   - Homepage: `http://localhost/eventzon/`
   - Admin login: `http://localhost/eventzon/login.php`

## Database Configuration
The application is configured to connect to:
- Host: localhost
- Port: 3307 (matching your XAMPP setup)
- Database: eventzon
- User: root
- Password: (empty - default XAMPP)

## Troubleshooting
- If you get "database connection failed", ensure MySQL is running on port 3307
- If tables don't exist, run the installation script again
- Check XAMPP error logs if issues persist

## Features
- Event management and booking system
- User registration and authentication
- Shopping cart functionality
- Admin dashboard for event management
- Responsive design for mobile and desktop