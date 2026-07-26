<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
$farmer_id = $_SESSION['user_id'];

$active_products = 0;
try {
    $res = $conn->query("SELECT COUNT(*) AS count FROM farm_products WHERE farmer_id = $farmer_id");
    if ($res) $active_products = $res->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {}

$total_orders = 0;
try {
    $res = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE farmer_id = $farmer_id");
    if ($res) $total_orders = $res->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {}

$monthly_revenue = 0;
try {
    $res = $conn->query("SELECT SUM(total_price) AS sum FROM orders WHERE farmer_id = $farmer_id AND MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())");
    if ($res) $monthly_revenue = $res->fetch_assoc()['sum'] ?? 0;
} catch (Exception $e) {}

$products_sold = 0;
try {
    $res = $conn->query("SELECT SUM(quantity) AS sum FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.farmer_id = $farmer_id AND o.status = 'Delivered'");
    if ($res) $products_sold = $res->fetch_assoc()['sum'] ?? 0;
} catch (Exception $e) {
    // Fallback if order_items logic fails
    $products_sold = 0;
}

$page_title = "Farmer Dashboard";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5">
    <div class="row align-items-center mb-5 animate-fade-in-up">
        <div class="col-md-8">
            <h1 class="display-4 text-success mb-2">Welcome back, <?= htmlspecialchars($_SESSION['fullname']) ?>! 👋</h1>
            <p class="text-muted fs-5">Your farm's digital command center is ready. What would you like to do today?</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="dropdown">
                <button class="btn btn-premium btn-premium-green shadow dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 animate-fade-in-up">
                    <li><a class="dropdown-item" href="add_product.php"><i class="fas fa-plus-circle me-2 text-success"></i> Add New Product</a></li>
                    <li><a class="dropdown-item" href="view_orders.php"><i class="fas fa-tasks me-2 text-primary"></i> Manage Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="weather.php"><i class="fas fa-cloud-sun me-2 text-info"></i> Check Weather</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5 animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="col-md-3">
            <div class="glass-card stat-card border-start border-4 border-success">
                <div class="stat-value text-success"><?= $active_products ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card border-start border-4 border-primary">
                <div class="stat-value text-primary"><?= $total_orders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card border-start border-4 border-warning">
                <div class="stat-value text-warning">₹<?= number_format($monthly_revenue, 2) ?></div>
                <div class="stat-label">This Month Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card border-start border-4 border-info">
                <div class="stat-value text-info"><?= rtrim(rtrim(number_format($products_sold, 2), '0'), '.') ?></div>
                <div class="stat-label">Units Sold</div>
            </div>
        </div>
    </div>

    <div class="row g-4 animate-fade-in-up" style="animation-delay: 0.2s;">
        <!-- Feature Cards -->
        <?php
        $features = [
            ['title' => 'AI Crop Recommendation', 'icon' => 'fa-seedling', 'color' => 'success', 'desc' => 'Get smart suggestions on what to plant based on soil and weather.', 'link' => 'crop_recommendation.php', 'btn' => 'Explore Models'],
            ['title' => 'Weather Forecast', 'icon' => 'fa-cloud-sun-rain', 'color' => 'primary', 'desc' => 'Real-time localized weather updates and severe alerts.', 'link' => 'weather.php', 'btn' => 'Check Weather'],
            ['title' => 'Soil Health Analysis', 'icon' => 'fa-vial', 'color' => 'warning', 'desc' => 'Analyze soil nutrients and receive fertilizer tips.', 'link' => 'soil_analysis.php', 'btn' => 'Analyze Soil'],
            ['title' => 'Pest & Disease Alerts', 'icon' => 'fa-bug', 'color' => 'danger', 'desc' => 'Identify diseases using AI scanner and get treatment tips.', 'link' => 'pest_detection.php', 'btn' => 'Scan Crop'],
            ['title' => 'Market Price Updates', 'icon' => 'fa-chart-line', 'color' => 'info', 'desc' => 'Live market prices from local mandis for optimal value.', 'link' => 'market_prices.php', 'btn' => 'View Rates'],
            ['title' => 'Machinery Rental', 'icon' => 'fa-tractor', 'color' => 'secondary', 'desc' => 'Find and rent modern farming equipment nearby.', 'link' => 'machinery.php', 'btn' => 'Find Equipment'],
            ['title' => 'Expert Support', 'icon' => 'fa-comments', 'color' => 'dark', 'desc' => 'Connect with agronomists for personalized advice.', 'link' => 'support.php', 'btn' => 'Ask Expert'],
            ['title' => 'Sell Harvest', 'icon' => 'fa-box-open', 'color' => 'success', 'desc' => 'List your products directly to customers.', 'link' => 'manage_products.php', 'btn' => 'Manage Inventory'],
        ];

        foreach ($features as $f): ?>
            <div class="col-lg-3 col-md-6">
                <div class="glass-card h-100 p-4 d-flex flex-column text-center">
                    <div class="mb-3">
                        <i class="fas <?= $f['icon'] ?> fa-3x text-<?= $f['color'] ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= $f['title'] ?></h5>
                    <p class="text-muted small flex-grow-1"><?= $f['desc'] ?></p>
                    <a href="<?= $f['link'] ?>" class="btn btn-outline-<?= $f['color'] ?> btn-sm rounded-pill mt-3 px-4"><?= $f['btn'] ?></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.inc.php'; ?>
