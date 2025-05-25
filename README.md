# 🚌 Isiolo Raha Bus Booking System

A comprehensive web-based bus ticket booking system for Isiolo Raha, a local Kenyan bus company. This modern system provides a complete solution for online bus ticket booking, seat selection, payment processing, and administrative management.

## ✨ Key Features

### 🔐 User Management
- **Secure Authentication**: Email/password signup and login with bcrypt password hashing
- **User Roles**: Separate user and admin roles with different access levels
- **Session Management**: Secure session handling with automatic logout
- **Password Reset**: Token-based password recovery system

### 🎫 Booking System
- **Route Search**: Search for available buses by origin, destination, and travel date
- **Interactive Seat Selection**: Visual seat map with real-time availability
- **Individual Bookings**: Standard single or multiple passenger bookings
- **Group Bookings**: Special group booking functionality for 5+ passengers
- **Booking Management**: View, modify, and cancel bookings
- **QR Code Generation**: Automatic QR codes for booking confirmations and tickets

### 💳 Payment Integration
- **Multiple Payment Methods**: Paystack integration for card payments
- **Manual Payment**: Cash payment option for admin bookings
- **Payment Verification**: Secure payment callback handling
- **Receipt Generation**: Professional receipt printing with company logo

### 👨‍💼 Admin Panel
- **Dashboard Analytics**: Real-time statistics and booking insights
- **Bus Management**: Add, edit, delete buses with amenities and status tracking
- **Route Management**: Create and manage routes with distance and duration
- **Schedule Management**: Set up bus schedules with departure times and fares
- **Booking Management**: View all bookings, change status, process refunds
- **Group Booking Management**: Handle group bookings and passenger details
- **User Management**: View and manage user accounts
- **Reports**: Generate comprehensive booking and revenue reports
- **Feedback Management**: View and respond to customer feedback

### 🎨 User Experience
- **Responsive Design**: Mobile-first design optimized for all devices
- **Interactive UI**: Modern interface with smooth animations and transitions
- **Print-Friendly**: Optimized ticket and receipt printing
- **Help System**: Built-in help guides and tooltips
- **Loading States**: Professional loading overlays and progress indicators

## 🛠️ Technologies Used

- **Backend**: Core PHP 7.4+ (no frameworks for maximum performance)
- **Database**: MySQL 5.7+ with optimized queries and indexing
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **CSS Framework**: Tailwind CSS for responsive design
- **Payment Gateway**: Paystack API for secure payments
- **QR Code Generation**: Endroid QR Code library via Composer
- **Icons**: Font Awesome for consistent iconography
- **Development Environment**: XAMPP/LAMP stack compatible

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (or any PHP development environment)
- **PHP 7.4+** with extensions: mysqli, gd, curl
- **MySQL 5.7+**
- **Composer** (for QR code dependencies)
- **Web browser** (Chrome, Firefox, Safari, Edge)

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Hassan1910/isiolo_raha.git
   cd isiolo_raha
   ```

2. **Set Up Environment**
   - Copy the project to your `htdocs` folder (for XAMPP)
   - Start Apache and MySQL services in XAMPP Control Panel

3. **Install Dependencies**
   ```bash
   composer install
   ```

4. **Database Setup**
   - Open your browser and navigate to `http://localhost/isioloraha/setup.php`
   - The setup script will automatically create the database and sample data
   - Alternatively, import `isioloraha.sql` directly into MySQL

5. **Configuration**
   - Update database credentials in `config/database.php` if needed
   - Configure Paystack keys in `config/config.php` for payment processing

6. **Access the System**
   - **Main Site**: `http://localhost/isioloraha`
   - **Admin Panel**: `http://localhost/isioloraha/admin`

### Default Credentials
- **Admin Email**: admin@isioloraha.com
- **Admin Password**: admin123
- **Change these credentials immediately after first login!**

## 📁 Project Structure

```
isioloraha/
├── 📁 admin/                    # Admin Panel
│   ├── index.php               # Admin dashboard with analytics
│   ├── buses.php               # Bus management (CRUD)
│   ├── routes.php              # Route management
│   ├── schedules.php           # Schedule management
│   ├── bookings.php            # Booking management
│   ├── group_bookings.php      # Group booking management
│   ├── reports.php             # Analytics and reports
│   ├── feedback.php            # Customer feedback
│   └── create_booking.php      # Admin booking creation
├── 📁 assets/                   # Static Assets
│   ├── css/
│   │   ├── style.css           # Main stylesheet
│   │   └── responsive.css      # Mobile responsiveness
│   ├── js/
│   │   ├── main.js             # Core JavaScript
│   │   └── form-validation.js  # Form validation
│   └── images/
│       └── isioloraha logo.png # Company logo
├── 📁 config/                   # Configuration
│   ├── config.php              # App settings & Paystack keys
│   ├── database.php            # Database connection
│   ├── session_config.php      # Session management
│   └── init_db.php             # Database initialization
├── 📁 includes/                 # Reusable Components
│   ├── components/
│   │   ├── bus_layout.php      # Seat selection component
│   │   ├── booking_progress.php # Booking progress indicator
│   │   ├── help_guide.php      # Help system
│   │   └── loading_overlay.php # Loading animations
│   ├── templates/
│   │   ├── header.php          # Site header
│   │   ├── footer.php          # Site footer
│   │   ├── admin_header.php    # Admin panel header
│   │   └── admin_sidebar.php   # Admin navigation
│   └── functions.php           # Utility functions
├── 📁 user/                     # User Dashboard
│   ├── dashboard.php           # User dashboard
│   ├── bookings.php            # Booking history
│   └── profile.php             # User profile management
├── 📁 vendor/                   # Composer Dependencies
│   └── endroid/qr-code/        # QR code generation library
├── 🏠 Core Pages
│   ├── index.php               # Homepage
│   ├── login.php               # User authentication
│   ├── register.php            # User registration
│   ├── search_results.php      # Bus search results
│   ├── select_seats.php        # Interactive seat selection
│   ├── passenger_details.php   # Passenger information
│   ├── payment.php             # Payment processing
│   ├── booking_confirmation.php # Booking success page
│   ├── group_booking.php       # Group booking interface
│   ├── print_ticket.php        # Ticket printing
│   └── generate_qr.php         # QR code generation
├── 🛠️ Setup & Utilities
│   ├── setup.php               # Database setup script
│   ├── isioloraha.sql          # Database schema
│   ├── composer.json           # PHP dependencies
│   └── .htaccess               # Apache configuration
└── 📖 README.md                # This documentation
```

## 📖 Usage Guide

### 👤 For Customers

#### 1. **Account Management**
- **Registration**: Create account with email, phone, and personal details
- **Login**: Secure authentication with session management
- **Profile**: Update personal information and contact details

#### 2. **Individual Booking Process**
1. **Search**: Select origin, destination, and travel date
2. **Browse**: View available buses with amenities and pricing
3. **Select Seats**: Interactive seat map with real-time availability
4. **Passenger Details**: Enter traveler information
5. **Payment**: Secure payment via Paystack (cards, mobile money)
6. **Confirmation**: Receive booking confirmation with QR code
7. **Ticket**: Print or download ticket with QR code

#### 3. **Group Booking Process**
1. **Group Setup**: Select "Group Booking" for 5+ passengers
2. **Schedule Selection**: Choose from available schedules
3. **Seat Selection**: Select multiple seats on visual seat map
4. **Group Details**: Enter group name and contact person
5. **Passenger Information**: Add details for each passenger
6. **Payment**: Process group payment
7. **Management**: Track group booking status

#### 4. **User Dashboard**
- **Booking History**: View all past and upcoming bookings
- **Booking Details**: Check status, seat numbers, and travel info
- **Ticket Access**: Reprint tickets and view QR codes
- **Profile Management**: Update account information

### 👨‍💼 For Administrators

#### 1. **Dashboard Analytics**
- **Real-time Statistics**: Users, bookings, revenue metrics
- **Recent Activity**: Latest bookings and system activity
- **Performance Metrics**: Booking trends and popular routes
- **Quick Actions**: Access to key management functions

#### 2. **Fleet Management**
- **Bus Registration**: Add new buses with registration numbers
- **Amenities Setup**: Configure bus features (AC, WiFi, USB charging)
- **Capacity Management**: Set seating arrangements and layouts
- **Status Tracking**: Active, maintenance, or inactive status
- **Bus Analytics**: Performance and utilization reports

#### 3. **Route & Schedule Management**
- **Route Creation**: Define origin-destination pairs with distances
- **Schedule Setup**: Set departure/arrival times and frequencies
- **Fare Management**: Configure pricing for different bus types
- **Seasonal Adjustments**: Modify schedules for peak periods

#### 4. **Booking Administration**
- **Booking Overview**: View all bookings with filtering options
- **Status Management**: Confirm, cancel, or modify bookings
- **Manual Booking**: Create bookings for walk-in customers
- **Group Booking Management**: Handle large group reservations
- **Payment Tracking**: Monitor payment status and refunds

#### 5. **Reporting & Analytics**
- **Revenue Reports**: Daily, weekly, monthly financial summaries
- **Booking Analytics**: Popular routes and peak travel times
- **Customer Insights**: User behavior and booking patterns
- **Operational Reports**: Bus utilization and performance metrics

#### 6. **Customer Service**
- **Feedback Management**: View and respond to customer feedback
- **Booking Support**: Assist customers with booking issues
- **Refund Processing**: Handle cancellations and refunds
- **Communication**: Send notifications and updates

## 🔒 Security Features

- **Password Security**: Bcrypt hashing with salt for all user passwords
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Token-based form validation
- **Session Security**: Secure session handling with automatic timeout
- **Payment Security**: PCI-compliant payment processing via Paystack
- **Access Control**: Role-based permissions (user/admin)
- **Data Validation**: Server-side validation for all user inputs
- **Secure Headers**: HTTP security headers implementation
- **Activity Logging**: Comprehensive audit trail for admin actions

## 🗄️ Database Schema

The system uses a well-structured MySQL database with the following key tables:

- **users**: User accounts and authentication
- **buses**: Fleet management and bus information
- **routes**: Origin-destination route definitions
- **schedules**: Bus schedules with timing and pricing
- **bookings**: Individual booking records
- **group_bookings**: Group booking management
- **booking_passengers**: Passenger details for bookings
- **payments**: Payment transaction records
- **feedback**: Customer feedback and reviews
- **activity_logs**: System activity audit trail

## 🚀 Performance Features

- **Optimized Queries**: Indexed database queries for fast performance
- **Caching**: Session-based caching for frequently accessed data
- **Responsive Design**: Mobile-first approach for all devices
- **Lazy Loading**: Efficient loading of images and components
- **Minified Assets**: Compressed CSS and JavaScript files
- **CDN Integration**: Font Awesome and external libraries via CDN

## 🛠️ Development & Deployment

### Local Development
```bash
# Clone repository
git clone https://github.com/Hassan1910/isiolo_raha.git

# Install dependencies
composer install

# Set up database
php setup.php

# Start development server (XAMPP)
# Access: http://localhost/isioloraha
```

### Production Deployment
1. **Server Requirements**: PHP 7.4+, MySQL 5.7+, Apache/Nginx
2. **SSL Certificate**: Required for payment processing
3. **Environment Variables**: Configure database and API keys
4. **File Permissions**: Set appropriate permissions for uploads
5. **Backup Strategy**: Regular database and file backups

## 🤝 Contributing

We welcome contributions to improve the Isiolo Raha Bus Booking System!

### How to Contribute
1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** to the branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards for PHP
- Write clear, commented code
- Test all functionality before submitting
- Update documentation for new features

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

## 📞 Support & Contact

### Technical Support
- **Email**: support@isioloraha.com
- **Phone**: +254 700 000 000
- **GitHub Issues**: [Report bugs or request features](https://github.com/Hassan1910/isiolo_raha/issues)

### Business Inquiries
- **Email**: info@isioloraha.com
- **Website**: [www.isioloraha.com](http://www.isioloraha.com)
- **Address**: Isiolo, Kenya

---

**Made with ❤️ for the Isiolo community**

*Simplifying bus travel, one booking at a time.*
