<?php
/**
 * VIP Mail Configuration File
 * 
 * This file reads configuration from environment variables (Vercel)
 * or falls back to hardcoded values for local development
 */

// Helper function to get environment variables
function getEnv($key, $default = '') {
    // Try to get from $_ENV first (Vercel)
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    
    // Try getenv() as fallback
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    
    // Return default value
    return $default;
}

// hCaptcha Configuration
define('HCAPTCHA_SITE_KEY', getEnv('HCAPTCHA_SITE_KEY', ''));
define('HCAPTCHA_SECRET_KEY', getEnv('HCAPTCHA_SECRET_KEY', ''));

// Telegram Bot Configuration
define('TELEGRAM_BOT_TOKEN', getEnv('TELEGRAM_BOT_TOKEN', ''));
define('TELEGRAM_CHAT_ID', getEnv('TELEGRAM_CHAT_ID', ''));

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);

// Security Configuration
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_MAX_REQUESTS', 5);
define('RATE_LIMIT_TIME_WINDOW', 3600);

// Email Configuration
define('ADMIN_EMAIL', getEnv('ADMIN_EMAIL', 'support@vipm.org'));
define('FROM_EMAIL', getEnv('FROM_EMAIL', 'noreply@vipm.org'));

// Environment Detection
define('ENVIRONMENT', getEnv('VERCEL_ENV', 'development')); // Vercel auto-sets this
define('DEBUG_MODE', ENVIRONMENT === 'development');

// PayPal Configuration
define('PAYPAL_EMAIL', getEnv('PAYPAL_EMAIL', '3nour6@gmail.com'));

// InstaPay Configuration
define('INSTAPAY_ACCOUNT', getEnv('INSTAPAY_ACCOUNT', 'nourehabfaroukhassan@instapay'));
define('INSTAPAY_PHONE', getEnv('INSTAPAY_PHONE', '+201158720470'));

// Timezone
date_default_timezone_set('Africa/Cairo');

/**
 * Validation function
 */
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
    
    return $errors;
}

// Only validate and show errors in development mode
if (DEBUG_MODE) {
    $configErrors = validateConfig();
    if (!empty($configErrors)) {
        error_log("Configuration Errors: " . implode(", ", $configErrors));
        
        // Show errors only in dev mode
        if (php_sapi_name() === 'cli') {
            echo "Configuration Errors:\n";
            foreach ($configErrors as $error) {
                echo "- $error\n";
            }
        }
    }
}

/**
 * DOCUMENTATION:
 * 
 * How to setup Environment Variables in Vercel:
 * 
 * 1. Go to your Vercel project dashboard
 * 2. Click on "Settings" tab
 * 3. Select "Environment Variables" from left sidebar
 * 4. Add the following variables:
 * 
 *    HCAPTCHA_SECRET_KEY = your_secret_key
 *    TELEGRAM_BOT_TOKEN = 123456789:ABCdefGHIjklMNOpqrsTUVwxyz
 *    TELEGRAM_CHAT_ID = 123456789
 *    
 *    Optional:
 *    ADMIN_EMAIL = your@email.com
 *    PAYPAL_EMAIL = your@paypal.com
 *    INSTAPAY_ACCOUNT = username@instapay
 *    INSTAPAY_PHONE = +201234567890
 * 
 * 5. Select which environments to apply them to:
 *    - Production (required)
 *    - Preview (recommended)
 *    - Development (optional)
 * 
 * 6. Click "Save"
 * 7. Redeploy your project for changes to take effect
 * 
 * For local development:
 * - Create a .env.local file (add to .gitignore)
 * - Add the same variables there
 * - PHP will read them automatically using getenv()
 */
?>
