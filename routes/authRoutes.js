const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const db = require('../config/db');

// Multer storage for profile pictures
const profileStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dir = path.join(__dirname, '../public/uploads/profile_pictures');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, 'profile_' + Date.now() + ext);
  }
});
const uploadProfile = multer({ storage: profileStorage });

// Helper to check login
const isAuth = (req, res, next) => {
  if (req.session && req.session.user_id) {
    return next();
  }
  return res.redirect('/login');
};

// Login Page
router.get(['/login', '/login.php'], (req, res) => {
  const msg = req.session.msg;
  const msg_type = req.session.msg_type;
  delete req.session.msg;
  delete req.session.msg_type;
  res.render('login', { msg, msg_type, user: req.session });
});

// Register Page
router.get(['/register', '/register.php'], (req, res) => {
  const msg = req.session.msg;
  const msg_type = req.session.msg_type;
  delete req.session.msg;
  delete req.session.msg_type;
  res.render('register', { msg, msg_type, user: req.session });
});

// Login Action
router.post(['/actions/login_action.php', '/backend/login.php', '/login'], async (req, res) => {
  const { email, password } = req.body;
  try {
    const [rows] = await db.query('SELECT * FROM users WHERE email = ?', [email]);
    if (rows.length === 0) {
      req.session.msg = 'User not found.';
      req.session.msg_type = 'danger';
      return res.redirect('/login');
    }
    const user = rows[0];
    const isMatch = await bcrypt.compare(password, user.password);
    if (!isMatch) {
      req.session.msg = 'Incorrect password.';
      req.session.msg_type = 'danger';
      return res.redirect('/login');
    }
    req.session.user_id = user.id;
    req.session.fullname = user.fullname;
    req.session.role = user.role;
    req.session.image = user.image || 'default.jpg';
    req.session.email = user.email;

    if (user.role === 'Farmer') {
      return res.redirect('/farmer/dashboard');
    } else {
      return res.redirect('/customer/dashboard');
    }
  } catch (err) {
    console.error(err);
    req.session.msg = 'Database error: ' + err.message;
    req.session.msg_type = 'danger';
    return res.redirect('/login');
  }
});

// Register Action
router.post(['/actions/register_action.php', '/backend/register.php', '/register'], async (req, res) => {
  const { fullname, email, phone, username, role, password } = req.body;

  if (!/^[0-9]{10}$/.test(phone)) {
    req.session.msg = 'Phone number must be exactly 10 digits.';
    req.session.msg_type = 'danger';
    return res.redirect('/register');
  }

  if (!/@gmail\.com$/.test(email)) {
    req.session.msg = 'Only @gmail.com emails are allowed.';
    req.session.msg_type = 'danger';
    return res.redirect('/register');
  }

  try {
    const [existing] = await db.query('SELECT id FROM users WHERE email = ?', [email]);
    if (existing.length > 0) {
      req.session.msg = 'Email already registered.';
      req.session.msg_type = 'danger';
      return res.redirect('/register');
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    await db.query(
      'INSERT INTO users (fullname, email, phone, username, role, password, image) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [fullname, email, phone, username, role, hashedPassword, 'default.jpg']
    );

    req.session.msg = 'Registration successful! Please login.';
    req.session.msg_type = 'success';
    return res.redirect('/login');
  } catch (err) {
    console.error(err);
    req.session.msg = 'Registration failed: ' + err.message;
    req.session.msg_type = 'danger';
    return res.redirect('/register');
  }
});

// Logout
router.get(['/actions/logout_action.php', '/backend/logout.php', '/logout'], (req, res) => {
  req.session.destroy(() => {
    res.redirect('/login');
  });
});

// Change Password Page
router.get(['/change_password', '/change_password.php'], isAuth, (req, res) => {
  const error = req.query.error;
  const success = req.query.success;
  res.render('change_password', { error, success, user: req.session });
});

// Change Password Action
router.post(['/backend/change_password_action.php', '/actions/change_password_action.php'], isAuth, async (req, res) => {
  const { old_password, new_password } = req.body;
  const userId = req.session.user_id;

  try {
    const [rows] = await db.query('SELECT password FROM users WHERE id = ?', [userId]);
    if (rows.length === 0) {
      return res.redirect('/change_password?error=User+not+found');
    }

    const match = await bcrypt.compare(old_password, rows[0].password);
    if (!match) {
      return res.redirect('/change_password?error=Incorrect+old+password');
    }

    const newHashed = await bcrypt.hash(new_password, 10);
    await db.query('UPDATE users SET password = ? WHERE id = ?', [newHashed, userId]);

    return res.redirect('/change_password?success=Password+updated+successfully');
  } catch (err) {
    return res.redirect('/change_password?error=Failed+to+update');
  }
});

const nodemailer = require('nodemailer');

async function sendOtpEmail(toEmail, otp) {
  require('dotenv').config();
  const emailUser = process.env.EMAIL_USER;
  const emailPass = process.env.EMAIL_PASS;

  if (!emailUser || !emailPass) {
    return { sent: false, reason: 'EMAIL_USER / EMAIL_PASS not set in .env' };
  }

  try {
    const transporter = nodemailer.createTransport({
      service: process.env.EMAIL_SERVICE || 'gmail',
      auth: {
        user: emailUser,
        pass: emailPass
      }
    });

    await transporter.sendMail({
      from: `"Agro-Haat AI" <${emailUser}>`,
      to: toEmail,
      subject: '🔑 Agro-Haat AI: Your Password Reset Verification OTP',
      html: `
        <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f7f6; border-radius: 10px; color: #333;">
          <h2 style="color: #2e7d32;">🌱 Agro-Haat AI Smart Farming Portal</h2>
          <p>Hello,</p>
          <p>You requested a password reset for your Agro-Haat account.</p>
          <div style="background-color: #ffffff; padding: 15px; border-left: 5px solid #10b981; margin: 20px 0; border-radius: 6px;">
            <p style="margin: 0; font-size: 14px; color: #666;">Your 6-Digit Verification OTP Code is:</p>
            <h1 style="margin: 5px 0; color: #2e7d32; letter-spacing: 4px;">${otp}</h1>
          </div>
          <p>This OTP code is valid for 10 minutes. Please do not share this code with anyone.</p>
          <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;">
          <p style="color: #888; font-size: 12px;">If you did not request a password reset, please ignore this email.</p>
        </div>
      `
    });

    return { sent: true };
  } catch (err) {
    console.error('Nodemailer Email Error:', err.message);
    return { sent: false, reason: err.message };
  }
}

// Forgot Password / OTP Request & Reset Action
router.post(['/actions/forgot_password_action.php', '/backend/forgot_password_action.php'], async (req, res) => {
  const { action, email, otp, new_password } = req.body;

  // Step 2: Reset password with OTP
  if (action === 'reset' || (otp && new_password)) {
    if (!req.session.reset_otp || req.session.reset_otp.email !== email) {
      return res.json({ success: false, message: 'Invalid reset session. Please request OTP again.' });
    }

    if (Date.now() > req.session.reset_otp.expires) {
      return res.json({ success: false, message: 'OTP expired. Please request a new OTP.' });
    }

    if (String(req.session.reset_otp.otp).trim() !== String(otp).trim()) {
      return res.json({ success: false, message: 'Incorrect OTP code. Please check and try again.' });
    }

    try {
      const hashed = await bcrypt.hash(new_password, 10);
      await db.query('UPDATE users SET password = ? WHERE email = ?', [hashed, email]);
      delete req.session.reset_otp;
      return res.json({ success: true, message: 'Password reset successfully! You can now login with your new password.' });
    } catch (err) {
      return res.json({ success: false, message: 'Database error: ' + err.message });
    }
  }

  // Step 1: Generate & send OTP
  try {
    const [rows] = await db.query('SELECT id, fullname FROM users WHERE email = ?', [email]);
    if (rows.length === 0) {
      return res.json({ success: false, message: 'Email address not found in system.' });
    }

    const generatedOtp = Math.floor(100000 + Math.random() * 900000).toString();
    req.session.reset_otp = {
      email,
      otp: generatedOtp,
      expires: Date.now() + (10 * 60 * 1000) // 10 mins
    };

    // Attempt real email dispatch via Nodemailer
    const mailResult = await sendOtpEmail(email, generatedOtp);

    if (mailResult.sent) {
      return res.json({
        success: true,
        email_sent: true,
        message: `OTP sent successfully to ${email}! Please check your email inbox.`
      });
    } else {
      // Fallback: If SMTP is not yet configured, show OTP code on screen for instant testing
      return res.json({
        success: true,
        otp: generatedOtp,
        email_sent: false,
        message: `OTP generated! (Note: Configure EMAIL_USER & EMAIL_PASS in .env to deliver to Inbox).\nYour Verification OTP Code is: ${generatedOtp}`
      });
    }
  } catch (err) {
    return res.json({ success: false, message: 'Error generating OTP.' });
  }
});

// Profile Settings Page
router.get(['/profile', '/profile.php'], isAuth, async (req, res) => {
  const userId = req.session.user_id;
  try {
    const [rows] = await db.query('SELECT * FROM users WHERE id = ?', [userId]);
    const user = rows[0] || {};
    const success = req.session.success;
    const error = req.session.error;
    delete req.session.success;
    delete req.session.error;

    res.render('profile', { user: { ...user, user_id: user.id }, success, error });
  } catch (err) {
    res.redirect('/');
  }
});

// Profile Update Action
router.post('/profile', isAuth, async (req, res) => {
  const userId = req.session.user_id;
  const { location, speciality, bio } = req.body;

  try {
    await db.query(
      'UPDATE users SET location = ?, speciality = ?, bio = ? WHERE id = ?',
      [location || '', speciality || '', bio || '', userId]
    );
    req.session.success = 'Profile updated successfully!';
  } catch (err) {
    req.session.error = 'Update failed!';
  }
  res.redirect('/profile');
});

// Upload Profile Image Action
router.post(['/backend/upload_image.php', '/actions/upload_image_action.php'], isAuth, uploadProfile.single('profile_image'), async (req, res) => {
  const userId = req.session.user_id;
  if (!req.file) {
    req.session.error = 'No file uploaded.';
    return res.redirect('/profile');
  }

  const filename = req.file.filename;
  try {
    await db.query('UPDATE users SET image = ? WHERE id = ?', [filename, userId]);
    req.session.image = filename;
    req.session.success = 'Profile photo updated successfully!';
  } catch (err) {
    req.session.error = 'Failed to update image in database.';
  }
  res.redirect('/profile');
});

module.exports = router;
