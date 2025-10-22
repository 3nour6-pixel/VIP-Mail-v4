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
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Send message to Telegram with photo directly from temp file
 */
function sendToTelegram($tempPhotoPath, $originalFilename, $email, $phone, $paymentMethod, $paymentType = null) {
    if (empty(TELEGRAM_BOT_TOKEN) || empty(TELEGRAM_CHAT_ID)) {
        error_log('Telegram credentials are not configured');
        return false;
    }
    
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendPhoto";
    
    // Prepare caption
    $caption = "🎉 <b>New VIP Mail Payment Request</b>\n\n";
    $caption .= "💳 <b>Payment Method:</b> " . ucfirst($paymentMethod) . "\n";
    
    if ($paymentType) {
        $caption .= "📋 <b>Payment Type:</b> " . ucfirst($paymentType) . "\n";
    }
    
    $caption .= "📧 <b>Email:</b> " . $email . "\n";
    $caption .= "📱 <b>Phone:</b> " . $phone . "\n";
    $caption .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s') . "\n";
    
    // Get file extension from original filename
    $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
    $mimetype = mime_content_type($tempPhotoPath);
    
    // Create CURLFile with proper filename
    $cfile = new CURLFile($tempPhotoPath, $mimetype, 'payment.' . $extension);
    
    // Prepare file upload
    $post_fields = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'caption' => $caption,
        'parse_mode' => 'HTML',
        'photo' => $cfile
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log('Telegram API error. HTTP Code: ' . $httpCode . ', Response: ' . $result);
        return false;
    }
    
    $response = json_decode($result, true);
    return isset($response['ok']) && $response['ok'] === true;
}

// Main execution
try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method');
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
    
    // Send to Telegram directly from temp file
    $telegramSuccess = sendToTelegram(
        $file['tmp_name'], 
        $file['name'], 
        $email, 
        $phone, 
        $paymentMethod, 
        $paymentType
    );
    
    if (!$telegramSuccess) {
        error_log('Failed to send notification to Telegram for email: ' . $email);
        sendResponse(false, 'Failed to send notification. Please try again.');
    }
    
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
