# EventZon Admin Panel - XAMPP Version

## Overview
The EventZon Admin Panel provides comprehensive administrative functionality for managing events, bookings, and generating reports in the XAMPP environment.

## Features

### 📊 Dashboard
- Real-time analytics overview
- Key performance metrics (events, bookings, revenue)
- Visual charts and graphs
- Recent activity summaries

### 🎪 Event Management
- **Add New Events**: Complete form with live preview
- **Edit Events**: Modify existing events with validation
- **Delete Events**: Remove events with confirmation
- **Search & Filter**: Find events by title, category, or date
- **Live Preview**: See how events will appear to users

### 👥 Booking Management
- **View All Bookings**: Comprehensive booking list
- **Status Management**: Update booking status (pending/confirmed/cancelled)
- **Filter Options**: Filter by status, event, or date range
- **Export Functionality**: Download booking data as CSV

### 📈 Reports & Analytics
- **Multiple Report Types**: Events, bookings, revenue, and users
- **Date Range Filtering**: Generate reports for specific periods
- **CSV Export**: Download reports in CSV format
- **Visual Analytics**: Charts showing trends and patterns

## Access Requirements

### Admin User Creation
1. Run the database installation script: `http://localhost/xampp-version/install_database.php`
2. This creates an admin user:
   - **Email**: admin@eventzon.cm
   - **Password**: admin123
   - **Role**: admin

### Admin Panel Access
- **URL**: `http://localhost/xampp-version/admin/admin.php`
- **Login Required**: Must be logged in with admin role
- **Auto-redirect**: Non-admin users are redirected to login

## File Structure

```
xampp-version/admin/
├── admin.php          # Main admin panel (dashboard, events, bookings, reports)
├── event-form.php     # Event creation/editing form
├── export.php         # CSV export functionality
└── README.md          # This documentation

xampp-version/assets/css/
└── admin.css          # Admin panel styling

xampp-version/includes/
└── auth.php           # Authentication functions
```

## Navigation

### Sidebar Menu
- **Dashboard**: Analytics overview and key metrics
- **Events**: Manage all events (create, edit, delete)
- **Bookings**: View and manage all bookings
- **Reports**: Generate and export various reports

### Quick Actions
- **View Site**: Navigate to public website
- **Logout**: Secure logout functionality

## Event Management Features

### Create/Edit Events
- **Required Fields**: Title, category, venue, city, region, date, price, tickets
- **Optional Fields**: Description, image URL
- **Auto-Selection**: Region auto-selects based on city choice
- **Live Preview**: Real-time preview of event appearance
- **Validation**: Form validation with helpful error messages

### Event Categories
- Music
- Business  
- Technology
- Arts
- Sports
- Food

### Cameroon Locations
**Regions**: Centre, Littoral, North-West, West, North, Far North, Adamawa, East, South, South-West

**Major Cities**: Yaoundé, Douala, Bamenda, Bafoussam, Garoua, Maroua, Ngaoundéré, Bertoua, Ebolowa, Kribi

## Booking Management Features

### Status Types
- **Pending**: Awaiting confirmation
- **Confirmed**: Approved and valid
- **Cancelled**: Cancelled by admin or user

### Bulk Actions
- Filter by status or event
- Export selected bookings
- Update multiple booking statuses

## Reports & Analytics

### Available Reports
1. **Events Report**: Complete event data with organizer info
2. **Bookings Report**: Detailed booking information
3. **Revenue Report**: Financial analytics by event
4. **Users Report**: User activity and spending data

### Export Features
- CSV format for easy data analysis
- Date range filtering
- Customizable report parameters

## Security Features

### Authentication
- Session-based authentication
- Password hashing with PHP's password_hash()
- Admin role verification
- Automatic session management

### Access Control
- Admin-only access to panel
- Secure logout functionality
- Session timeout handling

## Styling & UX

### Modern Interface
- Bootstrap 5 framework
- Custom CSS enhancements
- Responsive design for mobile/tablet
- Interactive elements with hover effects

### User Experience
- Loading animations
- Confirmation dialogs for destructive actions
- Toast notifications for user feedback
- Intuitive navigation and layout

## Chart & Analytics

### Dashboard Charts
- **Doughnut Chart**: Events by category distribution
- **Line Chart**: Monthly revenue trends
- **Stat Cards**: Key performance indicators

### Chart Libraries
- Chart.js for interactive visualizations
- Responsive design for all screen sizes
- Color-coded data representation

## Installation Steps

1. **Copy Files**: Place xampp-version folder in htdocs
2. **Run Installer**: Visit install_database.php to create database
3. **Login**: Use admin credentials to access admin panel
4. **Start Managing**: Begin creating events and managing bookings

## Browser Compatibility
- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 44+

## Performance Optimization
- Efficient SQL queries with proper indexing
- Minimal JavaScript for fast loading
- Optimized CSS with utility classes
- Lazy loading for large datasets

## Troubleshooting

### Common Issues
1. **Database Connection**: Ensure MySQL is running on port 3307
2. **Permission Errors**: Check htdocs folder permissions
3. **Session Issues**: Verify PHP session configuration
4. **Chart Loading**: Ensure Chart.js CDN is accessible

### Support Features
- Detailed error messages
- Fallback displays for missing data
- Graceful handling of edge cases

This admin panel provides a complete solution for managing the EventZon platform in the XAMPP environment, with professional-grade features and user experience.