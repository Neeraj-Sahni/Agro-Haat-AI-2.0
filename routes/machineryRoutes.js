const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const db = require('../config/db');

const machStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dir = path.join(__dirname, '../public/uploads/machinery');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, 'mach_' + Date.now() + ext);
  }
});
const uploadMach = multer({ storage: machStorage });

const isFarmer = (req, res, next) => {
  if (req.session && req.session.user_id && req.session.role === 'Farmer') return next();
  return res.redirect('/login');
};

// Farmer Machinery Page
router.get(['/farmer/machinery', '/farmer/machinery.php'], isFarmer, async (req, res) => {
  const userId = req.session.user_id;

  try {
    const [other_machinery] = await db.query(
      `SELECT m.*, u.fullname as owner_name 
       FROM machinery m 
       JOIN users u ON m.owner_id = u.id 
       WHERE m.availability_status = 'Available' AND m.owner_id != ?`,
      [userId]
    );

    const [my_machinery] = await db.query(
      'SELECT * FROM machinery WHERE owner_id = ?',
      [userId]
    );

    const msg = req.session.msg;
    const msg_type = req.session.msg_type;
    delete req.session.msg;
    delete req.session.msg_type;

    res.render('farmer/machinery', { other_machinery, my_machinery, msg, msg_type, user: req.session });
  } catch (err) {
    console.error(err);
    res.render('farmer/machinery', { other_machinery: [], my_machinery: [], user: req.session });
  }
});

// Machinery Action Handler
router.post('/actions/machinery_action.php', isFarmer, uploadMach.single('machinery_image'), async (req, res) => {
  const userId = req.session.user_id;
  const action = req.body.action;

  if (action === 'add') {
    const { machinery_name, rental_price, description } = req.body;
    const img = req.file ? req.file.filename : 'default_machinery.jpg';

    try {
      await db.query(
        'INSERT INTO machinery (owner_id, machinery_name, rental_price_per_day, availability_status, description, image) VALUES (?, ?, ?, ?, ?, ?)',
        [userId, machinery_name, rental_price, 'Available', description, img]
      );
      req.session.msg = 'Machinery listed successfully!';
      req.session.msg_type = 'success';
    } catch (err) {
      req.session.msg = 'Error listing machinery: ' + err.message;
      req.session.msg_type = 'danger';
    }
  }

  if (action === 'rent') {
    const { machinery_id } = req.body;
    try {
      await db.query(
        "UPDATE machinery SET availability_status = 'Rented' WHERE id = ?",
        [machinery_id]
      );
      req.session.msg = 'Machinery rental request sent!';
      req.session.msg_type = 'success';
    } catch (err) {
      req.session.msg = 'Rental request failed.';
      req.session.msg_type = 'danger';
    }
  }

  res.redirect('/farmer/machinery');
});

// Delete machinery (GET handler)
router.get('/actions/machinery_action.php', isFarmer, async (req, res) => {
  const { action, id } = req.query;
  const userId = req.session.user_id;

  if (action === 'delete' && id) {
    try {
      await db.query('DELETE FROM machinery WHERE id = ? AND owner_id = ?', [id, userId]);
      req.session.msg = 'Machinery listing deleted!';
      req.session.msg_type = 'success';
    } catch (err) {
      req.session.msg = 'Failed to delete listing.';
      req.session.msg_type = 'danger';
    }
  }
  res.redirect('/farmer/machinery');
});

module.exports = router;
