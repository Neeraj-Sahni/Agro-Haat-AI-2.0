const express = require('express');
const router = express.Router();
const db = require('../config/db');

const isAuth = (req, res, next) => {
  if (req.session && req.session.user_id) return next();
  return res.redirect('/login');
};

// Customer Messages Page
router.get(['/customer/messages', '/customer/messages.php', '/chat.php'], isAuth, (req, res) => {
  const receiverId = req.query.receiver_id || 0;
  res.render('customer/messages', { receiver_id: receiverId, user: req.session });
});

// Fetch Contacts Action
router.get('/actions/fetch_contacts.php', isAuth, async (req, res) => {
  const userId = req.session.user_id;
  const userRole = req.session.role;
  const targetRole = (userRole === 'Farmer') ? 'Customer' : 'Farmer';

  try {
    const [rows] = await db.query(
      `SELECT u.id, u.fullname, u.image,
              (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_msg,
              (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_time
       FROM users u 
       WHERE u.role = ? AND u.id != ?`,
      [userId, userId, userId, userId, targetRole, userId]
    );
    res.json(rows);
  } catch (err) {
    console.error(err);
    res.json([]);
  }
});

// Fetch Messages Action
router.get(['/actions/fetch_messages.php', '/backend/fetch_messages.php'], isAuth, async (req, res) => {
  const userId = req.session.user_id;
  const receiverId = req.query.receiver_id;

  try {
    const [userRows] = await db.query('SELECT fullname FROM users WHERE id = ?', [receiverId]);
    const receiverName = userRows.length > 0 ? userRows[0].fullname : 'User';

    const [messages] = await db.query(
      `SELECT * FROM messages 
       WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
       ORDER BY created_at ASC`,
      [userId, receiverId, receiverId, userId]
    );

    res.json({ receiver_name: receiverName, messages });
  } catch (err) {
    console.error(err);
    res.json({ receiver_name: '', messages: [] });
  }
});

// Send Message Action
router.post(['/actions/send_message_action.php', '/backend/send_message.php', '/actions/send_message.php'], isAuth, async (req, res) => {
  const senderId = req.session.user_id;
  const { receiver_id, message } = req.body;

  if (!receiver_id || !message) {
    return res.json({ success: false, error: 'Missing fields' });
  }

  try {
    await db.query(
      'INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)',
      [senderId, receiver_id, message]
    );
    res.json({ success: true });
  } catch (err) {
    console.error(err);
    res.json({ success: false, error: err.message });
  }
});

// Farmer Support Page
router.get(['/farmer/support', '/farmer/support.php'], isAuth, async (req, res) => {
  const userId = req.session.user_id;
  try {
    const [messages] = await db.query('SELECT * FROM messages WHERE sender_id = ? AND receiver_id = 0 ORDER BY created_at DESC LIMIT 5', [userId]);
    const msg = req.session.msg;
    const msg_type = req.session.msg_type;
    delete req.session.msg;
    delete req.session.msg_type;
    res.render('farmer/support', { messages, msg, msg_type, user: req.session });
  } catch (err) {
    res.render('farmer/support', { messages: [], user: req.session });
  }
});

// Support Query Action
router.post('/actions/support_action.php', isAuth, async (req, res) => {
  const userId = req.session.user_id;
  const queryText = req.body.query_text;

  try {
    await db.query('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, 0, ?)', [userId, queryText]);
    
    // Generate real-time AI expert advice
    const axios = require('axios');
    require('dotenv').config();
    const apiKey = process.env.GROQ_API_KEY || '';

    if (apiKey) {
      try {
        const aiRes = await axios.post(
          'https://api.groq.com/openai/v1/chat/completions',
          {
            model: 'groq/compound-mini',
            messages: [{ role: 'user', content: `Act as a senior Indian agronomist expert. Answer this farmer query concisely: "${queryText}"` }],
            temperature: 0.7
          },
          { headers: { Authorization: `Bearer ${apiKey}` }, timeout: 15000 }
        );

        if (aiRes.data && aiRes.data.choices && aiRes.data.choices[0]) {
          const aiReply = '👨‍🔬 [AI Agronomist Expert Reply]:\n' + aiRes.data.choices[0].message.content;
          await db.query('INSERT INTO messages (sender_id, receiver_id, message) VALUES (0, ?, ?)', [userId, aiReply]);
        }
      } catch (e) {}
    }

    req.session.msg = 'Your query has been submitted! AI Agronomist has answered below.';
    req.session.msg_type = 'success';
  } catch (err) {
    req.session.msg = 'Error submitting query.';
    req.session.msg_type = 'danger';
  }
  res.redirect('/farmer/support');
});

module.exports = router;
