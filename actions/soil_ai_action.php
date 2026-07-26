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
$n  = floatval($_POST['n'] ?? 0);
$p  = floatval($_POST['p'] ?? 0);
$k  = floatval($_POST['k'] ?? 0);
$ph = floatval($_POST['ph'] ?? 0);

if (!$n || !$p || !$k || !$ph) {
    echo json_encode(['success' => false, 'error' => 'Missing values']);
    exit;
}
$prompt = "You are an expert soil scientist for Indian farming. Analyze this soil test:

- Nitrogen (N): $n kg/ha
- Phosphorus (P): $p kg/ha
- Potassium (K): $k kg/ha
- Soil pH: $ph

Reply in EXACTLY this format:

STATUS: [one word/phrase like Healthy, Nitrogen Deficient, Acidic, etc.]

ADVICE: [2-3 practical sentences on what farmer should do]

FERTILIZER: [specific fertilizer name and quantity recommendation]

Be practical and specific to Indian farming conditions."; 

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
$status     = 'Analyzing...';
$advice     = $aiText; // fallback
$fertilizer = 'N/A';

foreach (explode("\n", $aiText) as $line) {
    if (preg_match('/^STATUS\s*:\s*(.+)/i', $line, $m))     $status     = trim($m[1]);
    if (preg_match('/^ADVICE\s*:\s*(.+)/i', $line, $m))     $advice     = trim($m[1]);
    if (preg_match('/^FERTILIZER\s*:\s*(.+)/i', $line, $m)) $fertilizer = trim($m[1]);
}

// Database save
$inputData = "N:$n, P:$p, K:$k, pH:$ph";
$stmt = $conn->prepare("INSERT INTO ai_recommendations (farmer_id, recommendation_type, input_data, recommendation_text) VALUES (?, 'Soil', ?, ?)");
$stmt->bind_param("iss", $user_id, $inputData, $status);
$stmt->execute();

echo json_encode([
    'success'     => true,
    'status'      => $status,
    'advice'      => $advice,
    'fertilizer'  => $fertilizer,
    'n'           => $n,
    'p'           => $p,
    'k'           => $k
]);
?>