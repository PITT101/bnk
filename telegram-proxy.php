<?php
/**
 * telegram-proxy.php — Server-side proxy for forwarding form data to Telegram.
 *
 * The bot token and chat ID live ONLY here (never exposed to the browser).
 * The client posts a "text" parameter; this script injects the credentials
 * and relays the message to the Telegram Bot API.
 */

header('Content-Type: application/json');

// --- Configure your Telegram bot credentials here ---
$botToken = '8987871839:AAEibtwHtRAJW5lUSRC78jERfceqjxL43cc';
$chatId   = '844962683';

// --- Read the text payload from the POST request ---
$text = '';
if (isset($_POST['text'])) {
    $text = trim($_POST['text']);
} else {
    // Accept JSON body as fallback
    $json = json_decode(file_get_contents('php://input'), true);
    if (is_array($json) && isset($json['text'])) {
        $text = trim($json['text']);
    }
}

if ($text === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing "text" parameter']);
    exit;
}

// --- Forward to Telegram API ---
$apiUrl = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
$postData = http_build_query([
    'chat_id' => $chatId,
    'text'    => $text,
    'parse_mode' => 'HTML',
]);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/x-www-form-urlencoded',
    ],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
echo $response;
