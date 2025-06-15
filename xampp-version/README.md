# EventZon - XAMPP Event Booking System

## Complete PHP/MySQL Event Booking Platform

This is a complete event booking system built with PHP and MySQL, designed to run on XAMPP. It includes all the features from your original requirements:

### ✅ Features Implemented

1. **User Authentication** - Login, registration, and session management
2. **Event Listings** - Browse events with search and filters
3. **Search Functionality** - Search by name, location, date, category
4. **Event Details** - Detailed event information pages
5. **Booking Cart** - Add/remove items, manage cart
6. **Checkout Process** - Complete booking system
7. **Booking History** - User dashboard with booking management
8. **Admin Panel** - Complete administration interface
9. **Database Admin** - phpMyAdmin-like interface

### 🚀 XAMPP Installation Instructions

#### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP on your computer
3. Start Apache and MySQL services from XAMPP Control Panel

#### Step 2: Setup the Application
1. Copy the entire `xampp-version` folder contents to `C:\xampp\htdocs\eventzon\`
2. Open your web browser and go to `http://localhost/eventzon/`
3. The application will automatically create the database and tables

#### Step 3: Access the System
- **Website**: http://localhost/eventzon/
- **Admin Panel**: http://localhost/eventzon/admin/
- **Database Admin**: http://localhost/eventzon/admin/database.php
- **phpMyAdmin**: http://localhost/phpmyadmin/ (traditional phpMyAdmin)

#### Step 4: Default Login Credentials
- **Admin Account**: 
  - Email: admin@eventzon.com
  - Password: admin123

### 📁 File Structure

```
xampp-version/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   └── auth.php             # Authentication functions
├── admin/
│   ├── index.php            # Admin dashboard
│   ├── database.php         # Database administration
│   ├── events.php           # Event management
│   ├── bookings.php         # Booking management
│   └── users.php            # User management
├── assets/
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   ├── js/
│   │   └── main.js          # JavaScript functionality
│   └── images/              # Image assets
├── index.php                # Main homepage
├── login.php                # User login
├── register.php             # User registration
├── event_details.php        # Event details page
├── cart.php                 # Shopping cart
├── dashboard.php            # User dashboard
├── checkout.php             # Checkout process
└── logout.php               # Logout handler
```

### 🗄️ Database Schema

The system automatically creates these tables:
- `users` - User accounts and profiles
- `events` - Event information
- `bookings` - Ticket bookings and reservations
- `cart_items` - Shopping cart items

### 🔧 Configuration

#### Database Settings (config/database.php)
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eventzon_db');
```

#### XAMPP Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server

### 🎯 Key Features

#### Database Administration Interface
- **SQL Query Executor** - Run custom SQL queries safely
- **Table Browser** - View and manage database tables
- **Safety Features** - Prevents dangerous operations
- **Sample Queries** - Pre-built queries for common tasks

#### Admin Panel Features
- **Dashboard** - Overview statistics and charts
- **Event Management** - Create, edit, delete events
- **Booking Management** - View and manage all bookings
- **User Management** - Manage user accounts
- **Analytics** - Revenue and booking statistics

#### User Features
- **Event Discovery** - Search and filter events
- **Booking System** - Add to cart and checkout
- **User Dashboard** - View booking history
- **Responsive Design** - Works on mobile and desktop

### 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session-based authentication
- Input validation and sanitization
- CSRF protection on forms
- Admin-only access controls

### 📱 Responsive Design

The interface is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

### 🎨 UI Components

- Modern Bootstrap-inspired design
- FontAwesome icons
- Gradient backgrounds
- Card-based layouts
- Mobile-friendly navigation

### 📊 Database Administration

The built-in database admin provides:
- **Table browsing** - View all tables and their data
- **SQL execution** - Run SELECT, INSERT, UPDATE, DELETE queries
- **Safety checks** - Prevents dangerous operations like DROP TABLE
- **Query samples** - Pre-built queries for common operations
- **Table statistics** - Row counts and table sizes

### 🚀 Getting Started

1. Install XAMPP and start Apache + MySQL
2. Copy files to `htdocs/eventzon/`
3. Visit `http://localhost/eventzon/`
4. Login with admin@eventzon.com / admin123
5. Start managing events and bookings!

### 📝 Notes

- The database is automatically created on first visit
- All tables are created with proper relationships
- Default admin account is created automatically
- The system includes sample data structure
- Compatible with standard XAMPP installation

### 🔗 Access Points

- **Main Site**: http://localhost/eventzon/
- **Admin Dashboard**: http://localhost/eventzon/admin/
- **Database Admin**: http://localhost/eventzon/admin/database.php
- **Classic phpMyAdmin**: http://localhost/phpmyadmin/

This system provides the same functionality as your original React/Node.js version but runs entirely on XAMPP with PHP and MySQL!