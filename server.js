const express = require('express');
const session = require('express-session');
const path = require('path');
const fs = require('fs');
const axios = require('axios');
const db = require('./config/db');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// View engine configuration
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Body parser & Session middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(
  session({
    secret: process.env.SESSION_SECRET || 'agrohaat_secret_key_2026',
    resave: false,
    saveUninitialized: false,
    cookie: { maxAge: 24 * 60 * 60 * 1000 } // 24 hours
  })
);

// Serve Static Files
app.use('/public', express.static(path.join(__dirname, 'public')));
app.use('/css', express.static(path.join(__dirname, 'css')));
app.use('/js', express.static(path.join(__dirname, 'js')));
app.use('/images', express.static(path.join(__dirname, 'images')));
app.use('/uploads', express.static(path.join(__dirname, 'public/uploads')));

// Ensure upload directories exist on startup
const uploadDirs = [
  path.join(__dirname, 'public/uploads/profile_pictures'),
  path.join(__dirname, 'public/uploads/farm_products'),
  path.join(__dirname, 'public/uploads/machinery'),
  path.join(__dirname, 'public/uploads/pest_scans')
];
uploadDirs.forEach(dir => {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

// Seed default subscription plans if table is empty
async function seedSubscriptionPlans() {
  try {
    const [rows] = await db.query('SELECT COUNT(*) as count FROM subscription_plans');
    if (rows[0].count === 0) {
      await db.query(`
        INSERT INTO subscription_plans (plan_name, duration_days, price, description) VALUES
        ('Daily Pack', 1, 49.00, 'Fresh vegetables daily, Perfect for daily cooking, Ultra-fresh produce, Free Home delivery, Cancel anytime'),
        ('Weekly Pack', 7, 99.00, '5-7 seasonal vegetables, Delivered twice a week, Fixed price guarantee, Family-friendly portions, Priority support'),
        ('Family Pack', 30, 999.00, 'Higher quantity for families, Weekly delivery, Customizable Basket, Dedicated support, Free Home Delivery')
      `);
    }
  } catch (e) {
    // Table might not exist yet
  }
}
seedSubscriptionPlans();

// Make user session globally available to EJS templates
app.use((req, res, next) => {
  res.locals.user = req.session.user_id ? req.session : null;
  res.locals.cart = req.session.cart || {};
  next();
});

// Import Routes
const authRoutes = require('./routes/authRoutes');
const productRoutes = require('./routes/productRoutes');
const orderRoutes = require('./routes/orderRoutes');
const aiRoutes = require('./routes/aiRoutes');
const chatRoutes = require('./routes/chatRoutes');
const machineryRoutes = require('./routes/machineryRoutes');
const subscriptionRoutes = require('./routes/subscriptionRoutes');

// Mount Routes
app.use('/', authRoutes);
app.use('/', productRoutes);
app.use('/', orderRoutes);
app.use('/', aiRoutes);
app.use('/', chatRoutes);
app.use('/', machineryRoutes);
app.use('/', subscriptionRoutes);

// Landing / Home Page
app.get(['/', '/index.php', '/index2.php'], (req, res) => {
  res.render('index', { user: req.session });
});

// Farmer Dashboard Route
app.get(['/farmer/dashboard', '/farmer/dashboard.php', '/farmer_dashboard.php'], async (req, res) => {
  if (!req.session || !req.session.user_id || req.session.role !== 'Farmer') {
    return res.redirect('/login');
  }

  const farmerId = req.session.user_id;
  let active_products = 0;
  let total_orders = 0;
  let monthly_revenue = 0;
  let products_sold = 0;

  try {
    const [pRes] = await db.query('SELECT COUNT(*) AS count FROM farm_products WHERE farmer_id = ?', [farmerId]);
    active_products = pRes[0]?.count || 0;

    const [oRes] = await db.query('SELECT COUNT(*) AS count FROM orders WHERE farmer_id = ?', [farmerId]);
    total_orders = oRes[0]?.count || 0;

    const [rRes] = await db.query(
      'SELECT SUM(total_price) AS sum FROM orders WHERE farmer_id = ? AND MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())',
      [farmerId]
    );
    monthly_revenue = rRes[0]?.sum || 0;

    try {
      const [sRes] = await db.query(
        "SELECT SUM(quantity) AS sum FROM orders WHERE farmer_id = ? AND status = 'Delivered'",
        [farmerId]
      );
      products_sold = sRes[0]?.sum || 0;
    } catch (e) {
      products_sold = 0;
    }
  } catch (err) {
    console.error(err);
  }

  res.render('farmer/dashboard', {
    active_products,
    total_orders,
    monthly_revenue,
    products_sold,
    user: req.session
  });
});

// Customer Dashboard Route
app.get(['/customer/dashboard', '/customer/dashboard.php', '/customer_dashboard.php'], async (req, res) => {
  if (!req.session || !req.session.user_id || req.session.role !== 'Customer') {
    return res.redirect('/login');
  }

  const customerId = req.session.user_id;
  let total_products = 0;
  let total_orders = 0;

  try {
    const [pRes] = await db.query('SELECT COUNT(*) as total FROM farm_products WHERE quantity_available > 0');
    total_products = pRes[0]?.total || 0;

    const [oRes] = await db.query('SELECT COUNT(*) as total FROM orders WHERE customer_id = ?', [customerId]);
    total_orders = oRes[0]?.total || 0;
  } catch (err) {
    console.error(err);
  }

  res.render('customer/dashboard', {
    total_products,
    total_orders,
    user: req.session,
    cart: req.session.cart
  });
});

// Farmer Weather Route
app.get(['/farmer/weather', '/farmer/weather.php'], async (req, res) => {
  if (!req.session || !req.session.user_id) return res.redirect('/login');

  require('dotenv').config();
  const city = req.query.city || 'Nagpur';
  const WEATHER_API_KEY = process.env.WEATHER_API_KEY || 'e0257658d3681b4d64fe09a5d00ee9c5';

  let temp = 28, humidity = 65, wind = 12, uv = 5;
  let condition = 'Sunny';
  let dailyForecast = [];

  // 1. Fetch Current Weather
  try {
    const wRes = await axios.get(
      `https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${WEATHER_API_KEY}&units=metric`,
      { timeout: 8000 }
    );
    if (wRes.data && wRes.data.main) {
      temp = Math.round(wRes.data.main.temp);
      humidity = wRes.data.main.humidity;
      wind = Math.round(wRes.data.wind.speed * 3.6);
      condition = wRes.data.weather[0].description;
      condition = condition.charAt(0).toUpperCase() + condition.slice(1);
    }
  } catch (e) {
    console.error('Weather Current API Error:', e.response?.data || e.message);
  }

  // 2. Fetch 5-Day Forecast
  try {
    const fRes = await axios.get(
      `https://api.openweathermap.org/data/2.5/forecast?q=${encodeURIComponent(city)}&appid=${WEATHER_API_KEY}&units=metric`,
      { timeout: 8000 }
    );
    if (fRes.data && fRes.data.list) {
      const seenDays = [];
      const iconMap = {
        '01': 'sun',
        '02': 'cloud-sun',
        '03': 'cloud',
        '04': 'cloud',
        '09': 'cloud-showers-heavy',
        '10': 'cloud-rain',
        '11': 'bolt',
        '13': 'snowflake',
        '50': 'smog'
      };

      fRes.data.list.forEach(item => {
        const dateObj = new Date(item.dt * 1000);
        const day = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
        if (!seenDays.includes(day) && dailyForecast.length < 5) {
          seenDays.push(day);
          const iconCode = item.weather[0].icon.substring(0, 2);
          dailyForecast.push({
            day,
            max: Math.round(item.main.temp_max),
            min: Math.round(item.main.temp_min),
            icon: iconMap[iconCode] || 'cloud'
          });
        }
      });
    }
  } catch (e) {
    console.error('Weather Forecast API Error:', e.response?.data || e.message);
  }

  if (dailyForecast.length === 0) {
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const todayIdx = new Date().getDay();
    for (let i = 0; i < 5; i++) {
      const dName = days[(todayIdx + i) % 7];
      dailyForecast.push({
        day: dName,
        max: temp + Math.floor(Math.random() * 3),
        min: temp - Math.floor(Math.random() * 4) - 2,
        icon: 'cloud-sun'
      });
    }
  }

  res.render('farmer/weather', {
    city,
    temp,
    humidity,
    wind,
    uv,
    condition,
    dailyForecast,
    user: req.session
  });
});

// Farmer Crop Recommendation Page
app.get(['/farmer/crop_recommendation', '/farmer/crop_recommendation.php'], async (req, res) => {
  if (!req.session || !req.session.user_id || req.session.role !== 'Farmer') return res.redirect('/login');
  const userId = req.session.user_id;

  try {
    const [history] = await db.query(
      "SELECT * FROM ai_recommendations WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5",
      [userId]
    );
    res.render('farmer/crop_recommendation', { history, user: req.session });
  } catch (err) {
    res.render('farmer/crop_recommendation', { history: [], user: req.session });
  }
});

// Farmer Soil Analysis Page
app.get(['/farmer/soil_analysis', '/farmer/soil_analysis.php'], async (req, res) => {
  if (!req.session || !req.session.user_id || req.session.role !== 'Farmer') return res.redirect('/login');
  const userId = req.session.user_id;

  try {
    const [history] = await db.query(
      "SELECT * FROM ai_recommendations WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5",
      [userId]
    );
    res.render('farmer/soil_analysis', { history, user: req.session });
  } catch (err) {
    res.render('farmer/soil_analysis', { history: [], user: req.session });
  }
});

// Farmer Pest Detection Page
app.get(['/farmer/pest_detection', '/farmer/pest_detection.php'], async (req, res) => {
  if (!req.session || !req.session.user_id || req.session.role !== 'Farmer') return res.redirect('/login');
  const userId = req.session.user_id;

  try {
    const [history] = await db.query(
      "SELECT * FROM ai_recommendations WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5",
      [userId]
    );
    res.render('farmer/pest_detection', { history, user: req.session });
  } catch (err) {
    res.render('farmer/pest_detection', { history: [], user: req.session });
  }
});

// Farmer Market Prices Page
app.get(['/farmer/market_prices', '/farmer/market_prices.php'], (req, res) => {
  if (!req.session || !req.session.user_id || req.session.role !== 'Farmer') return res.redirect('/login');
  const search = req.query.search || '';

  const market_data = [
    { crop: 'Wheat', market: 'Agroha Mandi, Haryana', price: 2125, min: 2000, max: 2250, unit: 'Quintal', trend: 'up' },
    { crop: 'Mustard', market: 'Jaipur Mandi, Rajasthan', price: 5450, min: 5200, max: 5600, unit: 'Quintal', trend: 'down' },
    { crop: 'Rice (Basmati)', market: 'Karnal Mandi, Haryana', price: 3800, min: 3600, max: 4000, unit: 'Quintal', trend: 'stable' },
    { crop: 'Potato', market: 'Azadpur Mandi, Delhi', price: 1200, min: 1100, max: 1350, unit: 'Quintal', trend: 'up' },
    { crop: 'Onion', market: 'Lasalgaon Mandi, Maharashtra', price: 1850, min: 1700, max: 2000, unit: 'Quintal', trend: 'up' },
    { crop: 'Cotton', market: 'Rajkot Mandi, Gujarat', price: 7200, min: 7000, max: 7400, unit: 'Quintal', trend: 'down' },
  ];

  const filtered = search
    ? market_data.filter(d => d.crop.toLowerCase().includes(search.toLowerCase()) || d.market.toLowerCase().includes(search.toLowerCase()))
    : market_data;

  res.render('farmer/market_prices', { market_data: filtered, search, user: req.session });
});

// Start Node.js express server
app.listen(PORT, () => {
  console.log(`Agro-Haat AI Node.js server running on http://localhost:${PORT}`);
});
