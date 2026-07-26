<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch available plans
$plans = $conn->query("SELECT * FROM subscription_plans");

// Fetch current active subscription - expiry bhi check karo
$sub_query = "SELECT s.*, p.plan_name, p.id as plan_id FROM customer_subscriptions s 
              JOIN subscription_plans p ON s.plan_id = p.id 
              WHERE s.customer_id = ? AND s.status = 'Active' 
              AND s.expiry_date >= CURDATE()
              ORDER BY s.id DESC LIMIT 1";
$stmt = $conn->prepare($sub_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_sub = $stmt->get_result()->fetch_assoc();

$page_title = "Delivery Plans";
include '../includes/header.inc.php';
include '../includes/navbar_customer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="text-center mb-5 animate-fade-in-up">
        <h2 class="text-primary fw-bold" style="color: var(--primary-blue) !important;">Freshness on Autopilot 📦</h2>
        <p class="text-muted">Choose a plan that fits your family's needs and never run out of fresh organic produce.</p>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <?= $_SESSION['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
    <?php endif; ?>

    <?php if ($current_sub): ?>
        <div class="glass-card p-4 mb-5 border-start border-4 border-success animate-fade-in-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-success mb-1">Your Active Subscription</h5>
                    <p class="mb-0 text-muted">Plan: <strong><?= htmlspecialchars($current_sub['plan_name']) ?></strong> | Status: <span class="badge bg-success">Active</span></p>
                    <small class="text-muted">Expires on: <?= date('d M, Y', strtotime($current_sub['expiry_date'])) ?></small>
                </div>
                <i class="fas fa-check-circle fa-3x text-success opacity-25"></i>
            </div>
        </div>
    <?php else: ?>
        <div class="glass-card p-4 mb-5 border-start border-4 border-warning animate-fade-in-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-warning mb-1">No Active Subscription</h5>
                    <p class="mb-0 text-muted">Subscribe to a plan below to get fresh produce delivered to your door!</p>
                </div>
                <i class="fas fa-box-open fa-3x text-warning opacity-25"></i>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4 align-items-center justify-content-center">
        <?php if ($plans && $plans->num_rows > 0): ?>
            <?php 
            $colors = ['warning', 'info', 'success'];
            $i = 0;
            while($row = $plans->fetch_assoc()): 
                $color = $colors[$i % 3];
                $i++;

                // Ye plan current subscribed hai?
                $is_current = ($current_sub && $current_sub['plan_id'] == $row['id']);
            ?>
                <div class="col-lg-4 col-md-6 animate-fade-in-up" style="animation-delay: <?= $i * 0.1 ?>s;">
                    <div class="glass-card h-100 p-4 text-center d-flex flex-column border-top border-4 border-<?= $color ?> <?= $is_current ? 'shadow-lg' : '' ?>">
                        
                        <?php if ($is_current): ?>
                            <div class="badge bg-success mb-2 py-2">✅ Your Current Plan</div>
                        <?php endif; ?>

                        <h4 class="fw-bold mb-3"><?= htmlspecialchars($row['plan_name']) ?></h4>
                        <div class="mb-4">
                            <span class="display-5 fw-bold text-<?= $color ?>">₹<?= number_format($row['price'], 0) ?></span>
                            <span class="text-muted">/ <?= $row['duration_days'] ?> days</span>
                        </div>
                        <ul class="list-unstyled text-start flex-grow-1 mb-4">
                            <?php 
                            $features = explode(',', $row['description']);
                            foreach($features as $feat): 
                            ?>
                                <li class="mb-2"><i class="fas fa-check-circle text-<?= $color ?> me-2"></i> <?= trim($feat) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <form action="../actions/subscription_action.php" method="POST">
                            <input type="hidden" name="plan_id" value="<?= $row['id'] ?>">
                            <?php if ($is_current): ?>
                                <button type="button" class="btn btn-success w-100 rounded-pill" disabled>
                                    ✅ Current Plan
                                </button>
                            <?php elseif ($current_sub): ?>
                                <button type="submit" class="btn btn-outline-<?= $color == 'info' ? 'primary' : ($color == 'success' ? 'success' : 'warning') ?> w-100 rounded-pill">
                                    🔄 Switch to This Plan
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-premium w-100 btn-premium-<?= $color == 'info' ? 'blue' : ($color == 'success' ? 'green' : 'blue') ?>">
                                    Subscribe Now
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-muted">No subscription plans available right now. Check back soon!</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.inc.php'; ?>