<?php
require_once '../config/db.php';

// Seed Subscription Plans
$plans = [
    ['Weekly Basket', 7, 499.00, '5kg Fresh Vegetables, 1kg Seasonal Fruits, Free Home Delivery.'],
    ['Daily Fresh', 30, 1799.00, 'Daily Morning Delivery, Customized Vegetable Mix, Zero Delivery Fees.'],
    ['Family Feast', 30, 2999.00, '15kg Weekly Big Basket, 3kg Fruits & Grains, Exclusive Farmer Deals.']
];

foreach ($plans as $plan) {
    $stmt = $conn->prepare("INSERT INTO subscription_plans (plan_name, duration_days, price, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE price=VALUES(price)");
    $stmt->bind_param("sids", $plan[0], $plan[1], $plan[2], $plan[3]);
    $stmt->execute();
}

// Seed Machinery
// Assuming we have at least one farmer user. Let's find one.
$farmer_res = $conn->query("SELECT id FROM users WHERE role = 'Farmer' LIMIT 1");
if ($farmer_res && $farmer_row = $farmer_res->fetch_assoc()) {
    $farmer_id = $farmer_row['id'];
    
    $machinery = [
        ['John Deere 5050D', 800.00, 'Available for plowing and tilling. Well maintained.', 'default_machinery.jpg'],
        ['Multi-Crop Harvester', 1500.00, 'High efficiency harvesting for wheat, rice, and corn.', 'default_machinery.jpg'],
        ['Power Tiller', 400.00, 'Compact and powerful for small farm operations.', 'default_machinery.jpg']
    ];

    foreach ($machinery as $m) {
        $stmt = $conn->prepare("INSERT INTO machinery (owner_id, machinery_name, rental_price_per_day, description, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $farmer_id, $m[0], $m[1], $m[2], $m[3]);
        $stmt->execute();
    }
}

echo "Database Seeded Successfully!";
?>
