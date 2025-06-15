# EventZon - XAMPP Version

## Installation Instructions

### Prerequisites
- XAMPP with PHP 7.4+ and MySQL 5.7+
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Steps

1. **Install XAMPP**
   - Download XAMPP from https://www.apachefriends.org/
   - Install XAMPP to `C:\xampp` (Windows) or `/Applications/XAMPP` (Mac)

2. **Copy Files**
   - Copy the entire `xampp-version` folder to `C:\xampp\htdocs\eventzon`
   - The structure should be: `C:\xampp\htdocs\eventzon\index.php`

3. **Start Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

4. **Database Setup**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - The database and tables will be created automatically when you first visit the site
   - Or manually create database named `eventzon`

5. **Access Application**
   - Open browser and go to: http://localhost/eventzon
   - The application should load with the homepage

### Default Configuration

- **Database Host**: localhost
- **Database Name**: eventzon  
- **Database User**: root
- **Database Password**: (empty)

### File Structure

```
C:\xampp\htdocs\eventzon\
├── index.php                 # Main entry point
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php           # Site header
│   ├── footer.php           # Site footer
│   └── functions.php        # Core functions
├── pages/
│   ├── home.php             # Homepage
│   ├── events.php           # Events listing
│   ├── event-details.php    # Event details
│   ├── login.php            # User login
│   ├── register.php         # User registration
│   ├── dashboard.php        # User dashboard
│   ├── cart.php             # Shopping cart
│   └── 404.php              # Error page
├── auth/
│   └── logout.php           # Logout handler
├── assets/
│   ├── css/
│   │   └── style.css        # Custom styles
│   └── js/
│       └── script.js        # JavaScript functions
└── README.md                # This file
```

### Features

- **Event Management**: Browse, search, and filter events
- **User Authentication**: Register, login, logout
- **Shopping Cart**: Add events to cart and checkout
- **Responsive Design**: Works on desktop and mobile
- **Secure**: Password hashing, input sanitization
- **Database**: MySQL with PDO for security

### Admin Access

To create an admin user:
1. Register a normal account
2. Go to phpMyAdmin
3. Open the `users` table
4. Change the `role` field from 'user' to 'admin' for your account

### Sample Data

To add sample events:
1. Login as admin
2. Go to dashboard
3. Use the admin panel to create events

### Troubleshooting

**Database Connection Error**
- Ensure MySQL service is running in XAMPP
- Check database credentials in `config/database.php`

**Page Not Found**
- Verify files are in correct location: `C:\xampp\htdocs\eventzon\`
- Check Apache service is running

**Functions Not Found**
- Ensure `includes/functions.php` is being loaded
- Check file permissions

### Security Notes

- Change default database credentials for production
- Enable password protection for phpMyAdmin
- Use HTTPS in production environment
- Regular backup of database

### Support

For issues or questions:
- Check XAMPP documentation
- Verify PHP and MySQL versions
- Review error logs in XAMPP control panel

### License

This EventZon XAMPP version is provided as-is for educational and development purposes.