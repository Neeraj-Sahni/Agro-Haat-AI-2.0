<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$farmer_id = $_SESSION['user_id'];

// ========== ADD PRODUCT ==========
if ($action == 'add') {
    $name  = trim($_POST['product_name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $unit  = trim($_POST['unit'] ?? 'kg');
    $qty   = floatval($_POST['quantity'] ?? 0);
    $cat   = $_POST['category'] ?? 'Vegetables';
    $desc  = trim($_POST['description'] ?? '');

    $product_image = 'default_product.jpg';

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed  = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png'];
        $filename = $_FILES['product_image']['name'];
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (array_key_exists($ext, $allowed)) {
            $new_filename = uniqid("prod_") . "." . $ext;
            $upload_dir   = __DIR__ . "/../public/uploads/farm_products/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $upload_dir . $new_filename)) {
                $product_image = $new_filename;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO farm_products (farmer_id, product_name, description, price, unit, quantity_available, category, product_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdsiss", $farmer_id, $name, $desc, $price, $unit, $qty, $cat, $product_image);

    if ($stmt->execute()) {
        $_SESSION['msg']      = "Product listed successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg']      = "Failed to list product: " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: ../farmer/manage_products.php");
    exit();
}

// ========== DELETE PRODUCT ==========
if ($action == 'delete') {
    $id = intval($_GET['id'] ?? 0);
    try {
        $stmt = $conn->prepare("DELETE FROM farm_products WHERE id = ? AND farmer_id = ?");
        $stmt->bind_param("ii", $id, $farmer_id);
        if ($stmt->execute()) {
            $_SESSION['msg']      = "Product removed from inventory.";
            $_SESSION['msg_type'] = "warning";
        } else {
            $_SESSION['msg']      = "Failed to remove product.";
            $_SESSION['msg_type'] = "danger";
        }
    } catch (Exception $e) {
        $_SESSION['msg']      = "Cannot delete — product has existing orders. Set quantity to 0 instead.";
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: ../farmer/manage_products.php");
    exit();
}

// ========== EDIT PRODUCT ==========
if ($action == 'edit') {
    $id    = intval($_POST['product_id'] ?? 0);
    $name  = trim($_POST['product_name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $unit  = trim($_POST['unit'] ?? 'kg');
    $qty   = floatval($_POST['quantity'] ?? 0);
    $cat   = $_POST['category'] ?? 'Vegetables';
    $desc  = trim($_POST['description'] ?? '');

    $new_filename = null;

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed  = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png'];
        $filename = $_FILES['product_image']['name'];
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (array_key_exists($ext, $allowed)) {
            $upload_dir = __DIR__ . "/../public/uploads/farm_products/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_filename = uniqid("prod_") . "." . $ext;

            if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $upload_dir . $new_filename)) {
                $new_filename = null;
            }
        }
    }

    if ($new_filename) {
        // Image bhi update karo
        $stmt = $conn->prepare("UPDATE farm_products SET product_name=?, description=?, price=?, unit=?, quantity_available=?, category=?, product_image=? WHERE id=? AND farmer_id=?");
        $stmt->bind_param("ssdsssii", $name, $desc, $price, $unit, $qty, $cat, $new_filename, $id, $farmer_id);
    } else {
        // Sirf data update karo
        $stmt = $conn->prepare("UPDATE farm_products SET product_name=?, description=?, price=?, unit=?, quantity_available=?, category=? WHERE id=? AND farmer_id=?");
        $stmt->bind_param("ssdsssii", $name, $desc, $price, $unit, $qty, $cat, $id, $farmer_id);
    }

    if ($stmt->execute()) {
        $_SESSION['msg']      = "Product updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg']      = "Failed to update: " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: ../farmer/manage_products.php");
    exit();
}

header("Location: ../farmer/manage_products.php");
exit();
?>