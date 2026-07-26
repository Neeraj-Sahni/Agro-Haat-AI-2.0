<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Fetch farmer's products
$products_res = $conn->query("SELECT * FROM farm_products WHERE farmer_id = $farmer_id ORDER BY created_at DESC");

$page_title = "Manage Inventory";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-5 animate-fade-in-up">
        <h2 class="text-success fw-bold mb-0">My Inventory 🚜</h2>
        <button class="btn btn-premium btn-premium-green rounded-pill px-4 shadow" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-2"></i>Add New Product
        </button>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <?= $_SESSION['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
    <?php endif; ?>

    <div class="row g-4 animate-fade-in-up" style="animation-delay: 0.1s;">
        <?php if ($products_res && $products_res->num_rows > 0): ?>
            <?php while($row = $products_res->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="glass-card h-100 overflow-hidden d-flex flex-column">
                        <div class="position-relative">
                            <img src="../public/uploads/farm_products/<?= htmlspecialchars($row['product_image'] ?? 'default_product.jpg') ?>" class="w-100" style="height: 180px; object-fit: cover;" onerror="this.src='https://placehold.co/400x300?text=Product'">
                            <span class="badge bg-<?= ($row['quantity_available'] > 10) ? 'success' : 'warning' ?> position-absolute top-0 end-0 m-3 shadow">
                                <?= $row['quantity_available'] ?> <?= htmlspecialchars($row['unit'] ?? 'kg') ?> left
                            </span>
                        </div>
                        <div class="p-3 flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($row['product_name']) ?></h6>
                            <h5 class="fw-bold text-success mb-2">₹<?= number_format($row['price'], 2) ?></h5>
                            <p class="text-muted small line-clamp-2 mb-0"><?= htmlspecialchars($row['description']) ?></p>
                        </div>
                        <div class="p-3 pt-0 d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $row['id'] ?>">Edit</button>
                            <a href="../actions/product_action.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger w-100 rounded-pill" onclick="return confirm('Delete this product? Note: You cannot delete products that have open orders. Consider setting quantity to 0 instead.')">Delete</a>
                        </div>
                    </div>
                </div>

                <!-- Edit Product Modal -->
                <div class="modal fade" id="editProductModal<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="fw-bold text-success">Edit Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="../actions/product_action.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Product Name</label>
                                        <input type="text" name="product_name" class="form-control rounded-pill px-3 bg-light border-0" value="<?= htmlspecialchars($row['product_name']) ?>" required>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted">Price (₹)</label>
                                            <input type="number" step="0.01" name="price" class="form-control rounded-pill px-3 bg-light border-0" value="<?= $row['price'] ?>" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted">Unit</label>
                                            <input type="text" name="unit" class="form-control rounded-pill px-3 bg-light border-0" value="<?= htmlspecialchars($row['unit'] ?? 'kg') ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Available Quantity</label>
                                        <input type="number" name="quantity" class="form-control rounded-pill px-3 bg-light border-0" value="<?= $row['quantity_available'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Category</label>
                                        <select name="category" class="form-select rounded-pill px-3 bg-light border-0" required>
                                            <option value="Vegetables" <?= $row['category'] == 'Vegetables' ? 'selected' : '' ?>>Vegetables</option>
                                            <option value="Fruits" <?= $row['category'] == 'Fruits' ? 'selected' : '' ?>>Fruits</option>
                                            <option value="Grains" <?= $row['category'] == 'Grains' ? 'selected' : '' ?>>Grains</option>
                                            <option value="Dairy" <?= $row['category'] == 'Dairy' ? 'selected' : '' ?>>Dairy</option>
                                            <option value="Other" <?= $row['category'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Update Image (Optional)</label>
                                        <input class="form-control rounded-pill px-3 bg-light border-0" type="file" name="product_image" accept="image/*">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold text-muted">Description</label>
                                        <textarea name="description" class="form-control rounded-4 bg-light border-0" rows="3" required><?= htmlspecialchars($row['description']) ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="submit" class="btn btn-premium btn-premium-green w-100 py-2 shadow">Update Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-seedling text-muted mb-3 opacity-25" style="font-size: 4rem;"></i>
                <h4 class="text-muted">Your inventory is empty.</h4>
                <p class="text-muted">Start listing your fresh harvest to reach customers.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-success">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/product_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Product Name</label>
                        <input type="text" name="product_name" class="form-control rounded-pill px-3 bg-light border-0" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Price (₹)</label>
                            <input type="number" step="0.01" name="price" class="form-control rounded-pill px-3 bg-light border-0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Unit (e.g. kg)</label>
                            <input type="text" name="unit" class="form-control rounded-pill px-3 bg-light border-0" value="kg" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Available Quantity</label>
                        <input type="number" name="quantity" class="form-control rounded-pill px-3 bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Category</label>
                        <select name="category" class="form-select rounded-pill px-3 bg-light border-0" required>
                            <option value="Vegetables">Vegetables</option>
                            <option value="Fruits">Fruits</option>
                            <option value="Grains">Grains</option>
                            <option value="Dairy">Dairy</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Product Image</label>
                        <input class="form-control rounded-pill px-3 bg-light border-0" type="file" name="product_image" id="product_image" accept="image/*">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">Description</label>
                        <textarea name="description" class="form-control rounded-4 bg-light border-0" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-premium btn-premium-green w-100 py-2 shadow">List Product</button>
                </div>
            </form>
        </div>
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
