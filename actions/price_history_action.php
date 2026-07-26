<?php
session_start();

require_once '../config/db.php';
require_once '../backend/ai_service.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Login required'
    ]);
    exit;
}

$crop   = trim($_POST['crop'] ?? '');
$market = trim($_POST['market'] ?? '');

if (empty($crop)) {
    echo json_encode([
        'success' => false,
        'error' => 'Crop name required'
    ]);
    exit;
}

// Last 7 days dates
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('d/m/Y', strtotime("-$i days"));
}

$datesStr = implode(', ', $dates);

// AI Prompt
$prompt = "You are an Indian agricultural market expert.

Generate realistic price history for $crop at $market mandi.

Reply ONLY in this exact format, one entry per line:

DATE|PRICE|MIN|MAX|TREND

Generate exactly 7 entries for these dates:

$datesStr

Example:
01/01/2025|2125|2000|2250|up

TREND must be only:
up
down
stable

Prices must be realistic Indian mandi prices in INR per Quintal.";

// AI Response
$result = getAIResponse($prompt);

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'error' => $result['message']
    ]);
    exit;
}

$aiText = trim($result['message']);

$history = [];

foreach (explode("\n", $aiText) as $line) {

    $line = trim($line);

    if (empty($line)) continue;

    $parts = explode('|', $line);

    if (count($parts) == 5) {

        $history[] = [
            'date'  => trim($parts[0]),
            'price' => (int) trim($parts[1]),
            'min'   => (int) trim($parts[2]),
            'max'   => (int) trim($parts[3]),
            'trend' => strtolower(trim($parts[4]))
        ];
    }
}

echo json_encode([
    'success' => true,
    'history' => $history
]);
?>