const express = require('express');
const router = express.Router();
const db = require('../config/db');

const isCustomer = (req, res, next) => {
  if (req.session && req.session.user_id && req.session.role === 'Customer') return next();
  return res.redirect('/login');
};

const isAuth = (req, res, next) => {
  if (req.session && req.session.user_id) return next();
  return res.redirect('/login');
};

// Customer Delivery Plans Page
router.get(['/customer/delivery_plans', '/customer/delivery_plans.php'], isCustomer, async (req, res) => {
  const userId = req.session.user_id;

  try {
    const [plans] = await db.query('SELECT * FROM subscription_plans');

    const [subs] = await db.query(
      `SELECT s.*, p.plan_name, p.id as plan_id 
       FROM customer_subscriptions s 
       JOIN subscription_plans p ON s.plan_id = p.id 
       WHERE s.customer_id = ? AND s.status = 'Active' 
       ORDER BY s.id DESC LIMIT 1`,
      [userId]
    );

    const current_sub = subs.length > 0 ? subs[0] : null;
    const msg = req.session.msg;
    const msg_type = req.session.msg_type;
    delete req.session.msg;
    delete req.session.msg_type;

    res.render('customer/delivery_plans', { plans, current_sub, msg, msg_type, user: req.session, cart: req.session.cart });
  } catch (err) {
    console.error(err);
    res.render('customer/delivery_plans', { plans: [], current_sub: null, user: req.session, cart: req.session.cart });
  }
});

// Subscription Action
router.post('/actions/subscription_action.php', isCustomer, async (req, res) => {
  const userId = req.session.user_id;
  const { plan_id } = req.body;

  try {
    const [planRows] = await db.query('SELECT * FROM subscription_plans WHERE id = ?', [plan_id]);
    if (planRows.length === 0) {
      req.session.msg = 'Invalid plan selected.';
      req.session.msg_type = 'danger';
      return res.redirect('/customer/delivery_plans');
    }

    const plan = planRows[0];
    const durationDays = plan.duration_days || 30;

    const startDate = new Date();
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + durationDays);

    // Cancel existing active subscriptions
    await db.query("UPDATE customer_subscriptions SET status = 'Cancelled' WHERE customer_id = ?", [userId]);

    // Insert new subscription
    await db.query(
      'INSERT INTO customer_subscriptions (customer_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)',
      [userId, plan_id, startDate.toISOString().slice(0, 10), endDate.toISOString().slice(0, 10), 'Active']
    );

    req.session.msg = `Successfully subscribed to ${plan.plan_name}!`;
    req.session.msg_type = 'success';
  } catch (err) {
    console.error(err);
    req.session.msg = 'Subscription failed: ' + err.message;
    req.session.msg_type = 'danger';
  }

  res.redirect('/customer/delivery_plans');
});

// Farmer Profiles Page
router.get(['/customer/farmer_profiles', '/customer/farmer_profiles.php'], isCustomer, async (req, res) => {
  try {
    const [farmers] = await db.query(`
      SELECT u.id, u.fullname, u.email, u.image, 
             u.location, u.speciality, u.bio,
             COUNT(p.id) as total_products,
             AVG(p.price) as avg_price
      FROM users u
      LEFT JOIN farm_products p ON p.farmer_id = u.id
      WHERE u.role = 'Farmer'
      GROUP BY u.id
    `);
    res.render('customer/farmer_profiles', { farmers, user: req.session, cart: req.session.cart });
  } catch (err) {
    console.error(err);
    res.render('customer/farmer_profiles', { farmers: [], user: req.session, cart: req.session.cart });
  }
});

// Offers & FAQs Page
router.get(['/customer/offers', '/customer/offers.php'], isCustomer, (req, res) => {
  res.render('customer/offers', { user: req.session, cart: req.session.cart });
});

module.exports = router;
