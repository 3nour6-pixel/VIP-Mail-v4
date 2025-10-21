<?php
/**
 * VIP Mail Configuration File
 * 
 * IMPORTANT: This file contains sensitive credentials.
 * Never commit this file to version control!
 * Add config.php to .gitignore
 */

// hCaptcha Configuration
define('HCAPTCHA_SITE_KEY', '21167ab8-26cc-4d12-b60c-f54c98f6efa0');
define('HCAPTCHA_SECRET_KEY', 'ES_af02552a87824384a3ec24ec0848c75b'); // ADD YOUR SECRET KEY HERE

// Telegram Bot Configuration
define('TELEGRAM_BOT_TOKEN', '8496412644:AAFJNZ62xXo0COxqVTx034wvyS3zl0ZIWdI'); // ADD YOUR BOT TOKEN HERE (format: 123456789:ABCdefGHIjklMNOpqrsTUVwxyz)
define('TELEGRAM_CHAT_ID', '7522528768'); // ADD YOUR CHAT ID HERE (format: 123456789 or -123456789 for groups)

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Security Configuration
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_MAX_REQUESTS', 5); // Max requests per IP
define('RATE_LIMIT_TIME_WINDOW', 3600); // Time window in seconds (1 hour)

// Email Configuration (optional - for future use)
define('ADMIN_EMAIL', 'support@vipm.org');
define('FROM_EMAIL', 'noreply@vipm.org');

// Environment
define('ENVIRONMENT', 'production'); // 'development' or 'production'
define('DEBUG_MODE', false); // Set to false in production

// PayPal Configuration
define('PAYPAL_EMAIL', '3nour6@gmail.com');

// InstaPay Configuration
define('INSTAPAY_ACCOUNT', 'nourehabfaroukhassan@instapay');
define('INSTAPAY_PHONE', '+201158720470');

// Timezone
date_default_timezone_set('Africa/Cairo');

/**
 * How to get your Telegram Bot Token and Chat ID:
 * 
 * 1. Create a Telegram Bot:
 *    - Open Telegram and search for @BotFather
 *    - Send /newbot command
 *    - Follow instructions to create your bot
 *    - Copy the bot token (looks like: 123456789:ABCdefGHIjklMNOpqrsTUVwxyz)
 * 
 * 2. Get your Chat ID:
 *    - Send a message to your bot
 *    - Visit: https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
 *    - Look for "chat":{"id": YOUR_CHAT_ID
 *    - Copy the chat ID number
 * 
 * 3. For Group/Channel:
 *    - Add your bot to the group/channel as admin
 *    - Send a message in the group
 *    - Visit the getUpdates URL above
 *    - Group IDs usually start with a minus sign (e.g., -123456789)
 * 
 * How to get hCaptcha Secret Key:
 * 
 * 1. Login to hCaptcha dashboard: https://dashboard.hcaptcha.com/
 * 2. Go to your site settings
 * 3. Copy the Secret Key
 */

// Validation function
function validateConfig() {
    $errors = [];
    
    if (empty(HCAPTCHA_SECRET_KEY)) {
        $errors[] = 'HCAPTCHA_SECRET_KEY is not configured';
    }
    
    if (empty(TELEGRAM_BOT_TOKEN)) {
        $errors[] = 'TELEGRAM_BOT_TOKEN is not configured';
    }
    
    if (empty(TELEGRAM_CHAT_ID)) {
        $errors[] = 'TELEGRAM_CHAT_ID is not configured';
    }
    
    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            $errors[] = 'Unable to create uploads directory';
        }
    }
    
    if (!is_writable(UPLOAD_DIR)) {
        $errors[] = 'Uploads directory is not writable';
    }
    
    return $errors;
}

// Only validate in development mode
if (ENVIRONMENT === 'development' && DEBUG_MODE) {
    $configErrors = validateConfig();
    if (!empty($configErrors)) {
        echo "Configuration Errors:\n";
        foreach ($configErrors as $error) {
            echo "- $error\n";
        }
    }
}
?>