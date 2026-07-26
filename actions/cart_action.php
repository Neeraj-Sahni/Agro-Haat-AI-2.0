<?php
session_start();
require_once '../config/db.php';

$action = $_REQUEST['action'] ?? '';
$product_id = $_REQUEST['product_id'] ?? $_GET['id'] ?? 0;

if ($action == 'add' && $product_id > 0) {
    $qty = $_POST['quantity'] ?? 1;
    
    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM farm_products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $qty,
                'product_image' => $product['product_image'] ?? 'default_product.jpg'
            ];
        }
        $_SESSION['msg'] = "Added to cart!";
        $_SESSION['msg_type'] = "success";
    }
    header("Location: ../customer/browse_products.php");
    exit();
}

if ($action == 'update' && $product_id > 0) {
    if (isset($_SESSION['cart'][$product_id])) {
        if (isset($_POST['qty_change']) && $_POST['qty_change'] == 'plus') {
            $_SESSION['cart'][$product_id]['quantity']++;
        } else if (isset($_POST['qty_change']) && $_POST['qty_change'] == 'minus' && $_SESSION['cart'][$product_id]['quantity'] > 1) {
            $_SESSION['cart'][$product_id]['quantity']--;
        }
    }
    header("Location: ../customer/cart.php");
    exit();
}

if ($action == 'remove' && $product_id > 0) {
    unset($_SESSION['cart'][$product_id]);
    header("Location: ../customer/cart.php");
    exit();
}

if ($action == 'clear') {
    unset($_SESSION['cart']);
}

header("Location: ../customer/browse_products.php");
exit();
?>
