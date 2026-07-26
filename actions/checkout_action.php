<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['cart'])) {
    $customer_id = $_SESSION['user_id'];
    $customer_lat     = !empty($_POST['customer_lat']) ? floatval($_POST['customer_lat']) : null;
    $customer_lng     = !empty($_POST['customer_lng']) ? floatval($_POST['customer_lng']) : null;
    $customer_address = !empty($_POST['customer_address']) ? trim($_POST['customer_address']) : null;
    $conn->begin_transaction();
    
    try {
        $product_ids = implode(',', array_keys($_SESSION['cart']));
        $query = "SELECT * FROM farm_products WHERE id IN ($product_ids) FOR UPDATE";
        $result = $conn->query($query);
        
        $products = [];
        while($row = $result->fetch_assoc()) {
            $products[$row['id']] = $row;
        }
        
        // Group cart items by farmer_id
        $orders_by_farmer = [];
        foreach ($_SESSION['cart'] as $p_id => $item) {
            $qty = floatval($item['quantity']);
            if (isset($products[$p_id]) && $products[$p_id]['quantity_available'] >= $qty) {
                $farmer_id = $products[$p_id]['farmer_id'];
                $price = $products[$p_id]['price'];
                if (!isset($orders_by_farmer[$farmer_id])) {
                    $orders_by_farmer[$farmer_id] = ['total_price' => 0, 'items' => []];
                }
                $orders_by_farmer[$farmer_id]['total_price'] += ($qty * $price);
                $orders_by_farmer[$farmer_id]['items'][] = [
                    'product_id' => $p_id,
                    'quantity' => $qty,
                    'price' => $price
                ];
                
                $new_qty = $products[$p_id]['quantity_available'] - $qty;
                $update_stmt = $conn->prepare("UPDATE farm_products SET quantity_available = ? WHERE id = ?");
                $update_stmt->bind_param("di", $new_qty, $p_id);
                $update_stmt->execute();
            } else {
                throw new Exception("Product '{$products[$p_id]['product_name']}' is out of stock or requested quantity unavailable.");
            }
        }
        
        // Insert orders and order_items
        foreach ($orders_by_farmer as $farmer_id => $order_data) {
            $total_price = $order_data['total_price'];
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, farmer_id, total_price, status, order_date, customer_lat, customer_lng, customer_address) VALUES (?, ?, ?, 'Pending', CURRENT_TIMESTAMP, ?, ?, ?)");
            $stmt->bind_param("iiddds", $customer_id, $farmer_id, $total_price, $customer_lat, $customer_lng, $customer_address);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            
            foreach ($order_data['items'] as $item) {
                $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $item_stmt->bind_param("iidd", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }
        }
        $conn->commit();
        $_SESSION['cart'] = []; 
        $_SESSION['msg'] = "Order placed successfully! Farmers have been notified.";
        $_SESSION['msg_type'] = "success";
        header("Location: ../customer/my_orders.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['msg'] = "Checkout failed: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: ../customer/cart.php");
        exit();
    }
} else {
    header("Location: ../customer/cart.php");
    exit();
}
