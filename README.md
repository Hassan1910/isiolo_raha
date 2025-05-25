# Isiolo Raha Bus Booking System

A comprehensive web-based bus ticket booking system for Isiolo Raha, a local Kenyan bus company. This system allows users to search for routes, view real-time seat availability, book seats, and make payments online.

## Features

- **User Authentication**: Secure signup/login system with password hashing and session handling
- **Route Search**: Search for available buses based on origin, destination, and date
- **Seat Selection**: Interactive seat selection with real-time availability
- **Online Booking**: Complete booking process with passenger details
- **Payment Integration**: Secure online payments via Paystack
- **User Dashboard**: View booking history and manage profile
- **Admin Dashboard**: Manage buses, routes, schedules, bookings, and users
- **Reports**: Generate daily/weekly/monthly reports on bookings and revenue
- **Responsive Design**: Mobile-first design using Tailwind CSS

## Technologies Used

- **Backend**: Core PHP (no frameworks)
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **CSS Framework**: Tailwind CSS
- **Payment Gateway**: Paystack

## Installation

1. **Prerequisites**:
   - XAMPP (or any PHP development environment)
   - MySQL
   - Web browser

2. **Setup**:
   - Clone or download this repository to your `htdocs` folder (for XAMPP)
   - Start Apache and MySQL services in XAMPP
   - Open your browser and navigate to `http://localhost/isioloraha/setup.php` to set up the database and sample data
   - After setup is complete, you can access the system at `http://localhost/isioloraha`

3. **Default Admin Credentials**:
   - Email: admin@isioloraha.com
   - Password: admin123

## Project Structure

```
isioloraha/
├── admin/                  # Admin dashboard files
├── assets/                 # Static assets
│   ├── css/                # CSS files
│   ├── js/                 # JavaScript files
│   └── images/             # Image files
├── config/                 # Configuration files
│   ├── config.php          # Application configuration
│   ├── database.php        # Database connection
│   └── init_db.php         # Database initialization
├── includes/               # Reusable components
│   ├── classes/            # PHP classes
│   ├── templates/          # Header, footer, etc.
│   └── functions.php       # Utility functions
├── user/                   # User dashboard files
├── index.php               # Homepage
├── login.php               # Login page
├── register.php            # Registration page
├── search_results.php      # Search results page
├── select_seats.php        # Seat selection page
├── passenger_details.php   # Passenger details page
├── payment.php             # Payment page
├── verify_payment.php      # Payment verification
├── booking_confirmation.php # Booking confirmation
├── contact.php             # Contact page
├── setup.php               # Setup script
└── README.md               # Project documentation
```

## Usage

### For Users

1. **Registration/Login**:
   - Create a new account or log in with existing credentials

2. **Booking a Ticket**:
   - Search for buses by selecting origin, destination, and travel date
   - Choose a bus from the search results
   - Select your preferred seat(s)
   - Enter passenger details
   - Make payment using Paystack
   - Receive booking confirmation

3. **Managing Bookings**:
   - View all your bookings in the user dashboard
   - Check booking details and status
   - Print or download tickets

### For Administrators

1. **Dashboard**:
   - View system statistics and recent bookings

2. **Manage Buses**:
   - Add, edit, or delete buses
   - Update bus status (active, maintenance, inactive)

3. **Manage Routes**:
   - Add, edit, or delete routes
   - Set distance and duration

4. **Manage Schedules**:
   - Create bus schedules for specific routes
   - Set departure and arrival times, fares

5. **Manage Bookings**:
   - View all bookings
   - Change booking status
   - Generate reports

## Security Features

- Password hashing using bcrypt
- Prepared statements to prevent SQL injection
- CSRF protection
- XSS prevention
- Secure session handling

## License

This project is licensed under the MIT License.

## Contact

For any inquiries or support, please contact:
- Email: info@isioloraha.com
- Phone: +254 700 000 000
