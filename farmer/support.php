<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch recent support messages
$messages_res = $conn->query("SELECT * FROM messages WHERE sender_id = $user_id AND receiver_id = 0 ORDER BY created_at DESC LIMIT 5");

$page_title = "Expert Support";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="row g-5">
        <div class="col-md-7 animate-fade-in-up">
            <h2 class="text-success fw-bold mb-4">Connect with Experts 👨‍🔬</h2>
            <p class="text-muted mb-4">Get professional advice on crop diseases, soil health, and modern farming techniques. Our team of agronomists is here to help.</p>
            
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-3">Ask a Query</h5>
                <form action="../actions/support_action.php" method="POST">
                    <div class="mb-3">
                        <textarea name="query_text" class="form-control" rows="4" placeholder="Describe your issue or ask a question..." style="border-radius: 15px;" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-premium btn-premium-green w-100">Post Query</button>
                </form>
            </div>

            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type'] ?> mt-4 border-0 shadow-sm" role="alert">
                    <?= $_SESSION['msg'] ?>
                </div>
                <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="mt-5">
                <h5 class="fw-bold mb-3">Recent Queries</h5>
                <?php if ($messages_res && $messages_res->num_rows > 0): ?>
                    <?php while($row = $messages_res->fetch_assoc()): ?>
                        <div class="glass-card mb-3 p-3 border-start border-4 border-primary">
                            <p class="mb-1 fw-medium"><?= htmlspecialchars($row['message']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?= date('d M, Y H:i', strtotime($row['created_at'])) ?></small>
                                <span class="badge bg-light text-dark border">Pending response</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted italic">You haven't posted any queries yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-5 animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-4">Our Top Experts</h5>
                <div class="d-flex align-items-center mb-4">
                    <img src="https://i.pravatar.cc/150?u=expert1" class="rounded-circle me-3 border border-3 border-success" style="width: 60px; height: 60px;">
                    <div>
                        <h6 class="fw-bold mb-0">Dr. Rajesh Kumar</h6>
                        <small class="text-muted">Soil Microbiologist</small>
                        <div class="text-warning small">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-4">
                    <img src="https://i.pravatar.cc/150?u=expert2" class="rounded-circle me-3 border border-3 border-success" style="width: 60px; height: 60px;">
                    <div>
                        <h6 class="fw-bold mb-0">Ms. Anita Sharma</h6>
                        <small class="text-muted">Pest Control Specialist</small>
                        <div class="text-warning small">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-success w-100 rounded-pill mt-2">View All Experts</button>
            </div>

            <div class="glass-card p-4 bg-success text-white">
                <h5 class="fw-bold mb-3">Community Forum</h5>
                <p class="small text-light">Join over 5,000+ farmers in discussing crop insurance, subsidies, and success stories.</p>
                <a href="#" class="btn btn-light btn-sm rounded-pill px-4 fw-bold">Visit Forum</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.inc.php'; ?>
