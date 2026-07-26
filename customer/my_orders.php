
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'All';

// Build Query
$query = "SELECT o.*, u.fullname as farmer_name 
          FROM orders o 
          JOIN users u ON o.farmer_id = u.id 
          WHERE o.customer_id = $user_id";

if ($status_filter !== 'All') {
    $query .= " AND o.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

$query .= " ORDER BY o.order_date DESC";
$orders_res = $conn->query($query);

$page_title = "My Orders";
include '../includes/header.inc.php';
include '../includes/navbar_customer.inc.php';
?>
<!-- Leaflet Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="container mt-5 mb-5">


<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-5 animate-fade-in-up">
        <h2 class="fw-bold mb-0" style="color: var(--primary-blue) !important;">My Purchase History 📜</h2>
        <div class="glass-card p-1 d-flex overflow-auto">
            <?php 
            $statuses = ['All', 'Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
            foreach ($statuses as $s): 
                $active = ($status_filter === $s) ? 'btn-premium btn-premium-blue shadow' : 'text-muted';
            ?>
                <a href="?status=<?= $s ?>" class="btn btn-sm rounded-pill px-4 py-2 me-1 text-decoration-none <?= $active ?>"><?= $s ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4 animate-fade-in-up" style="animation-delay: 0.1s;">
        <?php if ($orders_res && $orders_res->num_rows > 0): ?>
            <?php while($order = $orders_res->fetch_assoc()): 
                $status_class = [
                    'Pending' => 'bg-secondary',
                    'Confirmed' => 'bg-primary',
                    'Shipped' => 'bg-info',
                    'Delivered' => 'bg-success',
                    'Cancelled' => 'bg-danger'
                ][$order['status']] ?? 'bg-light text-dark';
            ?>
                <div class="col-12">
                    <div class="glass-card p-4 h-100 overflow-hidden border-bottom border-4 border-<?= str_replace('bg-', '', $status_class) ?>">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h6 class="text-muted small fw-bold mb-1">ORDER #<?= sprintf('%05d', $order['id']) ?></h6>
                                <h5 class="fw-bold mb-0">Farmer: <?= htmlspecialchars($order['farmer_name']) ?></h5>
                                <small class="text-muted"><?= date('d M, Y', strtotime($order['order_date'])) ?></small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge <?= $status_class ?> rounded-pill px-4 py-2"><?= $order['status'] ?></span>
                            </div>
                            <div class="col-md-3 text-center">
                                <h5 class="fw-bold mb-0 text-primary">₹<?= number_format($order['total_price'], 2) ?></h5>
                                <small class="text-muted">Paid via: Online</small>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-4" data-bs-toggle="collapse" data-bs-target="#order-<?= $order['id'] ?>">Details</button>
                                <?php if ($order['status'] === 'Pending'): ?>
                                    <a href="../actions/cancel_order_action.php?id=<?= $order['id'] ?>" class="btn btn-link link-danger btn-sm text-decoration-none ms-2">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Details Collapse -->
                        <div class="collapse mt-4" id="order-<?= $order['id'] ?>">
                            <div class="p-3 bg-light rounded-4">
                                <h6 class="fw-bold mb-3 small text-uppercase">Items Purchased</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <?php
                                            $items_query = "SELECT p.product_name, oi.quantity, oi.price 
                                                        FROM order_items oi 
                                                        JOIN farm_products p ON oi.product_id = p.id 
                                                        WHERE oi.order_id = " . $order['id'];
                                            $items_res = $conn->query($items_query);
                                            while($item = $items_res->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                    <td class="text-center">x<?= $item['quantity'] ?></td>
                                                    <td class="text-end fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- ORDER TRACKING -->
                                <div class="mt-4">
                                    <h6 class="fw-bold mb-3 small text-uppercase">📦 Order Tracking</h6>
                                    
                                    <!-- Status Timeline -->
                                    <div class="d-flex justify-content-between align-items-center mb-4 position-relative">
                                        <div style="position:absolute; top:20px; left:0; right:0; height:3px; background:#e0e0e0; z-index:0;"></div>
                                        <?php
                                        $steps = ['Pending', 'Confirmed', 'Shipped', 'Delivered'];
                                        $currentIndex = array_search($order['status'], $steps);
                                        if ($currentIndex === false) $currentIndex = -1;
                                        foreach ($steps as $si => $step):
                                            $done = $si <= $currentIndex;
                                            $colors = ['Pending'=>'secondary','Confirmed'=>'primary','Shipped'=>'info','Delivered'=>'success'];
                                            $col = $done ? ($colors[$step] ?? 'secondary') : 'light';
                                        ?>
                                        <div class="text-center position-relative" style="z-index:1; flex:1;">
                                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center bg-<?= $col ?> <?= $done ? 'text-white' : 'text-muted border' ?>" style="width:40px;height:40px;">
                                                <?php if($step==='Pending'): ?><i class="fas fa-clock"></i>
                                                <?php elseif($step==='Confirmed'): ?><i class="fas fa-check"></i>
                                                <?php elseif($step==='Shipped'): ?><i class="fas fa-truck"></i>
                                                <?php elseif($step==='Delivered'): ?><i class="fas fa-home"></i>
                                                <?php endif; ?>
                                            </div>
                                            <small class="d-block mt-2 fw-bold <?= $done ? 'text-dark' : 'text-muted' ?>"><?= $step ?></small>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Map -->
                                    <?php if (!empty($order['customer_lat']) && !empty($order['customer_lng'])): ?>
                                        <div class="mt-3">
                                            <p class="small text-muted mb-2">
                                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                <?= htmlspecialchars($order['customer_address'] ?? 'Delivery Location') ?>
                                            </p>
                                            <div id="trackMap-<?= $order['id'] ?>" style="height:200px; border-radius:12px;"></div>
                                            <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                document.getElementById('order-<?= $order['id'] ?>').addEventListener('shown.bs.collapse', function() {
                                                    if (!window.trackMap_<?= $order['id'] ?>) {
                                                        window.trackMap_<?= $order['id'] ?> = L.map('trackMap-<?= $order['id'] ?>').setView([<?= $order['customer_lat'] ?>, <?= $order['customer_lng'] ?>], 15);
                                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.trackMap_<?= $order['id'] ?>);
                                                        L.marker([<?= $order['customer_lat'] ?>, <?= $order['customer_lng'] ?>])
                                                            .addTo(window.trackMap_<?= $order['id'] ?>)
                                                            .bindPopup('📍 Delivery Location')
                                                            .openPopup();
                                                    }
                                                    window.trackMap_<?= $order['id'] ?>.invalidateSize();
                                                });
                                            });
                                            </script>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning small py-2">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            Location share nahi ki thi order ke time — map unavailable.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-shopping-bag text-muted mb-3 opacity-25" style="font-size: 4rem;"></i>
                <h4 class="text-muted">You haven't placed any orders matching this filter.</h4>
                <a href="browse_products.php" class="btn btn-premium btn-premium-blue mt-3">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.inc.php'; ?>

