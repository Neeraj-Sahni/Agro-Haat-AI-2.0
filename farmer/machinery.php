<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all available machinery except owned by current user (for renting)
$other_machinery = $conn->query("SELECT m.*, u.fullname as owner_name FROM machinery m JOIN users u ON m.owner_id = u.id WHERE m.availability_status = 'Available' AND m.owner_id != $user_id");

// Fetch my machinery
$my_machinery = $conn->query("SELECT * FROM machinery WHERE owner_id = $user_id");

$page_title = "Machinery Rental";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in-up">
        <h2 class="text-success fw-bold mb-0">Machinery Rental 🚜</h2>
        <button class="btn btn-premium btn-premium-green" data-bs-toggle="modal" data-bs-target="#addMachineryModal">
            <i class="fas fa-plus me-2"></i>List My Machinery
        </button>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <?= $_SESSION['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
    <?php endif; ?>

    <ul class="nav nav-pills mb-4 glass-card p-1 d-inline-flex" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="pills-rent-tab" data-bs-toggle="pill" data-bs-target="#pills-rent" type="button" role="tab">Available for Rent</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="pills-my-tab" data-bs-toggle="pill" data-bs-target="#pills-my" type="button" role="tab">My Machinery</button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <!-- Rent Tab -->
        <div class="tab-pane fade show active" id="pills-rent" role="tabpanel">
            <div class="row g-4">
                <?php if ($other_machinery && $other_machinery->num_rows > 0): ?>
                    <?php while($row = $other_machinery->fetch_assoc()): ?>
                        <div class="col-lg-4 col-md-6 animate-fade-in-up">
                            <div class="glass-card h-100 overflow-hidden">
                                <div class="p-0 position-relative">
                                    <img src="../public/uploads/machinery/<?= htmlspecialchars($row['image'] ?? 'default_machinery.jpg') ?>" class="w-100" style="height: 200px; object-fit: cover;" onerror="this.src='https://placehold.co/600x400?text=Machinery'">
                                    <span class="badge bg-success position-absolute top-0 end-0 m-3 shadow">Available</span>
                                </div>
                                <div class="p-4">
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($row['machinery_name']) ?></h5>
                                    <p class="text-muted small mb-2">Owned by: <span class="fw-bold"><?= htmlspecialchars($row['owner_name']) ?></span></p>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($row['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="fw-bold text-success fs-5">₹<?= number_format($row['rental_price_per_day'], 0) ?>/day</span>
                                        <form action="../actions/machinery_action.php" method="POST">
                                            <input type="hidden" name="action" value="rent">
                                            <input type="hidden" name="machinery_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-premium btn-premium-green btn-sm">Rent Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-muted">No machinery currently available.</h4>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Machinery Tab -->
        <div class="tab-pane fade" id="pills-my" role="tabpanel">
            <div class="row g-4">
                <?php if ($my_machinery && $my_machinery->num_rows > 0): ?>
                    <?php while($row = $my_machinery->fetch_assoc()): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="glass-card h-100 overflow-hidden border-start border-4 border-<?= $row['availability_status'] == 'Available' ? 'success' : 'warning' ?>">
                                <div class="p-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($row['machinery_name']) ?></h5>
                                        <span class="badge bg-<?= $row['availability_status'] == 'Available' ? 'success' : 'warning' ?>"><?= $row['availability_status'] ?></span>
                                    </div>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($row['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-success">₹<?= number_format($row['rental_price_per_day'], 0) ?>/day</span>
                                        <button class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-tractor text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-muted">You haven't listed any machinery yet.</h4>
                        <button class="btn btn-success mt-3 rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addMachineryModal">List Item</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addMachineryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-success">List Your Machinery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/machinery_action.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Machinery Name</label>
                        <input type="text" name="machinery_name" class="form-control rounded-pill" placeholder="e.g. John Deere Tractor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Rental Price (per day)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent rounded-start-pill">₹</span>
                            <input type="number" name="rental_price" class="form-control rounded-end-pill" placeholder="800" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Machinery Image</label>
                        <input class="form-control rounded-pill px-3 bg-light border-0" type="file" name="machinery_image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Condition, horsepower, etc." style="border-radius: 15px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-premium btn-premium-green w-100">Submit Listing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.inc.php'; ?>
