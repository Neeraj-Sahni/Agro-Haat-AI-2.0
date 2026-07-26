<?php
session_start();
require_once '../config/db.php';
require_once '../backend/ai_service.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$soil    = trim($_POST['soil'] ?? '');
$season  = trim($_POST['season'] ?? '');
$location = trim($_POST['location'] ?? 'India');

if (!$soil || !$season) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit;
}

$prompt = "You are an expert Indian agricultural advisor. A farmer has the following conditions:
- Soil Type: $soil
- Season: $season
- Location: $location

Give crop recommendation in this EXACT format (nothing else):
CROP: [crop name]
REASONING: [one line reason]
YIELD: [estimated yield per acre]
WATER: [Low/Moderate/High]

Be specific to Indian farming conditions.";

$result = getAIResponse($prompt);

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'error' => $result['message']
    ]);
    exit;
}

$aiText = $result['message'];

// Debug - dekho AI kya return kar raha hai
error_log("AI Response: " . $aiText);

$crop      = 'N/A';
$reasoning = 'N/A';
$yield     = 'N/A';
$water     = 'N/A';

// Case-insensitive flexible parsing
$lines = explode("\n", $aiText);
foreach ($lines as $line) {
    $line = trim($line);
    $lineLower = strtolower($line);
    
    if (preg_match('/^crop\s*:\s*(.+)/i', $line, $m))      $crop      = trim($m[1]);
    if (preg_match('/^reasoning\s*:\s*(.+)/i', $line, $m)) $reasoning = trim($m[1]);
    if (preg_match('/^yield\s*:\s*(.+)/i', $line, $m))     $yield     = trim($m[1]);
    if (preg_match('/^water\s*:\s*(.+)/i', $line, $m))     $water     = trim($m[1]);
}

// Agar phir bhi N/A aaye to full response dikhao
if ($crop === 'N/A') {
    $crop      = 'See full response';
    $reasoning = $aiText; // Pura AI response dikhao
    $yield     = 'N/A';
    $water     = 'N/A';
}
// Database mein save karo
$inputData = "Soil: $soil, Season: $season, Location: $location";

if (!isset($conn)) {
    if (isset($mysqli)) $conn = $mysqli;
    elseif (isset($db)) $conn = $db;
}

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

// Prepare and execute insert
$stmt = $conn->prepare("INSERT INTO ai_recommendations (farmer_id, recommendation_type, input_data, recommendation_text) VALUES (?, 'Crop', ?, ?)");
if ($stmt) {
    $stmt->bind_param("iss", $user_id, $inputData, $crop);
    $stmt->execute();
    $stmt->close();
} else {
    error_log('DB prepare failed: '.($conn->error ?? 'unknown'));
}

echo json_encode([
    'success'   => true,
    'crop'      => $crop,
    'reasoning' => $reasoning,
    'yield'     => $yield,
    'water'     => $water
]);
?>