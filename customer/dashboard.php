<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Stats Fetching
$prod_query = "SELECT COUNT(*) as total FROM farm_products WHERE quantity_available > 0";
$prod_res = $conn->query($prod_query);
$total_products = ($prod_res) ? $prod_res->fetch_assoc()['total'] : 0;

$order_query = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total'];

$page_title = "Customer Dashboard";
include '../includes/header.inc.php';
include '../includes/navbar_customer.inc.php';
?>

<div class="container mt-5">
    <div class="row align-items-center mb-5 animate-fade-in-up">
        <div class="col-md-7">
            <h1 class="display-4 text-primary mb-2" style="color: var(--primary-blue) !important;">Hello, <?= htmlspecialchars($_SESSION['fullname']) ?>! 🛒</h1>
            <p class="text-muted fs-5">Fresh organic vegetables delivered straight from the farm to your door. Support local farmers today.</p>
        </div>
        <div class="col-md-5">
            <form action="browse_products.php" method="GET" class="glass-card p-2 d-flex">
                <input type="text" name="search" class="form-control border-0 bg-transparent" placeholder="Search vegetables...">
                <button class="btn btn-premium btn-premium-blue ms-2" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5 animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="col-md-4">
            <div class="glass-card stat-card border-bottom border-4 border-warning">
                <div class="stat-value text-warning"><?= $total_products ?></div>
                <div class="stat-label">Available Items</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card stat-card border-bottom border-4 border-info">
                <div class="stat-value text-info"><?= $total_orders ?></div>
                <div class="stat-label">Your Orders</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card stat-card border-bottom border-4 border-success">
                <div class="stat-value text-success">100%</div>
                <div class="stat-label">Organic Guarantee</div>
            </div>
        </div>
    </div>

    <div class="row g-4 animate-fade-in-up" style="animation-delay: 0.2s;">
        <!-- Feature Cards -->
        <?php
        $features = [
            ['title' => 'Browse Products', 'icon' => 'fa-carrot', 'color' => 'warning', 'desc' => 'Discover fresh vegetables and organic produce directly from local farmers.', 'link' => 'browse_products.php', 'btn' => 'Shop Now'],
            ['title' => 'Delivery Plans', 'icon' => 'fa-calendar-alt', 'color' => 'success', 'desc' => 'Subscribe to weekly fresh baskets delivered to your doorstep.', 'link' => 'delivery_plans.php', 'btn' => 'View Plans'],
            ['title' => 'Track Orders', 'icon' => 'fa-truck', 'color' => 'info', 'desc' => 'Keep an eye on your recent purchases and delivery status.', 'link' => 'my_orders.php', 'btn' => 'My Orders'],
            ['title' => 'Farmer Profiles', 'icon' => 'fa-users', 'color' => 'secondary', 'desc' => 'Meet the farmers growing your food. View their practices and ratings.', 'link' => 'farmer_profiles.php', 'btn' => 'Meet Farmers'],
            ['title' => 'Purchase History', 'icon' => 'fa-history', 'color' => 'primary', 'desc' => 'Easily reorder your favorite products from past purchases.', 'link' => 'my_orders.php', 'btn' => 'View History'],
            ['title' => 'Offers & Deals', 'icon' => 'fa-tags', 'color' => 'danger', 'desc' => 'Check out bulk discounts and seasonal offers on fresh produce.', 'link' => 'offers.php', 'btn' => 'Claim Offers'],
        ];

        foreach ($features as $f): ?>
            <div class="col-lg-4 col-md-6">
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
