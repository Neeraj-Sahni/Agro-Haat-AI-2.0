<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - AgroHaat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo"><i class="fa-solid fa-leaf"></i> AgroHaat Farmer</div>
        <div class="nav-links">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            <a href="chat.php"><i class="fa-solid fa-comments"></i> Messages</a>
            <a href="backend/logout.php" class="btn btn-danger" style="color:white;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Your Farming Command Center</h2>
            <p>Access smart insights, reach customers, and grow your business.</p>
        </div>

        <div class="grid">
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-seedling"></i></div>
                <h3>Crop Recommendations</h3>
                <p>Get AI-powered suggestions for the best crops to plant based on your soil and season.</p>
                <button class="btn btn-primary" onclick="openModal('cropModal')">View Suggestions</button>
            </div>
            
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-cloud-sun-rain"></i></div>
                <h3>Weather & Alerts</h3>
                <p>Real-time weather updates to help you plan your farming activities better.</p>
                <button class="btn btn-primary" onclick="openModal('weatherModal')">Check Weather</button>
            </div>

            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-microscope"></i></div>
                <h3>Soil Health Analysis</h3>
                <p>Upload your soil test results to get tailored fertilizer recommendations.</p>
                <button class="btn btn-primary" onclick="openModal('soilModal')">Analyze Soil</button>
            </div>

            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-bug"></i></div>
                <h3>Pest & Disease Detection</h3>
                <p>Upload a photo of your crop to instantly identify pests and get solutions.</p>
                <button class="btn btn-primary" onclick="openModal('pestModal')">Detect Pests</button>
            </div>

            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-chart-line"></i></div>
                <h3>Market Prices</h3>
                <p>Stay updated with the latest crop prices in your local and regional markets.</p>
                <button class="btn btn-primary" onclick="openModal('priceModal')">View Prices</button>
            </div>

            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-shop"></i></div>
                <h3>My Products</h3>
                <p>Manage your crop listings and sell directly to customers at fair prices.</p>
                <button class="btn btn-primary" onclick="alert('Feature coming soon!')">Manage Products</button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="cropModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('cropModal')">&times;</span>
            <h2>🌱 Crop Recommendations</h2>
            <p>Based on your region's soil and upcoming weather, we recommend planting <strong>Wheat</strong> or <strong>Mustard</strong> this season for optimal yield.</p>
        </div>
    </div>
    
    <div id="weatherModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('weatherModal')">&times;</span>
            <h2>🌦️ Weather Forecast</h2>
            <p><strong>Today:</strong> Sunny, 28°C</p>
            <p><strong>Tomorrow:</strong> Light Rain expected</p>
            <p><em>Alert:</em> Favorable conditions for sowing. Delay pesticide spraying.</p>
        </div>
    </div>

    <div id="soilModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('soilModal')">&times;</span>
            <h2>🔬 Soil Health Analysis</h2>
            <div class="form-group">
                <label>Upload Soil Test Image</label>
                <input type="file" accept="image/*">
            </div>
            <button class="btn btn-primary" onclick="alert('Analysis started!')">Analyze via AI</button>
        </div>
    </div>

    <div id="pestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('pestModal')">&times;</span>
            <h2>🐛 Pest & Disease Detection</h2>
            <div class="form-group">
                <label>Upload Crop Leaf Image</label>
                <input type="file" accept="image/*">
            </div>
            <button class="btn btn-primary" onclick="alert('Scanning for diseases...')">Detect with AI</button>
        </div>
    </div>

    <div id="priceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('priceModal')">&times;</span>
            <h2>📉 Market Prices</h2>
            <ul>
                <li>Wheat: ₹2,100 / Quintal <span style="color:green;">▲</span></li>
                <li>Rice: ₹1,950 / Quintal <span style="color:red;">▼</span></li>
                <li>Tomato: ₹1,200 / Quintal <span style="color:green;">▲</span></li>
            </ul>
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
