This file outlines the setup and integration of Paystack for handling payments in the Isiolo Raha Bus Booking System.

### Key Files

1.  **`paystack_callback.php`** - Handles payment verification and booking confirmation after a successful payment.

### Configuration

1.  **`config/paystack_config.php`** - Contains Paystack API keys and callback URL definitions.

    ```php
    // Paystack API Keys
    define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
    define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

    // Paystack Callback URL
    define('PAYSTACK_CALLBACK_URL', 'https://yourdomain.com/paystack_callback.php');
    ```

### Payment Flow

1.  User selects seats and proceeds to the payment page (`payment.php`).
2.  User clicks the "Pay with Paystack" button, which triggers the Paystack payment popup.
3.  After a successful payment, Paystack redirects the user to the callback URL (`paystack_callback.php`).
4.  The callback script verifies the payment with Paystack, creates the booking, and redirects to the booking confirmation page (`booking_confirmation.php`).
