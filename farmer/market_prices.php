<?php
session_start();
require_once '../config/db.php';
require_once '../backend/ai_service.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$search = $_GET['search'] ?? '';

$cropQuery = $search
    ? "for $search crop"
    : "for common Indian crops like Wheat, Rice, Potato, Onion, Cotton, Mustard, Tomato, Soybean";

$prompt = "You are an Indian agricultural market expert. Generate current realistic mandi prices $cropQuery.

Reply ONLY in this exact format, one crop per line, no extra text:
CROP|MANDI|PRICE|MIN|MAX|TREND

Wheat|Agroha Mandi Haryana|2125|2000|2250|up

(give 6 realistic entries)

TREND must be: up, down, or stable.
PRICE, MIN, MAX must be realistic Indian mandi prices in INR per Quintal.";

$result = getAIResponse($prompt);

$market_data = [];

if ($result['success']) {
    $aiText = trim($result['message']);
} else {
    $aiText = '';
}

if (!empty($aiText)) {
    $lines = explode("\n", $aiText);
    foreach ($lines as $line) {
        $line  = trim($line);
        $parts = explode('|', $line);
        if (count($parts) === 6 && is_numeric(trim($parts[2]))) {
            $market_data[] = [
                'crop'   => trim($parts[0]),
                'market' => trim($parts[1]),
                'price'  => intval($parts[2]),
                'min'    => intval($parts[3]),
                'max'    => intval($parts[4]),
                'unit'   => 'Quintal',
                'trend'  => strtolower(trim($parts[5])),
                'date'   => date('d/m/Y')
            ];
        }
    }
}

if (empty($market_data)) {
    $market_data = [
        ['crop' => 'Wheat',          'market' => 'Agroha Mandi, Haryana',        'price' => 2125, 'min' => 2000, 'max' => 2250, 'unit' => 'Quintal', 'trend' => 'up',     'date' => date('d/m/Y')],
        ['crop' => 'Mustard',        'market' => 'Jaipur Mandi, Rajasthan',      'price' => 5450, 'min' => 5200, 'max' => 5600, 'unit' => 'Quintal', 'trend' => 'down',   'date' => date('d/m/Y')],
        ['crop' => 'Rice (Basmati)', 'market' => 'Karnal Mandi, Haryana',        'price' => 3800, 'min' => 3600, 'max' => 4000, 'unit' => 'Quintal', 'trend' => 'stable', 'date' => date('d/m/Y')],
        ['crop' => 'Potato',         'market' => 'Azadpur Mandi, Delhi',         'price' => 1200, 'min' => 1100, 'max' => 1350, 'unit' => 'Quintal', 'trend' => 'up',     'date' => date('d/m/Y')],
        ['crop' => 'Onion',          'market' => 'Lasalgaon Mandi, Maharashtra', 'price' => 1850, 'min' => 1700, 'max' => 2000, 'unit' => 'Quintal', 'trend' => 'up',     'date' => date('d/m/Y')],
        ['crop' => 'Cotton',         'market' => 'Rajkot Mandi, Gujarat',        'price' => 7200, 'min' => 7000, 'max' => 7400, 'unit' => 'Quintal', 'trend' => 'down',   'date' => date('d/m/Y')],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Price Updates - Agro-Haat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .navbar { background-color: #2e7d32; }
        .navbar-brand, .nav-link { color: #ffffff !important; font-weight: 500; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .trend-up { color: #2e7d32; }
        .trend-down { color: #d32f2f; }
        .trend-stable { color: #f9a825; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back to Home</a>
        </div>
    </nav>

    <div class="container mt-5 mb-5 pb-5">
        <div class="row mb-4 align-items-center">
            <div class="col-md-7">
                <h2 class="text-success fw-bold mb-1">Live Mandi Prices 📈</h2>
                <p class="text-muted">Stay updated with the latest crop rates from major markets across India.</p>
            </div>
            <div class="col-md-5">
                <form action="market_prices.php" method="GET" class="input-group shadow-sm rounded-pill overflow-hidden">
                    <input type="text" name="search" class="form-control border-0 px-4" placeholder="Search crop or mandi..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-success px-4" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <div class="card card-custom overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Crop Name</th>
                            <th class="py-3">Market (Mandi)</th>
                            <th class="py-3">Modal Price (₹)</th>
                            <th class="py-3">Min - Max (₹)</th>
                            <th class="py-3">Trend</th>
                            <th class="pe-4 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($market_data)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">No data found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($market_data as $data): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($data['crop']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($data['market']) ?></td>
                                <td class="fw-bold">₹<?= number_format($data['price']) ?></td>
                                <td class="text-muted small">₹<?= number_format($data['min']) ?> - ₹<?= number_format($data['max']) ?></td>
                                <td>
                                    <?php if ($data['trend'] === 'up'): ?>
                                        <span class="trend-up small fw-bold"><i class="fas fa-arrow-trend-up me-1"></i> Rising</span>
                                    <?php elseif ($data['trend'] === 'down'): ?>
                                        <span class="trend-down small fw-bold"><i class="fas fa-arrow-trend-down me-1"></i> Falling</span>
                                    <?php else: ?>
                                        <span class="trend-stable small fw-bold"><i class="fas fa-minus me-1"></i> Stable</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-sm btn-outline-success rounded-pill px-3"
                                        onclick="showPriceHistory('<?= htmlspecialchars($data['crop']) ?>', '<?= htmlspecialchars($data['market']) ?>')">
                                        Price History
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 p-3 bg-white rounded-3 border shadow-sm small text-muted">
            <i class="fas fa-info-circle me-2"></i> AI-generated realistic mandi prices based on current market trends.
        </div>
    </div>

    <!-- Price History Modal -->
    <div class="modal fade" id="priceHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-chart-line me-2"></i>
                        Price History — <span id="modalCropName"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalLoading" class="text-center py-4">
                        <div class="spinner-border text-success mb-3"></div>
                        <p class="text-muted">AI generating price history...</p>
                    </div>
                    <div id="modalContent" class="d-none">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Modal Price (₹)</th>
                                    <th>Min (₹)</th>
                                    <th>Max (₹)</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    async function showPriceHistory(crop, market) {
        document.getElementById('modalCropName').innerText = crop;
        document.getElementById('modalLoading').classList.remove('d-none');
        document.getElementById('modalContent').classList.add('d-none');
        document.getElementById('historyTableBody').innerHTML = '';

        const modal = new bootstrap.Modal(document.getElementById('priceHistoryModal'));
        modal.show();

        try {
            const response = await fetch('../actions/price_history_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `crop=${encodeURIComponent(crop)}&market=${encodeURIComponent(market)}`
            });

            const data = await response.json();

            document.getElementById('modalLoading').classList.add('d-none');
            document.getElementById('modalContent').classList.remove('d-none');

            if (data.success && data.history.length > 0) {
                let html = '';
                data.history.forEach(row => {
                    const trendHtml = row.trend === 'up'
                        ? '<span class="text-success fw-bold"><i class="fas fa-arrow-up"></i> Rising</span>'
                        : row.trend === 'down'
                        ? '<span class="text-danger fw-bold"><i class="fas fa-arrow-down"></i> Falling</span>'
                        : '<span class="text-warning fw-bold"><i class="fas fa-minus"></i> Stable</span>';

                    html += `<tr>
                        <td>${row.date}</td>
                        <td class="fw-bold">₹${parseInt(row.price).toLocaleString('en-IN')}</td>
                        <td class="text-muted">₹${parseInt(row.min).toLocaleString('en-IN')}</td>
                        <td class="text-muted">₹${parseInt(row.max).toLocaleString('en-IN')}</td>
                        <td>${trendHtml}</td>
                    </tr>`;
                });
                document.getElementById('historyTableBody').innerHTML = html;
            } else {
                document.getElementById('historyTableBody').innerHTML =
                    '<tr><td colspan="5" class="text-center text-muted">No history available</td></tr>';
            }
        } catch(err) {
            document.getElementById('modalLoading').classList.add('d-none');
            document.getElementById('modalContent').classList.remove('d-none');
            document.getElementById('historyTableBody').innerHTML =
                '<tr><td colspan="5" class="text-center text-danger">Error loading history</td></tr>';
        }
    }
    </script>
</body>
</html>