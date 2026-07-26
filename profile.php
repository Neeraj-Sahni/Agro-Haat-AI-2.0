<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Update karo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location   = trim($_POST['location'] ?? '');
    $speciality = trim($_POST['speciality'] ?? '');
    $bio        = trim($_POST['bio'] ?? '');

    $stmt = $conn->prepare("UPDATE users SET location = ?, speciality = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("sssi", $location, $speciality, $bio, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Update failed!";
    }
    header("Location: profile.php");
    exit();
}

// Current data fetch karo
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$dashboard_link = ($role === 'Farmer') ? 'farmer/dashboard.php' : 'customer/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success">👤 Profile Settings</h2>
        <a href="<?= $dashboard_link ?>" class="btn btn-outline-secondary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i>Dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Profile Preview -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 text-center">
                <?php
                $image_path = "uploads/profile_pictures/" . ($user['image'] ?? 'default.jpg');
                $full_path  = __DIR__ . "/" . $image_path;
                ?>
                <?php if (file_exists($full_path) && ($user['image'] ?? '') != 'default.jpg'): ?>
                    <img src="<?= $image_path ?>"
                         class="rounded-circle mb-3 border border-4 border-success shadow"
                         style="width:120px;height:120px;object-fit:cover;">
                <?php else: ?>
                    <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:120px;height:120px;">
                        <span class="text-white" style="font-size:48px;">
                            <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['fullname']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-success"><?= htmlspecialchars($role) ?></span>

                <?php if (!empty($user['bio'])): ?>
                    <p class="text-muted small fst-italic mt-3">"<?= htmlspecialchars($user['bio']) ?>"</p>
                <?php endif; ?>

                <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                    <?php if (!empty($user['speciality'])): ?>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-seedling text-success me-1"></i><?= htmlspecialchars($user['speciality']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($user['location'])): ?>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($user['location']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Photo Upload -->
                <hr class="my-4">
                <h6 class="fw-bold mb-3">Change Profile Photo</h6>
                <form action="backend/upload_image.php" method="POST" enctype="multipart/form-data">
                    <input type="file" class="form-control mb-2" name="profile_image" accept="image/*" required>
                    <small class="text-muted d-block mb-3">Max 5MB. JPG, PNG allowed.</small>
                    <button type="submit" class="btn btn-outline-success rounded-pill w-100">
                        <i class="fas fa-upload me-2"></i>Upload Photo
                    </button>
                </form>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-4">✏️ Edit Profile</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Full Name</label>
                        <input type="text" class="form-control rounded-pill bg-light" 
                               value="<?= htmlspecialchars($user['fullname']) ?>" disabled>
                        <small class="text-muted">Name change ke liye admin se contact karo.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Email</label>
                        <input type="text" class="form-control rounded-pill bg-light" 
                               value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Location / City</label>
                        <input type="text" name="location" class="form-control rounded-pill"
                               placeholder="e.g. Nagpur, Maharashtra"
                               value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                    </div>

                    <?php if ($role === 'Farmer'): ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Speciality</label>
                        <select name="speciality" class="form-select rounded-pill">
                            <option value="">Select Speciality</option>
                            <?php
                            $specialities = ['Organic Farmer', 'Vegetable Specialist', 'Fruit Grower', 'Grain Farmer', 'Dairy Farmer', 'Poultry Farmer', 'Mixed Farming'];
                            foreach ($specialities as $sp):
                                $selected = ($user['speciality'] ?? '') === $sp ? 'selected' : '';
                            ?>
                                <option value="<?= $sp ?>" <?= $selected ?>><?= $sp ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="speciality" value="<?= htmlspecialchars($user['speciality'] ?? '') ?>">
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Bio / About</label>
                        <textarea name="bio" class="form-control" rows="3"
                                  placeholder="Apne baare mein kuch likho..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>