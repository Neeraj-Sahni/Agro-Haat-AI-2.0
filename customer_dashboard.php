<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - AgroHaat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo"><i class="fa-solid fa-basket-shopping"></i> AgroHaat Customer</div>
        <div class="nav-links">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            <a href="chat.php"><i class="fa-solid fa-comments"></i> Messages</a>
            <a href="backend/logout.php" class="btn btn-danger" style="color:white;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Fresh From The Farm</h2>
            <p>Buy high-quality, fresh produce directly from farmers at fair prices.</p>
        </div>

        <div class="grid">
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-carrot"></i></div>
                <h3>Available Crops</h3>
                <p>Browse fresh vegetables, grains, and fruits listed directly by farmers.</p>
                <button class="btn btn-primary" onclick="openModal('shopModal')">Browse Products</button>
            </div>
            
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <h3>My Orders</h3>
                <p>Track your recent purchases and view your order history.</p>
                <button class="btn btn-primary" onclick="openModal('ordersModal')">Track Orders</button>
            </div>

            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-tags"></i></div>
                <h3>Best Deals</h3>
                <p>Check out discounted prices on bulk orders or seasonal produce.</p>
                <button class="btn btn-primary" onclick="openModal('dealsModal')">View Deals</button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="shopModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('shopModal')">&times;</span>
            <h2>🛒 Available Crops</h2>
            <p>Buy fresh produce directly from farmers!</p>
            <ul style="margin-left: 20px; margin-bottom: 15px;">
                <li>Fresh Tomatoes - ₹30/kg</li>
                <li>Organic Wheat - ₹25/kg</li>
                <li>Potatoes - ₹20/kg</li>
            </ul>
            <button class="btn btn-primary" onclick="alert('Added to cart')">Buy Now</button>
        </div>
    </div>
    
    <div id="ordersModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('ordersModal')">&times;</span>
            <h2>📦 My Orders</h2>
            <p><strong>Order #1024:</strong> 5kg Tomatoes - <span style="color:green">Delivered</span></p>
            <p><strong>Order #1025:</strong> 10kg Wheat - <span style="color:orange">On the way</span></p>
        </div>
    </div>

    <div id="dealsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('dealsModal')">&times;</span>
            <h2>💸 Best Deals</h2>
            <p>Buy 20kg of potatoes and get 10% off. Offer valid till tomorrow!</p>
        </div>
    </div>

    <!-- Floating Chatbot Button -->
    <div class="floating-chat" onclick="toggleChatbot()" title="AI Assistant">
        <i class="fa-solid fa-robot"></i>
    </div>

    <!-- Floating Chatbot UI -->
    <div class="chatbot-window" id="chatbotWindow">
        <div class="chatbot-header">
            <h3><i class="fa-solid fa-robot"></i> Agro-AI Assistant</h3>
            <span class="close-bot" onclick="toggleChatbot()">&times;</span>
        </div>
        <div class="chatbot-messages" id="botMessages">
            <div class="bot-msg">Hello! I'm Agro-AI. How can I assist you today? Ask me about weather, crops, or market prices.</div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="botInput" placeholder="Ask me anything...">
            <button class="btn btn-primary" onclick="sendBotMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
