<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'All';
$search = $_GET['search'] ?? '';

// Build Query
$query = "SELECT o.*, u.fullname as customer_name, u.email as customer_email 
          FROM orders o 
          JOIN users u ON o.customer_id = u.id 
          WHERE o.farmer_id = $farmer_id";

if ($status_filter !== 'All') {
    $query .= " AND o.status = '" . mysqli_real_escape_with_null($conn, $status_filter) . "'";
}

if (!empty($search)) {
    $query .= " AND u.fullname LIKE '%" . mysqli_real_escape_with_null($conn, $search) . "%'";
}

$query .= " ORDER BY o.order_date DESC";
$orders_res = $conn->query($query);

// Helper function for escaping
function mysqli_real_escape_with_null($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}

$page_title = "Manage Orders";
include '../includes/header.inc.php';
include '../includes/navbar_farmer.inc.php';
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in-up">
        <h2 class="text-success fw-bold mb-0">Customer Orders 📦</h2>
        <div class="d-flex">
            <form action="" method="GET" class="glass-card p-1 d-flex me-2">
                <input type="text" name="search" class="form-control border-0 bg-transparent" placeholder="Customer name..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-premium btn-premium-green ms-1"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="mb-4 animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="glass-card p-1 d-inline-flex overflow-auto" style="max-width: 100%;">
            <?php 
            $statuses = ['All', 'Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
            foreach ($statuses as $s): 
                $active = ($status_filter === $s) ? 'btn-premium btn-premium-green shadow' : 'text-muted';
            ?>
                <a href="?status=<?= $s ?>&search=<?= urlencode($search) ?>" class="btn btn-sm rounded-pill px-4 py-2 me-1 text-decoration-none <?= $active ?>"><?= $s ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4 animate-fade-in-up" style="animation-delay: 0.2s;">
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
                    <div class="glass-card p-4 h-100 overflow-hidden border-start border-4 border-<?= str_replace('bg-', '', $status_class) ?>">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h6 class="text-muted small fw-bold mb-1">ORDER #<?= sprintf('%05d', $order['id']) ?></h6>
                                <h5 class="fw-bold mb-0"><?= htmlspecialchars($order['customer_name']) ?></h5>
                                <small class="text-muted"><?= date('d M, Y | H:i', strtotime($order['order_date'])) ?></small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge <?= $status_class ?> rounded-pill px-4 py-2" style="font-size: 0.85rem;"><?= $order['status'] ?></span>
                            </div>
                            <div class="col-md-3 text-center">
                                <h5 class="fw-bold mb-0 text-success">₹<?= number_format($order['total_price'], 2) ?></h5>
                                <small class="text-muted">Payment Mode: Prepaid</small>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="btn btn-outline-success btn-sm rounded-pill px-4 me-2" data-bs-toggle="collapse" data-bs-target="#order-<?= $order['id'] ?>">View Details</button>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-premium btn-premium-green btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Update</button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li><a class="dropdown-item" href="../actions/update_order_status.php?id=<?= $order['id'] ?>&status=Confirmed">Confirm Order</a></li>
                                        <li><a class="dropdown-item" href="../actions/update_order_status.php?id=<?= $order['id'] ?>&status=Shipped">Mark as Shipped</a></li>
                                        <li><a class="dropdown-item" href="../actions/update_order_status.php?id=<?= $order['id'] ?>&status=Delivered">Mark as Delivered</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="../actions/update_order_status.php?id=<?= $order['id'] ?>&status=Cancelled">Cancel Order</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded Details -->
                        <div class="collapse mt-4" id="order-<?= $order['id'] ?>">
                            <div class="glass-card p-3 bg-light border-0">
                                <h6 class="fw-bold border-bottom pb-2 mb-3">Order Items</h6>
                                <table class="table table-sm table-borderless">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th>Item</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch order items (this is a simplification, assumes items are in a certain format in DB or can be fetched)
                                        // In reality, we'd have an order_items table. Let's assume we fetch them.
                                        $items_query = "SELECT p.product_name, oi.quantity, oi.price 
                                                       FROM order_items oi 
                                                       JOIN farm_products p ON oi.product_id = p.id 
                                                       WHERE oi.order_id = " . $order['id'];
                                        $items_res = $conn->query($items_query);
                                        if ($items_res):
                                            while($item = $items_res->fetch_assoc()):
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                <td class="text-center"><?= $item['quantity'] ?></td>
                                                <td class="text-end">₹<?= number_format($item['price'], 2) ?></td>
                                                <td class="text-end fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                            </tr>
                                        <?php endwhile; endif; ?>
                                    </tbody>
                                </table>
                                <div class="mt-3 text-muted small">
                                    <?php if (!empty($order['customer_lat']) && !empty($order['customer_lng'])): ?>
                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                        <?= htmlspecialchars($order['customer_address'] ?? 'Location shared') ?>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill ms-2"
                                            onclick="showDeliveryMap(
                                                <?= $order['customer_lat'] ?>,
                                                <?= $order['customer_lng'] ?>,
                                                '<?= htmlspecialchars(addslashes($order['customer_address'] ?? '')) ?>',
                                                '<?= htmlspecialchars(addslashes($order['customer_name'])) ?>'
                                            )">
                                            <i class="fas fa-map me-1"></i>View Map
                                        </button>
                                    <?php else: ?>
                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                        <span class="text-muted">Location not shared by customer</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-muted">No orders found for the selected criteria.</h4>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Map Modal -->
<div class="modal fade" id="deliveryMapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    Delivery Location — <span id="mapCustomerName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="deliveryMap" style="height: 400px;"></div>
                <div class="p-3 bg-light">
                    <p class="mb-0 small text-muted">
                        <i class="fas fa-map-pin text-danger me-2"></i>
                        <span id="mapAddress"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let deliveryMap = null;
let deliveryMarker = null;

function showDeliveryMap(lat, lng, address, customerName) {
    document.getElementById('mapCustomerName').innerText = customerName;
    document.getElementById('mapAddress').innerText = address || 'Address not available';

    const modal = new bootstrap.Modal(document.getElementById('deliveryMapModal'));
    modal.show();

    setTimeout(() => {
        if (!deliveryMap) {
            deliveryMap = L.map('deliveryMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(deliveryMap);
        } else {
            deliveryMap.setView([lat, lng], 15);
            if (deliveryMarker) deliveryMap.removeLayer(deliveryMarker);
        }

        deliveryMarker = L.marker([lat, lng])
            .addTo(deliveryMap)
            .bindPopup(`📍 <b>${customerName}</b><br>${address}`)
            .openPopup();

        deliveryMap.invalidateSize();
    }, 300);
}
</script>

<?php include '../includes/footer.inc.php'; ?>
