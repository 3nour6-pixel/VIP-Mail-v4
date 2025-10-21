<?php
/**
 * Telegram Bot Test Script
 * This file helps you test your Telegram bot configuration
 * DELETE THIS FILE AFTER TESTING!
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Enable error display for testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>VIP Mail - Telegram Bot Test</h1>";
echo "<hr>";

// Test 1: Check if credentials are set
echo "<h2>Test 1: Configuration Check</h2>";
if (empty(TELEGRAM_BOT_TOKEN)) {
    echo "<p style='color: red;'>❌ TELEGRAM_BOT_TOKEN is not set!</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ TELEGRAM_BOT_TOKEN is set</p>";
}

if (empty(TELEGRAM_CHAT_ID)) {
    echo "<p style='color: red;'>❌ TELEGRAM_CHAT_ID is not set!</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ TELEGRAM_CHAT_ID is set</p>";
}

// Test 2: Test bot connection
echo "<h2>Test 2: Bot Connection</h2>";
$url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/getMe";
$response = file_get_contents($url);
$result = json_decode($response, true);

if (isset($result['ok']) && $result['ok'] === true) {
    echo "<p style='color: green;'>✅ Bot is active!</p>";
    echo "<pre>";
    echo "Bot Username: @" . $result['result']['username'] . "\n";
    echo "Bot Name: " . $result['result']['first_name'] . "\n";
    echo "Bot ID: " . $result['result']['id'] . "\n";
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Bot connection failed!</p>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    exit;
}

// Test 3: Send test message
echo "<h2>Test 3: Send Test Message</h2>";
$testUrl = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
$testData = [
    'chat_id' => TELEGRAM_CHAT_ID,
    'text' => "🧪 Test message from VIP Mail\n\nIf you see this, your bot is working correctly! ✅\n\nTime: " . date('Y-m-d H:i:s'),
    'parse_mode' => 'Markdown'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($testData)
    ]
];

$context = stream_context_create($options);
$testResponse = file_get_contents($testUrl, false, $context);
$testResult = json_decode($testResponse, true);

if (isset($testResult['ok']) && $testResult['ok'] === true) {
    echo "<p style='color: green;'>✅ Test message sent successfully!</p>";
    echo "<p>Check your Telegram chat to see the message.</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send test message!</p>";
    echo "<pre>" . print_r($testResult, true) . "</pre>";
}

// Test 4: Check cURL support
echo "<h2>Test 4: cURL Support</h2>";
if (function_exists('curl_init')) {
    echo "<p style='color: green;'>✅ cURL is installed</p>";
    
    // Test cURL version
    $curlVersion = curl_version();
    echo "<pre>";
    echo "cURL Version: " . $curlVersion['version'] . "\n";
    echo "SSL Version: " . $curlVersion['ssl_version'] . "\n";
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ cURL is NOT installed!</p>";
    echo "<p>You need to install/enable cURL extension in PHP</p>";
}

// Test 5: Check file upload support
echo "<h2>Test 5: File Upload Configuration</h2>";
echo "<pre>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "s\n";
echo "</pre>";

// Test 6: Check uploads directory
echo "<h2>Test 6: Uploads Directory</h2>";
$uploadDir = __DIR__ . '/../uploads/';
if (is_dir($uploadDir)) {
    echo "<p style='color: green;'>✅ Uploads directory exists</p>";
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✅ Uploads directory is writable</p>";
    } else {
        echo "<p style='color: red;'>❌ Uploads directory is NOT writable!</p>";
        echo "<p>Run: chmod 755 " . $uploadDir . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Uploads directory does not exist!</p>";
    echo "<p>Create it with: mkdir " . $uploadDir . "</p>";
}

echo "<hr>";
echo "<h2>✅ Testing Complete!</h2>";
echo "<p><strong>⚠️ IMPORTANT: Delete this test file (test-telegram.php) after testing!</strong></p>";
echo "<p>If all tests passed, your setup is ready to use.</p>";
?>