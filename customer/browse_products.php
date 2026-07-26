<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'All';

// Fetch Categories
$cat_query = "SELECT DISTINCT category FROM farm_products WHERE quantity_available > 0";
$cat_res = $conn->query($cat_query);

// Build Product Query
$query = "SELECT p.*, u.fullname as farmer_name 
          FROM farm_products p 
          JOIN users u ON p.farmer_id = u.id 
          WHERE p.quantity_available > 0";

if ($category !== 'All') {
    $query .= " AND p.category = '" . mysqli_real_escape_string($conn, $category) . "'";
}

if (!empty($search)) {
    $query .= " AND p.product_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

$query .= " ORDER BY p.created_at DESC";
$products_res = $conn->query($query);

$page_title = "Browse Products";
include '../includes/header.inc.php';
include '../includes/navbar_customer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="row align-items-center mb-5 animate-fade-in-up">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0" style="color: var(--primary-blue) !important;">Fresh Marketplace 🥦</h2>
            <p class="text-muted">Buying directly from farmers ensures the best quality and fair prices.</p>
        </div>
        <div class="col-md-6">
            <form action="" method="GET" class="glass-card p-1 d-flex">
                <input type="hidden" name="category" value="<?= $category ?>">
                <input type="text" name="search" class="form-control border-0 bg-transparent" placeholder="Search by product name..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-premium btn-premium-blue ms-1"><i class="fas fa-search me-2"></i>Find</button>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-5 animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Categories Sidebar / Top Bar -->
        <div class="col-12">
            <div class="glass-card p-1 d-flex overflow-auto">
                <a href="?category=All&search=<?= urlencode($search) ?>" class="btn btn-sm rounded-pill px-4 py-2 me-2 text-decoration-none <?= $category == 'All' ? 'btn-premium btn-premium-blue shadow' : 'text-muted' ?>">All Harvest</a>
                <?php while ($cat = $cat_res->fetch_assoc()): 
                    $active = ($category == $cat['category']) ? 'btn-premium btn-premium-blue shadow' : 'text-muted';
                ?>
                    <a href="?category=<?= urlencode($cat['category']) ?>&search=<?= urlencode($search) ?>" class="btn btn-sm rounded-pill px-4 py-2 me-2 text-decoration-none <?= $active ?>"><?= htmlspecialchars($cat['category']) ?></a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 animate-fade-in-up" style="animation-delay: 0.2s;">
        <?php if ($products_res && $products_res->num_rows > 0): ?>
            <?php while($row = $products_res->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="glass-card h-100 overflow-hidden d-flex flex-column">
                        <div class="position-relative">
                            <img src="../public/uploads/farm_products/<?= htmlspecialchars($row['product_image'] ?? 'default_product.jpg') ?>" class="w-100" style="height: 200px; object-fit: cover;" onerror="this.src='https://placehold.co/400x300?text=Fresh+Produce'">
                            <span class="badge bg-success position-absolute top-0 end-0 m-3 shadow">Fresh</span>
                        </div>
                        <div class="p-3 flex-grow-1">
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($row['product_name']) ?></h5>
                            <small class="text-muted d-block mb-2">Farmer: <span class="fw-bold"><?= htmlspecialchars($row['farmer_name']) ?></span></small>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-primary fs-5">₹<?= number_format($row['price'], 2) ?></span>
                                <small class="text-muted">Per <?= htmlspecialchars($row['unit'] ?? 'kg') ?></small>
                            </div>
                            <p class="text-muted small mb-0 line-clamp-2"><?= htmlspecialchars($row['description']) ?></p>
                        </div>
                        <div class="p-3 pt-0">
                            <form action="../actions/cart_action.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                <div class="input-group input-group-sm mb-2 rounded-pill overflow-hidden border">
                                    <button type="button" class="btn btn-light" onclick="this.nextElementSibling.stepDown()">-</button>
                                    <input type="number" name="quantity" class="form-control text-center border-0" value="1" min="1" max="<?= $row['quantity_available'] ?>">
                                    <button type="button" class="btn btn-light" onclick="this.previousElementSibling.stepUp()">+</button>
                                </div>
                                <button type="submit" class="btn btn-premium btn-premium-blue w-100 rounded-pill"><i class="fas fa-cart-plus me-2"></i>Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search-minus text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-muted">Oops! No products found in this category.</h4>
                <a href="browse_products.php" class="btn btn-link link-primary">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>

<?php include '../includes/footer.inc.php'; ?>
