<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch previous scans
$history = $conn->query("SELECT * FROM ai_recommendations WHERE farmer_id = $user_id AND recommendation_type = 'Pest' ORDER BY created_at DESC LIMIT 5");

$page_title = "Pest & Disease Detection";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="row g-5">
        <div class="col-md-6 animate-fade-in-up">
            <h2 class="text-danger fw-bold mb-4">AI Vision Scanner 🔍🪲</h2>
            <p class="text-muted mb-4">Upload a clear photo of your affected crop leaf or stem. Our AI vision model will identify the pest or disease and suggest immediate treatments.</p>
            
            <div class="glass-card p-4 mb-3">
                <label class="form-label small fw-bold text-muted">Crop Type</label>
                <select id="cropType" class="form-select rounded-pill">
                    <option value="tomato">Tomato</option>
                    <option value="wheat">Wheat</option>
                    <option value="rice">Rice</option>
                    <option value="cotton">Cotton</option>
                    <option value="potato">Potato</option>
                    <option value="maize">Maize</option>
                    <option value="sugarcane">Sugarcane</option>
                    <option value="soybean">Soybean</option>
                    <option value="plant">Plant</option>
                    <option value="others">Others</option>
                </select>
            </div>

            <div class="glass-card p-5 text-center border-dashed border-2" id="dropZone" style="border-radius: 20px; cursor: pointer;">
                <input type="file" id="fileInput" class="d-none" accept="image/*">
                <div id="uploadPlaceholder">
                    <i class="fas fa-cloud-upload-alt fa-4x text-danger mb-3 opacity-50"></i>
                    <h5 class="fw-bold">Click to Upload or Drag & Drop</h5>
                    <p class="text-muted small">JPG, PNG up to 5MB</p>
                </div>
                <div id="previewContainer" class="d-none">
                    <img id="imagePreview" src="" class="img-fluid rounded-3 mb-3 shadow" style="max-height: 250px;">
                    <br>
                    <button type="button" id="scanButton" class="btn btn-premium btn-premium-green px-5">Analyze Image</button>
                    <button type="button" id="resetButton" class="btn btn-link text-muted d-block mx-auto mt-2">Remove</button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div id="scanOverlay" class="glass-card p-5 h-100 d-none flex-column align-items-center justify-content-center text-center animate-fade-in-up">
                <div class="spinner-border text-danger mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5 class="fw-bold">AI Analyzing...</h5>
                <p class="text-muted mt-2 small">Scanning for structural patterns and color anomalies.</p>
            </div>

            <div id="resultCard" class="glass-card p-4 h-100 d-none animate-fade-in-up">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h5 class="fw-bold text-danger">Diagnosis Result</h5>
                    <span class="badge bg-danger rounded-pill px-3">AI Analyzed</span>
                </div>
                <div class="mb-4">
                    <h3 id="diagnosisName" class="fw-bold text-dark">--</h3>
                    <p class="text-muted small" id="diagnosisDesc">--</p>
                </div>
                <div class="glass-card p-3 bg-light border-0 small mb-4">
                    <h6 class="fw-bold mb-2"><i class="fas fa-notes-medical me-2"></i>Proposed Treatment:</h6>
                    <p id="treatment" class="mb-0">--</p>
                </div>
                <div class="alert alert-warning border-0 small py-2 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span>Consult a local expert before applying heavy chemicals.</span>
                </div>
                <button class="btn btn-outline-danger w-100 rounded-pill mt-3" onclick="location.reload()">New Scan</button>
            </div>
            
            <div id="placeholderResult" class="glass-card p-5 h-100 d-flex flex-column align-items-center justify-content-center border-dashed border-2 text-muted">
                <i class="fas fa-bug fa-3x mb-3 opacity-25"></i>
                <p class="mb-0 text-center">Scan results will appear here after analysis.</p>
            </div>
        </div>
    </div>

    <div class="mt-5 animate-fade-in-up" style="animation-delay: 0.3s;">
        <h5 class="fw-bold mb-4">Previous Scan Reports</h5>
        <div class="row g-3">
            <?php if ($history && $history->num_rows > 0): ?>
                <?php while($row = $history->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="glass-card p-3 border-start border-4 border-danger">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($row['recommendation_text']) ?></h6>
                            <small class="text-muted d-block mb-2">Confidence: High</small>
                            <small class="text-muted"><?= date('d M, Y', strtotime($row['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center p-4">
                    <p class="text-muted italic">No previous scans found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const dropZone         = document.getElementById('dropZone');
const fileInput        = document.getElementById('fileInput');
const preview          = document.getElementById('imagePreview');
const previewContainer = document.getElementById('previewContainer');
const placeholder      = document.getElementById('uploadPlaceholder');

dropZone.onclick = () => fileInput.click();

fileInput.onchange = e => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = () => {
            preview.src = reader.result;
            placeholder.classList.add('d-none');
            previewContainer.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
};

document.getElementById('resetButton').onclick = (e) => {
    e.stopPropagation();
    fileInput.value = '';
    placeholder.classList.remove('d-none');
    previewContainer.classList.add('d-none');
};

document.getElementById('scanButton').onclick = async (e) => {
    e.stopPropagation();

    const file = fileInput.files[0];
    if (!file) return;

    // Loading show karo
    document.getElementById('previewContainer').classList.add('d-none');
    document.getElementById('placeholderResult').classList.add('d-none');
    document.getElementById('resultCard').classList.add('d-none');
    document.getElementById('scanOverlay').classList.remove('d-none');
    document.getElementById('scanOverlay').classList.add('d-flex');

    const formData = new FormData();
    formData.append('image', file);
    formData.append('crop_type', document.getElementById('cropType').value);

    try {
        const response = await fetch('../actions/pest_ai_action.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        document.getElementById('scanOverlay').classList.add('d-none');
        document.getElementById('scanOverlay').classList.remove('d-flex');

        if (data.success) {
            document.getElementById('placeholderResult').classList.add('d-none');
            document.getElementById('resultCard').classList.remove('d-none');
            document.getElementById('diagnosisName').innerText = data.name;
            document.getElementById('diagnosisDesc').innerText = data.desc;
            document.getElementById('treatment').innerText     = data.treatment;
        } else {
            document.getElementById('placeholderResult').classList.remove('d-none');
            alert('Error: ' + data.error);
        }
    } catch(err) {
        document.getElementById('scanOverlay').classList.add('d-none');
        document.getElementById('scanOverlay').classList.remove('d-flex');
        document.getElementById('placeholderResult').classList.remove('d-none');
        alert('Server se connect nahi ho pa raha!');
    }
};
</script>