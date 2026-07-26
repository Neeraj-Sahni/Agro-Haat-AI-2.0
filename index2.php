<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Farmer Web Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css?v=1">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <img src="images/logo.jpg">
                    Agro-Haat
                </div>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#subscription">Subscription</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <div class="cta-buttons">
                    <Button class="btn btn-primary" onclick="window.location.href='login.php' ">I'm a farmer</Button>
                    <button class="btn btn-secondary" onclick="window.location.href='login.php'">I'm a Customer</button>
                </div>
                <button class="mobile-menu-btn">☰</button>
            </nav>
        </div>
    </header>
    <section class="hero" id="home">
        <div class="container">
            <h1>From Farm to Customer - Direct & fair</h1>
            <P>Empowering farmers, delighting Customers, transforming agriculture</P>
            <div class="btn-gap">
                <a href="#subscription" class="btn start">Start Subscription</a>
                <a href="#features" class="btn more">Learn More</a>
            </div>
        </div>
    </section>
    <section id="features">
        <div class="container">
            <h1 class="section-title">Why Choose Agro-Haat?</h1>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-cloud-sun-rain"></i>
                    </div>
                    <h3>Smart Weather Forecasting</h3>
                    <p>Real-time weather updates and help farmers plan sowing, irrigation, and harvesting with confidence.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                    <h3>Crop Analysis</h3>
                    <p>Upload crop photos for instant health analysis, diease detection, and expert recommendations.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-tractor"></i>
                    </div>
                    <h3>Machinery Rental</h3>
                    <p>Rent tractors, harvesters, and equipment from nearby owners at affordable rates without high purchase costs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-shop"></i>
                    </div>
                    <h3>Direct Marketplace</h3>
                    <p>Farmers sell directly to customers, removing middlemen and ensuring fair prices for both sides.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-brands fa-pagelines"></i>
                    </div>
                    <h3> Crop Suggestions</h3>
                    <p>Get personalized crop recommendations based on soil type, weather data, and market demand.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                    <h3>Smart Packaging</h3>
                    <P>Small pack sizes (5kg, 10kg, 25kg) reduce wastage and make fresh produce more accessible.</P>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Farm-to-customer delivery through local hubs ensures freshness and reduces spoilage.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <h3>Fair Pricing</h3>
                    <p>Transparent pricing with no hodden costs. farmers earn more, customers pay less.</p>
                </div>    
            </div>
        </div>
    </section>
    <section id="how-it-works">
        <div class="container">
            <h1 class="section-title">How it Works</h1>
            <div class="how-it-works-grid">
                <div class="how-it-work-card">
                    <div class="step-number">1</div>
                    <h3>Farmers List Products</h3>
                    <p>Farmers upload their crops with photos, pricing, and package options through the app or local representatives.</p>
                </div>
                <div class="how-it-work-card">
                    <div class="step-number">2</div>
                    <h3>Customers Browse & Order</h3>
                    <p>Customers search for fresh produce, select quantities, and place orders online or via WhatsApp.</p>
                </div>
                <div class="how-it-work-card">
                    <div class="step-number">3</div>
                    <h3>Hub Collection</h3>
                    <p>Produce is collected at local village hubs, sorted, and prepared for efficient delivery.</p>
                </div>
                <div class="how-it-work-card">
                    <div class="step-number">4</div>
                    <h3>Fast Delivery</h3>
                    <p>Direct farm-to-customer delivery ensures maximum freshness and minimal wastage.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="interactive-demo">
        <div class="container">
            <h1 class="section-title">Explore Our Services</h1>
            <div class="demo-selector">
                <button class="demo-btn active" onclick="showDemo('weather')">🌦️ Weather</button>            
                <button class="demo-btn" onclick="showDemo('crops')">🌱 Crop Analysis</button>            
                <button class="demo-btn" onclick="showDemo('machinery')">🚜 Machinery</button>            
                <button class="demo-btn" onclick="showDemo('marketplace')">🛒 Marketplace</button>                
            </div>
            <div class="demo-content" id="demoContent">
                <h3>Real-time Weather Forecasting</h3>
                <p><strong>Today's Weather:</strong>Sunny, 28°C</p>
                <p><strong>7-Day Forecast:</strong>Light rain expected on Day 3</p>
                <p><strong>Alerts:</strong>☀️ Moderate rainfall alert - Plan irrigation accordingly</p>
                <p><strong>Recommendations:</strong>Good conditions for sowing. consider postponing until after Day 3 rain.</p>
            </div>
        </div>
    </section>
    <section class="subscription" id="subscription">
        <div class="container">
            <h1 class="section-title">Subscription Plans</h1>
            <p style="text-align: center; margin-bottom: 2rem; color: #666;">Fresh vegetables delivered to your doorstep - automaticall!</p>
            <div class="subscription-cards">
                <div class="subscription-card">
                    <h3>Daily Pack</h3>
                    <div class="price">₹49<span style="font-size: 1rem;">/day</span></div>
                    <ul>
                        <li>Fresh vegetables daily</li>
                        <li>Perfect for daily cooking</li>
                        <li>Ultra-fresh produce</li>
                        <li>Free Home delivery</li>
                        <li>Cancel anytime</li>
                    </ul>
                    <button class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">Subscribe Now</button>
                </div>
                <div class="subscription-card">
                    <h3>Weekly Pack</h3>
                    <div class="price">₹99<span style="font-size: 1rem;">/week</span></div>
                    <ul>
                        <li>5-7 seasonal vegetables</li>
                        <li>Delivered twice a week</li>
                        <li>Fixed price guarantee</li>
                        <li>Family-friendly protions</li>
                        <li>Priority support</li>
                    </ul>
                    <button class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">Subscribe Now</button>
                </div>
                <div class="subscription-card">
                    <h3>Family Pack</h3>
                    <div class="price">₹999<span style="font-size: 1rem;">/month</span></div>
                    <ul>
                        <li>Higher quantity for families</li>
                        <li>Weekly delivery</li>
                        <li>Customizable Basket</li>
                        <li>Dedicated support</li>
                        <li>Free Home Delivery</li>
                    </ul>
                    <button class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">Subscribe Now</button>
                </div>
            </div>
        </div>
    </section>
    <section class="stats">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Making a Real Impact</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>5000+</h3>
                    <p>Farmers Connected</p>
                </div>
                <div class="stat-item">
                    <h3>25000+</h3>
                    <p>Happy Customers</p>
                </div>
                <div class="stat-item">
                    <h3>30%</h3>
                    <p>Higher Farmer Income</p>
                </div>
                <div class="stat-item">
                    <h3>40%</h3>
                    <p>Reduced Wastage</p>
                </div>
            </div>
        </div>
    </section>
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Agro-Haat</h3>
                <p>Connecting farmers directly customers for a fairer agricultural marketplace.</p>
            </div>
            <div class="footer-section">
                <h3>For Farmers</h3>
                <ul>
                    <li><a href="#"> Register as Farmer</a></li>
                    <li><a href="#">Weather Forecast</a></li>
                    <li><a href="#">Crop Analysis</a></li>
                    <li><a href="#">Machinery Rental</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>For Customers</h3>
                <ul>
                    <li><a href="#">Browse Products</a></li>
                    <li><a href="#">Subscription Plans</a></li>
                    <li><a href="#">Track Orders</a></li>
                    <li><a href="#">Contact Support</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li>📞 WhatsApp: +91-XXXXXXXXXX</li>
                    <li>📧 Email: support@agrohaat.com</li>
                    <li>📍 Local Collection Centers Across India</li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2026 Agro-Haat. Emowering Indian Agriculture.</p>
        </div>
    </footer>
    <script src="js/script.js?v=2S"></script>
</body>
</html>