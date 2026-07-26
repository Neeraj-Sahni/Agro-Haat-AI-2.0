<?php
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top navbar-premium" style="background: var(--premium-gradient-blue);">
    <div class="container">
        <a class="navbar-brand navbar-brand-premium" href="dashboard.php">
            <i class="fas fa-shopping-basket me-2"></i>FarmToHome
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="browse_products.php">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" style="font-size: 0.65rem;"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="../public/uploads/profile_pictures/<?= htmlspecialchars($_SESSION['image'] ?? 'default.jpg') ?>" alt="Profile" class="rounded-circle border border-2 border-white me-2" style="width: 35px; height: 35px; object-fit: cover;">
                        <span class="d-none d-lg-inline"><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 animate-fade-in-up">
                        <li><a class="dropdown-item" href="../profile.php"><i class="fas fa-user-circle me-2 text-primary"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="my_orders.php"><i class="fas fa-box me-2 text-info"></i> My Orders</a></li>
                        <li><a class="dropdown-item" href="../change_password.php"><i class="fas fa-key me-2 text-warning"></i> Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../actions/logout_action.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
