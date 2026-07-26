<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch previous recommendations
// Ensure DB connection variable exists (some setups expose $conn in globals)
$db = isset($conn) ? $conn : (isset($GLOBALS['conn']) ? $GLOBALS['conn'] : null);
// Sanitize user id
$user_id = (int) $user_id;
if ($db) {
    $history = $db->query("SELECT * FROM ai_recommendations WHERE farmer_id = $user_id AND recommendation_type = 'Crop' ORDER BY created_at DESC LIMIT 5");
} else {
    // Fallback: no DB connection available
    $history = false;
}

$page_title = "AI Crop Recommendation";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="row g-5">
        <div class="col-md-6 animate-fade-in-up">
            <h2 class="text-success fw-bold mb-4">AI Crop Insight 🧠🌱</h2>
            <p class="text-muted mb-4">Make data-driven decisions. Our AI analyzes your soil and weather patterns to recommend the most profitable and sustainable crops.</p>
            
            <div class="glass-card p-4">
                <form id="recommendationForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Soil Type</label>
                        <select id="soilType" class="form-select rounded-pill" required>
                            <option value="">Select Soil Type</option>
                            <option value="Alluvial">Alluvial (Most Fertile)</option>
                            <option value="Black">Black (Cotton Soil)</option>
                            <option value="Red">Red Soil</option>
                            <option value="Laterite">Laterite Soil</option>
                            <option value="Sandy">Sandy Soil</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Current Season</label>
                        <select id="season" class="form-select rounded-pill" required>
                            <option value="Kharif">Kharif (Monsoon)</option>
                            <option value="Rabi">Rabi (Winter)</option>
                            <option value="Zaid">Zaid (Summer)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Farm Location (Auto-detected)</label>
                        <input type="text" class="form-control rounded-pill" value="Nagpur, Maharashtra" readonly>
                    </div>
                    <button type="button" id="getRecommendation" class="btn btn-premium btn-premium-green w-100">Generate Recommendation</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div id="resultCard" class="glass-card p-4 h-100 d-none animate-fade-in-up">
                <h5 class="fw-bold mb-4 text-success border-bottom pb-2">Top Recommendation</h5>
                <div class="text-center mb-4">
                    <i id="resultIcon" class="fas fa-seedling fa-4x text-success mb-3"></i>
                    <h3 id="recommendedCrop" class="fw-bold">--</h3>
                </div>
                <div class="alert alert-light border shadow-sm small">
                    <p class="mb-1"><strong>Reasoning:</strong> <span id="reasoning">--</span></p>
                    <p class="mb-1"><strong>Est. Yield:</strong> <span id="yield">--</span></p>
                    <p class="mb-0"><strong>Water Need:</strong> <span id="water">--</span></p>
                </div>
                <div class="mt-4">
                    <button class="btn btn-outline-success btn-sm w-100 rounded-pill" onclick="window.print()">Download Report</button>
                </div>
            </div>
            
            <div id="placeholderResult" class="glass-card p-5 h-100 d-flex flex-column align-items-center justify-content-center border-dashed border-2 text-muted">
                <i class="fas fa-microchip fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">Input details and click generate to see AI insights.</p>
            </div>
        </div>
    </div>

    <div class="mt-5 animate-fade-in-up" style="animation-delay: 0.3s;">
        <h5 class="fw-bold mb-4">Your Recommendation History</h5>
        <div class="row g-3">
            <?php if ($history && $history->num_rows > 0): ?>
                <?php while($row = $history->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="glass-card p-3 border-start border-4 border-success">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($row['recommendation_text']) ?></h6>
                            <small class="text-muted d-block mb-2">Input: <?= htmlspecialchars($row['input_data']) ?></small>
                            <small class="text-muted"><?= date('d M, Y', strtotime($row['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted italic">No previous recommendations found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('getRecommendation').addEventListener('click', async function() {
    const soil     = document.getElementById('soilType').value;
    const season   = document.getElementById('season').value;
    const location = document.querySelector('input[readonly]').value;

    if (!soil) { alert("Please select soil type"); return; }

    // Button loading state
    const btn = document.getElementById('getRecommendation');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>AI Analyzing...';

    // Placeholder hide karo
    document.getElementById('placeholderResult').classList.add('d-none');
    document.getElementById('resultCard').classList.remove('d-none');
    document.getElementById('recommendedCrop').innerText = 'Analyzing...';
    document.getElementById('reasoning').innerText = '...';
    document.getElementById('yield').innerText = '...';
    document.getElementById('water').innerText = '...';

    try {
        const response = await fetch('../actions/log_ai_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `soil=${encodeURIComponent(soil)}&season=${encodeURIComponent(season)}&location=${encodeURIComponent(location)}`
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('recommendedCrop').innerText = data.crop;
            document.getElementById('reasoning').innerText       = data.reasoning;
            document.getElementById('yield').innerText           = data.yield;
            document.getElementById('water').innerText           = data.water;
        } else {
            document.getElementById('recommendedCrop').innerText = 'Error!';
            document.getElementById('reasoning').innerText       = data.error;
        }
    } catch(e) {
        document.getElementById('recommendedCrop').innerText = 'Server Error!';
        document.getElementById('reasoning').innerText       = 'Ollama chal raha hai? Check karo.';
    }

    btn.disabled = false;
    btn.innerHTML = 'Generate Recommendation';
});
</script>

<?php include '../includes/footer.inc.php'; ?>
