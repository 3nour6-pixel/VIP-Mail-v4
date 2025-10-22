<?php
/**
 * VIP Mail Payment API v1
 * Handles payment form submissions and sends notifications to Telegram
 */

// Load configuration file
require_once __DIR__ . '/config.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Send JSON response and exit
 */
function sendResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Validate hCaptcha response
 */
function verifyHCaptcha($token) {
    if (empty(HCAPTCHA_SECRET_KEY)) {
        error_log('hCaptcha secret key is not configured');
        return false;
    }
    
    $data = [
        'secret' => HCAPTCHA_SECRET_KEY,
        'response' => $token
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents('https://hcaptcha.com/siteverify', false, $context);
    
    if ($result === false) {
        error_log('Failed to verify hCaptcha: connection error');
        return false;
    }
    
    $response = json_decode($result);
    return $response && $response->success === true;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Validate desired email format
 */
function validateDesiredEmail($desiredEmail) {
    // Check if empty
    if (empty($desiredEmail)) {
        return false;
    }
    
    // Check length
    if (strlen($desiredEmail) < 3 || strlen($desiredEmail) > 64) {
        return false;
    }
    
    // Check format (only lowercase letters, numbers, dots, underscores, hyphens)
    if (!preg_match('/^[a-z0-9._-]+$/', $desiredEmail)) {
        return false;
    }
    
    // Check for consecutive dots
    if (strpos($desiredEmail, '..') !== false) {
        return false;
    }
    
    // Check if starts or ends with dot or hyphen
    if (preg_match('/^[.-]|[.-]$/', $desiredEmail)) {
        return false;
    }
    
    return true;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Send message to Telegram with photo
 */
function sendToTelegram($photoPath, $email, $phone, $paymentMethod, $paymentType = null) {
    if (empty(TELEGRAM_BOT_TOKEN) || empty(TELEGRAM_CHAT_ID)) {
        error_log('Telegram credentials are not configured');
        return false;
    }
    
    // Check if file exists
    if (!file_exists($photoPath)) {
        error_log('Photo file does not exist: ' . $photoPath);
        return false;
    }
    
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendPhoto";
    
    // Prepare caption (max 1024 characters for Telegram)
    $caption = "🎉 *New VIP Mail Payment Request*\n\n";
    $caption .= "💳 *Payment Method:* " . ucfirst($paymentMethod) . "\n";
    
    if ($paymentType) {
        $caption .= "📋 *Payment Type:* " . ucfirst(str_replace('_', ' ', $paymentType)) . "\n";
    }
    
    $caption .= "📧 *Email:* " . $email . "\n";
    $caption .= "📱 *Phone:* " . $phone . "\n";
    $caption .= "⏰ *Time:* " . date('Y-m-d H:i:s') . "\n";
    
    // Prepare post fields with CURLFile
    $post_fields = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'caption' => $caption,
        'parse_mode' => 'Markdown',
        'photo' => new CURLFile(realpath($photoPath))
    ];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    // Execute request
    $result = curl_exec($ch);
    
    // Check for cURL errors
    if ($result === false) {
        $error = curl_error($ch);
        error_log('cURL Error: ' . $error);
        curl_close($ch);
        return false;
    }
    
    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log response for debugging
    error_log('Telegram API Response: ' . $result);
    
    // Check HTTP status code
    if ($httpCode !== 200) {
        error_log('Telegram API error. HTTP Code: ' . $httpCode . ', Response: ' . $result);
        return false;
    }
    
    // Parse response
    $response = json_decode($result, true);
    
    // Check if response is valid
    if (isset($response['ok']) && $response['ok'] === true) {
        return true;
    } else {
        $errorMsg = isset($response['description']) ? $response['description'] : 'Unknown error';
        error_log('Telegram API returned error: ' . $errorMsg);
        return false;
    }
}

// Main execution
try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method');
    }
    
    // Create uploads directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Verify hCaptcha
    $hcaptchaResponse = $_POST['h-captcha-response'] ?? '';
    if (empty($hcaptchaResponse)) {
        sendResponse(false, 'Captcha verification is required');
    }
    
    if (!verifyHCaptcha($hcaptchaResponse)) {
        sendResponse(false, 'Captcha verification failed. Please try again.');
    }
    
    // Get and validate email
    $email = $_POST['email'] ?? '';
    if (empty($email) || !validateEmail($email)) {
        sendResponse(false, 'Invalid email address');
    }
    $email = sanitizeInput($email);
    
    // Get and validate phone
    $phone = $_POST['phone'] ?? '';
    if (empty($phone) || !validatePhone($phone)) {
        sendResponse(false, 'Invalid phone number');
    }
    $phone = sanitizeInput($phone);
    
    // Get payment method
    $paymentMethod = $_POST['payment_method'] ?? '';
    if (!in_array($paymentMethod, ['paypal', 'instapay'])) {
        sendResponse(false, 'Invalid payment method');
    }
    
    // Get payment type (for PayPal)
    $paymentType = $_POST['paypal-type'] ?? null;
    
    // Validate file upload
    if (!isset($_FILES['screenshot']) || $_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, 'Payment screenshot is required');
    }
    
    $file = $_FILES['screenshot'];
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        sendResponse(false, 'File size exceeds maximum limit (5MB)');
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        sendResponse(false, 'Invalid file type. Only images are allowed.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('payment_', true) . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        sendResponse(false, 'Failed to upload file. Please try again.');
    }
    
    // Send to Telegram
    $telegramSuccess = sendToTelegram($filepath, $email, $phone, $paymentMethod, $paymentType);
    
    if (!$telegramSuccess) {
        // If Telegram fails, still keep the file but log the error
        error_log('Failed to send notification to Telegram for email: ' . $email);
        // Don't delete the file, admin can check uploads folder
        sendResponse(false, 'Request received but notification failed. We will process your request manually.');
    }
    
    // Optional: Delete file after successful Telegram send (uncomment if you want to auto-delete)
    // @unlink($filepath);
    
    // Success response
    sendResponse(true, 'Your payment request has been submitted successfully!', [
        'email' => $email,
        'phone' => $phone,
        'payment_method' => $paymentMethod
    ]);
    
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendResponse(false, 'An unexpected error occurred. Please try again later.');
}
?>
