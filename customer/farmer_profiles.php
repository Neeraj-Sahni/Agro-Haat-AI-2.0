<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Connect with Farmers";
include '../includes/header.inc.php';
include '../includes/navbar_customer.inc.php';

// Farmers ke saath products count bhi fetch karo
$farmers_res = $conn->query("
    SELECT u.id, u.fullname, u.email, u.image, 
           u.location, u.speciality, u.bio,
           COUNT(p.id) as total_products,
           AVG(p.price) as avg_price
    FROM users u
    LEFT JOIN farm_products p ON p.farmer_id = u.id
    WHERE u.role = 'Farmer'
    GROUP BY u.id
");
?>

<div class="container mt-5 mb-5">
    <div class="text-center mb-5 animate-fade-in-up">
        <h2 class="fw-bold" style="color: var(--primary-blue) !important;">Meet Our Farmers 🌾</h2>
        <p class="text-muted">Direct communication fosters trust. Reach out to the producers of your food.</p>
    </div>

    <div class="row g-4 animate-fade-in-up" style="animation-delay: 0.1s;">
        <?php if ($farmers_res && $farmers_res->num_rows > 0): ?>
            <?php while($farmer = $farmers_res->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="glass-card p-4 h-100 text-center animate-fade-in-up">
                        
                        <!-- Profile Image -->
                        <img src="../public/uploads/profile_pictures/<?= htmlspecialchars($farmer['image'] ?? 'default.jpg') ?>" 
                             class="rounded-circle mb-3 border border-4 border-success shadow" 
                             style="width: 100px; height: 100px; object-fit: cover;" 
                             onerror="this.src='https://i.pravatar.cc/150?u=<?= $farmer['id'] ?>'">
                        
                        <!-- Name -->
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($farmer['fullname']) ?></h5>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($farmer['email']) ?></p>

                        <!-- Bio -->
                        <?php if (!empty($farmer['bio'])): ?>
                            <p class="text-muted small mb-3 fst-italic">"<?= htmlspecialchars($farmer['bio']) ?>"</p>
                        <?php endif; ?>
                        
                        <!-- Badges — Real Data -->
                        <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-seedling text-success me-1"></i>
                                <?= !empty($farmer['speciality']) ? htmlspecialchars($farmer['speciality']) : 'Farmer' ?>
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                <?= !empty($farmer['location']) ? htmlspecialchars($farmer['location']) : 'India' ?>
                            </span>
                        </div>

                        <!-- Stats -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-2">
                                    <h6 class="fw-bold mb-0 text-success"><?= $farmer['total_products'] ?? 0 ?></h6>
                                    <small class="text-muted">Products</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-2">
                                    <h6 class="fw-bold mb-0 text-primary">
                                        <?= $farmer['avg_price'] ? '₹' . number_format($farmer['avg_price'], 0) : 'N/A' ?>
                                    </h6>
                                    <small class="text-muted">Avg Price</small>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <a href="messages.php?receiver_id=<?= $farmer['id'] ?>" 
                               class="btn btn-premium btn-premium-blue w-100 rounded-pill">
                                <i class="fas fa-comment-dots me-2"></i>Send Message
                            </a>
                            <a href="browse_products.php?farmer_id=<?= $farmer['id'] ?>" 
                               class="btn btn-outline-success rounded-circle" 
                               style="width: 45px; height: 45px; min-width:45px;"
                               title="View Products">
                                <i class="fas fa-store"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-user-slash text-muted mb-3" style="font-size: 3rem;"></i>
                <p class="text-muted">No farmers registered yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.inc.php'; ?>