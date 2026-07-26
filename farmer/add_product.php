<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];
$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $unit = trim($_POST['unit']);
    $quantity = $_POST['quantity_available'];
    $description = trim($_POST['description']);
    $product_image = 'default_product.jpg';

    // Handle File Upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
        $filename = $_FILES['product_image']['name'];
        $filetype = $_FILES['product_image']['type'];
        $filesize = $_FILES['product_image']['size'];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists(strtolower($ext), $allowed)) {
            $message = "Error: Please select a valid file format (JPG, JPEG, PNG, GIF).";
            $messageType = "danger";
        } elseif ($filesize > 5 * 1024 * 1024) {
            $message = "Error: File size is larger than the allowed limit (5MB).";
            $messageType = "danger";
        } else {
            // Verify MIME type
            if (in_array($filetype, $allowed)) {
                $new_filename = uniqid("prod_") . "." . $ext;
                $upload_path = "../public/uploads/farm_products/" . $new_filename;
                
                if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $upload_path)) {
                    $product_image = $new_filename;
                } else {
                    $message = "Error: Failed to upload image.";
                    $messageType = "danger";
                }
            } else {
                $message = "Error: There was a problem with your file upload.";
                $messageType = "danger";
            }
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO farm_products (farmer_id, product_name, category, price, unit, quantity_available, product_image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdsss", $farmer_id, $product_name, $category, $price, $unit, $quantity, $product_image, $description);
        
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Product added successfully!";
            $_SESSION['msg_type'] = "success";
            header("Location: manage_products.php");
            exit();
        } else {
            $message = "Error: Could not list the product. " . $stmt->error;
            $messageType = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - AI Smart Farming</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body {
            background-color: #f4f7f6;
            font-family: 'Inter', sans-serif;
        }
        .navbar {
            background-color: #2e7d32;
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #2e7d32;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="manage_products.php"><i class="fas fa-arrow-left me-2"></i>Back to Inventory</a>
            <span class="navbar-text text-white fw-bold ms-auto">
                <i class="fas fa-plus-circle me-1"></i> Add Product
            </span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5 mb-5 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <h2 class="text-success fw-bold text-center mb-4">List a New Product</h2>
                
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card bg-white">
                    <form action="add_product.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-tag me-1"></i> Product Name</label>
                            <input type="text" name="product_name" class="form-control" placeholder="e.g. Organic Tomatoes" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-layer-group me-1"></i> Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="" disabled selected>Select Category</option>
                                    <option value="Vegetables">Vegetables</option>
                                    <option value="Fruits">Fruits</option>
                                    <option value="Grains">Grains</option>
                                    <option value="Dairy">Dairy</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label"><i class="fas fa-image me-1"></i> Product Image</label>
                                <input class="form-control" type="file" name="product_image" id="product_image">
                                <div class="form-text">Optional. Max size: 5MB (JPG, PNG, GIF).</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-rupee-sign me-1"></i> Price</label>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="form-label"><i class="fas fa-balance-scale me-1"></i> Unit</label>
                                <select name="unit" class="form-select" required>
                                    <option value="kg">per kg</option>
                                    <option value="gram">per 100g</option>
                                    <option value="piece">per piece</option>
                                    <option value="dozen">per dozen</option>
                                    <option value="liter">per liter</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="form-label"><i class="fas fa-boxes me-1"></i> Available Qty</label>
                                <input type="number" step="0.01" name="quantity_available" class="form-control" placeholder="e.g. 50" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-align-left me-1"></i> Product Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Describe the quality, freshness, or farming method..." required></textarea>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 shadow-sm fw-bold">
                                <i class="fas fa-check-circle me-2"></i> List Product for Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
