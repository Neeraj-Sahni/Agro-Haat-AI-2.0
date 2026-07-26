<?php
require_once 'ai_service.php';

if (!isset($_POST['message'])) {
    exit('Error');
}

$message = trim($_POST['message']);

$prompt = "You are Agro-AI, a helpful farming assistant. Help farmers and customers with crop advice, prices, pest detection, weather tips, and trading. Answer concisely and helpfully.

User: $message

Agro-AI:";

$result = getAIResponse($prompt);

if ($result['success']) {
    echo $result['message'];
} else {
    echo $result['message'];
}
?>