<?php
/**
 * Production Paystack Configuration
 * 
 * IMPORTANT: Replace the test keys below with your actual live Paystack keys
 * You can get these from your Paystack Dashboard at https://dashboard.paystack.com
 */

// Production Paystack API settings - REPLACE WITH YOUR LIVE KEYS
define('PAYSTACK_PUBLIC_KEY', 'pk_live_your_live_public_key_here');
define('PAYSTACK_SECRET_KEY', 'sk_live_your_live_secret_key_here');
define('PAYSTACK_CURRENCY', 'KES');

// Production webhook settings
define('PAYSTACK_WEBHOOK_SECRET', 'your_webhook_secret_here');

// Production callback URLs
define('PAYSTACK_CALLBACK_URL', APP_URL . '/paystack_callback.php');
define('PAYSTACK_CANCEL_URL', APP_URL . '/payment.php?status=cancelled');

// Additional production settings
define('PAYSTACK_ENVIRONMENT', 'live');
define('PAYSTACK_API_URL', 'https://api.paystack.co');

?>