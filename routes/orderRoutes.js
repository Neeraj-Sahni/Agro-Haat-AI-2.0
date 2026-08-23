const express = require('express');
const router = express.Router();
const db = require('../config/db');

const isCustomer = (req, res, next) => {
  if (req.session && req.session.user_id && req.session.role === 'Customer') {
    return next();
  }
  return res.redirect('/login');
};

const isFarmer = (req, res, next) => {
  if (req.session && req.session.user_id && req.session.role === 'Farmer') {
    return next();
  }
  return res.redirect('/login');
};

// Customer Browse Products
router.get(['/customer/browse_products', '/customer/browse_products.php'], isCustomer, async (req, res) => {
  const search = req.query.search || '';
  const category = req.query.category || 'All';
  const farmerIdFilter = req.query.farmer_id;

  try {
    const [categories] = await db.query('SELECT DISTINCT category FROM farm_products WHERE quantity_available > 0');
    
    let query = `
      SELECT p.*, u.fullname as farmer_name 
      FROM farm_products p 
      JOIN users u ON p.farmer_id = u.id 
      WHERE p.quantity_available > 0
    `;
    const params = [];

    if (category !== 'All') {
      query += ' AND p.category = ?';
      params.push(category);
    }
    if (search) {
      query += ' AND p.product_name LIKE ?';
      params.push(`%${search}%`);
    }
    if (farmerIdFilter) {
      query += ' AND p.farmer_id = ?';
      params.push(farmerIdFilter);
    }
    query += ' ORDER BY p.created_at DESC';

    const [products] = await db.query(query, params);
    res.render('customer/browse_products', { products, categories, category, search, user: req.session, cart: req.session.cart });
  } catch (err) {
    console.error(err);
    res.render('customer/browse_products', { products: [], categories: [], category: 'All', search: '', user: req.session, cart: req.session.cart });
  }
});

// Cart Page
router.get(['/customer/cart', '/customer/cart.php'], isCustomer, (req, res) => {
  res.render('customer/cart', { cart: req.session.cart || {}, user: req.session });
});

// Cart Action handler
router.all('/actions/cart_action.php', isCustomer, async (req, res) => {
  req.session.cart = req.session.cart || {};
  const action = req.body.action || req.query.action;
  const productId = req.body.product_id || req.query.id;

  if (action === 'add' && productId) {
    const qty = parseInt(req.body.quantity) || 1;
    try {
      const [rows] = await db.query('SELECT * FROM farm_products WHERE id = ?', [productId]);
      if (rows.length > 0) {
        const prod = rows[0];
        if (req.session.cart[productId]) {
          req.session.cart[productId].quantity += qty;
        } else {
          req.session.cart[productId] = {
            id: prod.id,
            farmer_id: prod.farmer_id,
            name: prod.product_name,
            price: parseFloat(prod.price),
            product_image: prod.product_image,
            quantity: qty
          };
        }
      }
    } catch (err) {
      console.error(err);
    }
    return res.redirect('/customer/cart');
  }

  if (action === 'update' && productId) {
    const qtyChange = req.body.qty_change;
    if (req.session.cart[productId]) {
      if (qtyChange === 'plus') req.session.cart[productId].quantity += 1;
      if (qtyChange === 'minus') {
        req.session.cart[productId].quantity -= 1;
        if (req.session.cart[productId].quantity <= 0) {
          delete req.session.cart[productId];
        }
      }
    }
    return res.redirect('/customer/cart');
  }

  if (action === 'remove' && productId) {
    delete req.session.cart[productId];
    return res.redirect('/customer/cart');
  }

  res.redirect('/customer/cart');
});

// Checkout Action
router.post('/actions/checkout_action.php', isCustomer, async (req, res) => {
  const customerId = req.session.user_id;
  const cart = req.session.cart || {};

  if (Object.keys(cart).length === 0) {
    return res.redirect('/customer/browse_products');
  }

  const { customer_lat, customer_lng, customer_address } = req.body;

  try {
    for (let id in cart) {
      const item = cart[id];
      const totalPrice = item.price * item.quantity;
      await db.query(
        'INSERT INTO orders (customer_id, farmer_id, product_id, quantity, total_price, status) VALUES (?, ?, ?, ?, ?, ?)',
        [customerId, item.farmer_id, item.id, item.quantity, totalPrice, 'Pending']
      );
    }
    req.session.cart = {};
    return res.redirect('/customer/my_orders');
  } catch (err) {
    console.error(err);
    return res.redirect('/customer/cart');
  }
});

// Customer My Orders
router.get(['/customer/my_orders', '/customer/my_orders.php'], isCustomer, async (req, res) => {
  const customerId = req.session.user_id;
  const status_filter = req.query.status || 'All';

  try {
    let query = `
      SELECT o.*, u.fullname as farmer_name 
      FROM orders o 
      JOIN users u ON o.farmer_id = u.id 
      WHERE o.customer_id = ?
    `;
    const params = [customerId];

    if (status_filter !== 'All') {
      query += ' AND o.status = ?';
      params.push(status_filter);
    }
    query += ' ORDER BY o.order_date DESC';

    const [orders] = await db.query(query, params);
    res.render('customer/my_orders', { orders, status_filter, user: req.session, cart: req.session.cart });
  } catch (err) {
    console.error(err);
    res.render('customer/my_orders', { orders: [], status_filter: 'All', user: req.session, cart: req.session.cart });
  }
});

// Farmer View Orders
router.get(['/farmer/view_orders', '/farmer/view_orders.php'], isFarmer, async (req, res) => {
  const farmerId = req.session.user_id;
  const status_filter = req.query.status || 'All';
  const search = req.query.search || '';

  try {
    let query = `
      SELECT o.*, u.fullname as customer_name, u.email as customer_email 
      FROM orders o 
      JOIN users u ON o.customer_id = u.id 
      WHERE o.farmer_id = ?
    `;
    const params = [farmerId];

    if (status_filter !== 'All') {
      query += ' AND o.status = ?';
      params.push(status_filter);
    }
    if (search) {
      query += ' AND u.fullname LIKE ?';
      params.push(`%${search}%`);
    }
    query += ' ORDER BY o.order_date DESC';

    const [orders] = await db.query(query, params);
    res.render('farmer/view_orders', { orders, status_filter, search, user: req.session });
  } catch (err) {
    console.error(err);
    res.render('farmer/view_orders', { orders: [], status_filter: 'All', search: '', user: req.session });
  }
});

// Update Order Status (Farmer or Cancel by Customer)
router.get(['/actions/update_order_status.php', '/actions/cancel_order_action.php'], async (req, res) => {
  const { id, status } = req.query;
  const newStatus = status || 'Cancelled';

  try {
    await db.query('UPDATE orders SET status = ? WHERE id = ?', [newStatus, id]);
  } catch (err) {
    console.error(err);
  }

  if (req.session.role === 'Farmer') {
    return res.redirect('/farmer/view_orders');
  } else {
    return res.redirect('/customer/my_orders');
  }
});

module.exports = router;
