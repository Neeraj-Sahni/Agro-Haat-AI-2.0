const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const db = require('../config/db');

const prodStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dir = path.join(__dirname, '../public/uploads/farm_products');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, 'prod_' + Date.now() + ext);
  }
});
const uploadProd = multer({ storage: prodStorage });

const isFarmer = (req, res, next) => {
  if (req.session && req.session.user_id && req.session.role === 'Farmer') {
    return next();
  }
  return res.redirect('/login');
};

// Add Product Page
router.get(['/farmer/add_product', '/farmer/add_product.php'], isFarmer, (req, res) => {
  res.render('farmer/add_product', { user: req.session });
});

// Manage Products Page
router.get(['/farmer/manage_products', '/farmer/manage_products.php'], isFarmer, async (req, res) => {
  const farmerId = req.session.user_id;
  try {
    const [products] = await db.query(
      'SELECT * FROM farm_products WHERE farmer_id = ? ORDER BY created_at DESC',
      [farmerId]
    );
    const msg = req.session.msg;
    const msg_type = req.session.msg_type;
    delete req.session.msg;
    delete req.session.msg_type;
    res.render('farmer/manage_products', { products, msg, msg_type, user: req.session });
  } catch (err) {
    console.error(err);
    res.render('farmer/manage_products', { products: [], msg: 'Error loading inventory', msg_type: 'danger', user: req.session });
  }
});

// Add Product Action (Form submit from add_product page)
router.post(['/farmer/add_product', '/farmer/add_product.php'], isFarmer, uploadProd.single('product_image'), async (req, res) => {
  const farmerId = req.session.user_id;
  const { product_name, category, price, unit, quantity_available, description } = req.body;
  const productImage = req.file ? req.file.filename : 'default_product.jpg';

  try {
    await db.query(
      'INSERT INTO farm_products (farmer_id, product_name, category, price, unit, quantity_available, product_image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [farmerId, product_name, category, price, unit || 'kg', quantity_available, productImage, description]
    );
    req.session.msg = 'Product added successfully!';
    req.session.msg_type = 'success';
    return res.redirect('/farmer/manage_products');
  } catch (err) {
    console.error(err);
    return res.render('farmer/add_product', { message: 'Error listing product: ' + err.message, messageType: 'danger', user: req.session });
  }
});

// Action Product Handler (Modal / Action forms)
router.post('/actions/product_action.php', isFarmer, uploadProd.single('product_image'), async (req, res) => {
  const farmerId = req.session.user_id;
  const action = req.body.action;

  if (action === 'add') {
    const { product_name, category, price, unit, quantity, description } = req.body;
    const productImage = req.file ? req.file.filename : 'default_product.jpg';
    try {
      await db.query(
        'INSERT INTO farm_products (farmer_id, product_name, category, price, unit, quantity_available, product_image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [farmerId, product_name, category, price, unit || 'kg', quantity, productImage, description]
      );
      req.session.msg = 'Product listed successfully!';
      req.session.msg_type = 'success';
    } catch (err) {
      req.session.msg = 'Error adding product: ' + err.message;
      req.session.msg_type = 'danger';
    }
    return res.redirect('/farmer/manage_products');
  }

  if (action === 'edit') {
    const { product_id, product_name, category, price, unit, quantity, description } = req.body;
    try {
      if (req.file) {
        await db.query(
          'UPDATE farm_products SET product_name = ?, category = ?, price = ?, unit = ?, quantity_available = ?, product_image = ?, description = ? WHERE id = ? AND farmer_id = ?',
          [product_name, category, price, unit, quantity, req.file.filename, description, product_id, farmerId]
        );
      } else {
        await db.query(
          'UPDATE farm_products SET product_name = ?, category = ?, price = ?, unit = ?, quantity_available = ?, description = ? WHERE id = ? AND farmer_id = ?',
          [product_name, category, price, unit, quantity, description, product_id, farmerId]
        );
      }
      req.session.msg = 'Product updated successfully!';
      req.session.msg_type = 'success';
    } catch (err) {
      req.session.msg = 'Error updating product: ' + err.message;
      req.session.msg_type = 'danger';
    }
    return res.redirect('/farmer/manage_products');
  }

  res.redirect('/farmer/manage_products');
});

// Delete product
router.get('/actions/product_action.php', isFarmer, async (req, res) => {
  const { action, id } = req.query;
  const farmerId = req.session.user_id;

  if (action === 'delete' && id) {
    try {
      await db.query('DELETE FROM farm_products WHERE id = ? AND farmer_id = ?', [id, farmerId]);
      req.session.msg = 'Product deleted successfully!';
      req.session.msg_type = 'success';
    } catch (err) {
      req.session.msg = 'Cannot delete product if it has orders.';
      req.session.msg_type = 'danger';
    }
  }
  res.redirect('/farmer/manage_products');
});

module.exports = router;
