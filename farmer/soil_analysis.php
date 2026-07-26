<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch previous analysis
$history = $conn->query("SELECT * FROM ai_recommendations WHERE farmer_id = $user_id AND recommendation_type = 'Soil' ORDER BY created_at DESC LIMIT 5");

$page_title = "Soil Health Analysis";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="row g-5">
        <div class="col-md-6 animate-fade-in-up">
            <h2 class="text-warning fw-bold mb-4">Soil Intel 🧪🔬</h2>
            <p class="text-muted mb-4">Enter your soil test parameters (N-P-K levels and pH) to receive a detailed health report and customized fertilizer recommendations.</p>
            
            <div class="glass-card p-4">
                <form id="soilForm">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Nitrogen (N)</label>
                            <input type="number" id="n_level" class="form-control rounded-pill" placeholder="e.g. 140" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Phosphorus (P)</label>
                            <input type="number" id="p_level" class="form-control rounded-pill" placeholder="e.g. 50" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Potassium (K)</label>
                            <input type="number" id="k_level" class="form-control rounded-pill" placeholder="e.g. 200" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Soil pH</label>
                            <input type="number" step="0.1" id="ph_level" class="form-control rounded-pill" placeholder="e.g. 6.5" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" id="analyzeSoil" class="btn btn-premium btn-premium-green w-100">Run Health Check</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div id="resultCard" class="glass-card p-4 h-100 d-none animate-fade-in-up">
                <h5 class="fw-bold mb-4 text-warning border-bottom pb-2">Soil Health Report</h5>
                
                <div class="mb-4">
                    <label class="small fw-bold text-muted">Status</label>
                    <h4 id="soilStatus" class="fw-bold">--</h4>
                </div>

                <div class="row text-center mb-4">
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">N</small>
                            <span id="n_res" class="fw-bold">--</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">P</small>
                            <span id="p_res" class="fw-bold">--</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <small class="d-block text-muted">K</small>
                            <span id="k_res" class="fw-bold">--</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-3 bg-light border-0 small mb-4">
                    <h6 class="fw-bold mb-2 text-primary">Recommendations:</h6>
                    <p id="advice" class="mb-0">--</p>
                </div>

                <div class="alert alert-info border-0 small py-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <span>Optimum pH for most crops is 6.0 - 7.5.</span>
                </div>
            </div>
            
            <div id="placeholderResult" class="glass-card p-5 h-100 d-flex flex-column align-items-center justify-content-center border-dashed border-2 text-muted">
                <i class="fas fa-microscope fa-3x mb-3 opacity-25"></i>
                <p class="mb-0 text-center">Analyze your soil to get localized fertilizer and amendment tips.</p>
            </div>
        </div>
    </div>

    <div class="mt-5 animate-fade-in-up" style="animation-delay: 0.3s;">
        <h5 class="fw-bold mb-4">Soil Health History</h5>
        <div class="row g-3">
            <?php if ($history && $history->num_rows > 0): ?>
                <?php while($row = $history->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="glass-card p-3 border-start border-4 border-warning">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($row['recommendation_text']) ?></h6>
                            <small class="text-muted d-block mb-2">Data: <?= htmlspecialchars($row['input_data']) ?></small>
                            <small class="text-muted"><?= date('d M, Y', strtotime($row['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center p-4">
                    <p class="text-muted italic">No previous records found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('analyzeSoil').addEventListener('click', async function() {
    const n  = document.getElementById('n_level').value;
    const p  = document.getElementById('p_level').value;
    const k  = document.getElementById('k_level').value;
    const ph = document.getElementById('ph_level').value;

    if (!n || !p || !k || !ph) { alert("Please fill all fields"); return; }

    // Loading state
    const btn = document.getElementById('analyzeSoil');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>AI Analyzing...';

    document.getElementById('placeholderResult').classList.add('d-none');
    document.getElementById('resultCard').classList.remove('d-none');
    document.getElementById('soilStatus').innerText = 'Analyzing...';
    document.getElementById('advice').innerText = 'Please wait...';
    document.getElementById('n_res').innerText = n;
    document.getElementById('p_res').innerText = p;
    document.getElementById('k_res').innerText = k;

    try {
        const response = await fetch('../actions/soil_ai_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `n=${n}&p=${p}&k=${k}&ph=${ph}`
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('soilStatus').innerText = data.status;
            document.getElementById('advice').innerText     = data.advice + '\n\n💊 Fertilizer: ' + data.fertilizer;
            document.getElementById('n_res').innerText      = data.n;
            document.getElementById('p_res').innerText      = data.p;
            document.getElementById('k_res').innerText      = data.k;
        } else {
            document.getElementById('soilStatus').innerText = 'Error!';
            document.getElementById('advice').innerText     = data.error;
        }
    } catch(e) {
        document.getElementById('soilStatus').innerText = 'Server Error!';
        document.getElementById('advice').innerText     = 'Ollama chal raha hai? Check karo.';
    }

    btn.disabled = false;
    btn.innerHTML = 'Run Health Check';
});
</script>

<?php include '../includes/footer.inc.php'; ?>
