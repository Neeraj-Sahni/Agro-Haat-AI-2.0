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

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No image uploaded']);
    exit;
}

// Image save karo
$uploadDir = '../uploads/pest_scans/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$fileName = time() . '_' . basename($_FILES['image']['name']);
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
    echo json_encode(['success' => false, 'error' => 'Image upload failed']);
    exit;
}

// Crop type user se lenge (POST se)
$cropType = isset($_POST['crop_type']) ? trim($_POST['crop_type']) : 'unknown crop';


$prompt = "You are an expert agricultural plant pathologist for Indian farming.
A farmer has uploaded a photo of their $cropType crop showing some symptoms.

Based on common Indian crop diseases and pests, analyze and reply in EXACTLY this format:
NAME: [most likely pest or disease name]
DESC: [one line description of the disease/pest]
TREATMENT: [specific practical treatment recommendation for Indian farmers]

Be specific and practical.";

$result = getAIResponse($prompt);

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'error' => $result['message']
    ]);
    exit;
}

$aiText = $result['message'];

// Parse response
$name      = 'Unknown';
$desc      = $aiText;
$treatment = 'Consult local agricultural expert.';

foreach (explode("\n", $aiText) as $line) {
    if (preg_match('/^NAME\s*:\s*(.+)/i', $line, $m))      $name      = trim($m[1]);
    if (preg_match('/^DESC\s*:\s*(.+)/i', $line, $m))      $desc      = trim($m[1]);
    if (preg_match('/^TREATMENT\s*:\s*(.+)/i', $line, $m)) $treatment = trim($m[1]);
}

// Database save
global $conn;
$stmt = $conn->prepare("INSERT INTO ai_recommendations (farmer_id, recommendation_type, input_data, recommendation_text) VALUES (?, 'Pest', ?, ?)");
$stmt->bind_param("iss", $user_id, $cropType, $name);
$stmt->execute();

echo json_encode([
    'success'   => true,
    'name'      => $name,
    'desc'      => $desc,
    'treatment' => $treatment
]);
?>