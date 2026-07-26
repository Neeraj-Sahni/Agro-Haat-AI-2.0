<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

// Plan add karo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_name     = trim($_POST['plan_name']);
    $price         = floatval($_POST['price']);
    $duration_days = intval($_POST['duration_days']);
    $description   = trim($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO subscription_plans (plan_name, price, duration_days, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdis", $plan_name, $price, $duration_days, $description);

    if ($stmt->execute()) {
        $_SESSION['msg']      = "Plan added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg']      = "Failed to add plan!";
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: manage_plans.php");
    exit();
}

// Existing plans fetch karo
$plans = $conn->query("SELECT * FROM subscription_plans ORDER BY price ASC");

$page_title = "Manage Delivery Plans";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="fw-bold mb-4 text-success">📦 Manage Delivery Plans</h2>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show">
            <?= $_SESSION['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Add Plan Form -->
        <div class="col-lg-4">
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4">➕ Add New Plan</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Plan Name</label>
                        <input type="text" name="plan_name" class="form-control rounded-pill" placeholder="e.g. Weekly Basic" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Price (₹)</label>
                        <input type="number" name="price" class="form-control rounded-pill" placeholder="e.g. 499" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Duration (Days)</label>
                        <input type="number" name="duration_days" class="form-control rounded-pill" placeholder="e.g. 30" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Features (comma separated)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Fresh Vegetables, Free Delivery, Weekly Box"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold">Add Plan</button>
                </form>
            </div>
        </div>

        <!-- Existing Plans -->
        <div class="col-lg-8">
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4">📋 Existing Plans</h5>
                <?php if ($plans && $plans->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Plan Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Features</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($plan = $plans->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($plan['plan_name']) ?></td>
                                    <td>₹<?= number_format($plan['price'], 0) ?></td>
                                    <td><?= $plan['duration_days'] ?> days</td>
                                    <td class="small text-muted"><?= htmlspecialchars($plan['description']) ?></td>
                                    <td>
                                        <a href="?delete=<?= $plan['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill"
                                           onclick="return confirm('Delete this plan?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4">Koi plan nahi hai abhi — upar se add karo!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Delete plan
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM subscription_plans WHERE id = $del_id");
    header("Location: manage_plans.php");
    exit();
}
?>

<?php include '../includes/footer.inc.php'; ?>