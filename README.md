# 🌾 Agro-Haat AI 2.0

### AI-Powered Smart Farming & Digital Agriculture Platform

Agro-Haat AI 2.0 is an intelligent digital agriculture platform designed to help farmers make better decisions using Artificial Intelligence, weather information, crop assistance, pest and disease support, market insights, and a digital marketplace.

The platform connects **Farmers, Customers, AI Services, Market Information, and Product Management** into a single web-based ecosystem.

---

## 🚀 Live Demo

🌐 **Live Website:**  
https://agro-haat-ai.onrender.com

---

## 📌 Problem Statement

Farmers often face several challenges such as:

- Lack of timely agricultural information
- Difficulty identifying crop diseases and pests
- Uncertainty about weather conditions
- Lack of reliable market and mandi price information
- Difficulty finding suitable crops and farming practices
- Limited access to digital marketplaces
- Difficulty managing agricultural products and orders

These problems can result in reduced productivity, financial losses, and inefficient decision-making.

---

## 💡 Our Solution

Agro-Haat AI 2.0 provides a centralized platform where farmers can access intelligent agricultural assistance and digital services.

The platform combines:

- 🤖 AI-powered assistance
- 🌱 Smart farming support
- 🌦️ Weather information
- 🐛 Pest and disease assistance
- 📊 Market and mandi insights
- 🛒 Digital marketplace
- 📦 Product and order management
- 👨‍🌾 Farmer dashboard
- 👤 Customer dashboard
- 🔐 Secure authentication
- 📧 Email and OTP-based password recovery

---

# ✨ Key Features

## 🤖 AI Agricultural Assistant

The AI assistant helps users get agricultural guidance and answers farming-related questions.

It can assist with topics such as:

- Crop selection
- Farming practices
- Soil-related questions
- Crop problems
- Pest and disease-related queries
- Agricultural recommendations

---

## 🌱 Smart Crop Assistance

Farmers can receive intelligent guidance related to crop selection and farming decisions.

The system can consider agricultural information to provide useful recommendations for improving farming practices.

---

## 🌦️ Weather Assistance

The platform provides weather-related information to help farmers plan agricultural activities.

Weather information can help farmers make better decisions regarding:

- Irrigation
- Crop planning
- Spraying
- Harvesting
- Weather-sensitive farming activities

---

## 🐛 Pest & Disease Assistance

Farmers can use the platform to get assistance regarding common crop pests and diseases.

The feature helps users understand:

- Possible crop problems
- Pest-related issues
- Disease symptoms
- Possible preventive measures
- Suggested agricultural actions

---

## 📊 Market & Mandi Insights

Agro-Haat provides market-related information to help farmers understand agricultural pricing and market conditions.

This can help farmers make better decisions regarding:

- Selling crops
- Market selection
- Price comparison
- Market trends

---

## 🛒 Digital Marketplace

The platform provides a marketplace where agricultural products can be listed and purchased.

### Farmers can:

- Add products
- Manage product information
- Manage available stock
- View orders

### Customers can:

- Browse products
- View product details
- Add products to cart
- Place orders
- Track their purchases

---

## 👨‍🌾 Farmer Dashboard

Farmers have access to a dedicated dashboard for managing their agricultural activities.

The dashboard provides access to:

- Products
- Orders
- AI assistance
- Agricultural information
- Market information
- Farming-related services

---

## 👤 Customer Dashboard

Customers can use a dedicated dashboard to manage their marketplace activities.

Features include:

- Product browsing
- Cart management
- Orders
- Profile management
- Marketplace access

---

## 🔐 Authentication & Security

The application provides authentication functionality for users.

Security-related features include:

- User registration
- User login
- Password hashing
- Session-based authentication
- Role-based access
- Password recovery
- OTP-based password reset

Passwords are handled using secure hashing rather than storing plain-text passwords.

---

## 📧 Email & OTP Password Recovery

The application includes a password recovery system using OTP verification.

Basic flow:

```text
User requests password reset
        ↓
OTP is generated
        ↓
OTP is sent through email
        ↓
User enters OTP
        ↓
OTP is verified
        ↓
User creates a new password
```

---

# 🏗️ System Architecture

```text
                    ┌───────────────────────┐
                    │       User            │
                    │ Farmer / Customer     │
                    └───────────┬───────────┘
                                │
                                ▼
                    ┌───────────────────────┐
                    │   Agro-Haat Web UI    │
                    │   HTML / CSS / EJS    │
                    └───────────┬───────────┘
                                │
                                ▼
                    ┌───────────────────────┐
                    │ Node.js + Express     │
                    │      Backend          │
                    └───────────┬───────────┘
                                │
              ┌─────────────────┼─────────────────┐
              │                 │                 │
              ▼                 ▼                 ▼
      ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
      │ MySQL        │  │ AI Services  │  │ External     │
      │ Database     │  │              │  │ APIs         │
      └──────────────┘  └──────────────┘  └──────────────┘
              │                 │                 │
              └─────────────────┼─────────────────┘
                                ▼
                    ┌───────────────────────┐
                    │    Agro-Haat AI       │
                    │     Services          │
                    └───────────────────────┘
```

---

# 🧑‍💻 Technology Stack

## Frontend

- HTML5
- CSS3
- JavaScript
- EJS
- Bootstrap

## Backend

- Node.js
- Express.js

## Database

- MySQL
- MySQL2

## Authentication & Security

- Express Session
- bcryptjs
- Environment Variables

## AI & APIs

- AI-powered services
- External API integrations
- Weather API

## Email

- Nodemailer
- Gmail SMTP

## Deployment

- GitHub
- Render
- Aiven Cloud MySQL

---

# ☁️ Deployment Architecture

The production application follows this architecture:

```text
                GitHub Repository
                       │
                       ▼
              ┌─────────────────┐
              │     Render      │
              │ Node.js Server  │
              └────────┬────────┘
                       │
                       ▼
              ┌─────────────────┐
              │ Aiven Cloud     │
              │     MySQL       │
              └─────────────────┘
```

### Deployment Flow

```text
Developer
    │
    ▼
GitHub
    │
    ▼
Render
    │
    ▼
Node.js + Express Application
    │
    ▼
Aiven Cloud MySQL
```

---

# 🗄️ Database

The application uses a MySQL relational database for storing application data.

The database manages information related to areas such as:

- Users
- Farmers
- Customers
- Products
- Orders
- Authentication
- Agricultural information

The production database is hosted on **Aiven Cloud MySQL**.

---

# 📁 Project Structure

```text
Agro-Haat-AI-2.0/
│
├── config/
│   └── db.js
│
├── public/
│   ├── css/
│   ├── js/
│   └── images/
│
├── routes/
│   ├── auth routes
│   ├── farmer routes
│   ├── customer routes
│   └── other application routes
│
├── views/
│   ├── authentication/
│   ├── farmer/
│   ├── customer/
│   └── other views
│
├── server.js
├── package.json
├── package-lock.json
├── .gitignore
├── .env
└── README.md
```

> **Note:** `.env` contains private configuration and secrets and should never be committed to GitHub.

---

# ⚙️ Environment Variables

The application uses environment variables for configuration.

Example:

```env
PORT=3000

SESSION_SECRET=your_session_secret

DB_HOST=your_database_host
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

AI_PROVIDER=your_ai_provider

GITHUB_TOKEN=your_token
GITHUB_MODEL=your_model

EMAIL_SERVICE=gmail
EMAIL_USER=your_email
EMAIL_PASS=your_email_password

WEATHER_API_KEY=your_weather_api_key
```

Never expose API keys, database passwords, email passwords, or other secrets publicly.

---

# 💻 Local Installation

## 1. Clone the Repository

```bash
git clone https://github.com/Neeraj-Sahni/Agro-Haat-AI-2.0.git
```

Move into the project directory:

```bash
cd Agro-Haat-AI-2.0
```

---

## 2. Install Dependencies

```bash
npm install
```

---

## 3. Configure Environment Variables

Create a `.env` file in the project root.

Add the required database, authentication, AI, email, and API configuration.

---

## 4. Start the Application

```bash
node server.js
```

The application will run locally on:

```text
http://localhost:3000
```

---

# 🔄 Authentication Flow

```text
User
 │
 ▼
Registration / Login
 │
 ▼
Authentication
 │
 ▼
Session Created
 │
 ▼
Role-Based Dashboard
 │
 ├── Farmer Dashboard
 │
 └── Customer Dashboard
```

---

# 🤖 AI Workflow

```text
User Query
     │
     ▼
Agro-Haat AI Interface
     │
     ▼
Backend API
     │
     ▼
AI Service
     │
     ▼
AI Response
     │
     ▼
User
```

---

# 🎯 Project Objectives

The major objectives of Agro-Haat AI 2.0 are:

1. Provide accessible agricultural assistance.
2. Help farmers make better farming decisions.
3. Provide useful weather and market information.
4. Support pest and disease-related decision making.
5. Create a digital marketplace for agricultural products.
6. Connect farmers and customers through a single platform.
7. Use AI to improve agricultural information accessibility.
8. Build a scalable cloud-based agriculture platform.

---

# 🌍 Sustainable Development Goals

Agro-Haat AI 2.0 supports the following UN Sustainable Development Goals:

### 🌾 SDG 2 — Zero Hunger

The platform supports better agricultural decision-making and contributes toward improved food production.

### 🌎 SDG 13 — Climate Action

Weather awareness and climate-related agricultural information can help farmers make more informed decisions under changing environmental conditions.

---

# 🔮 Future Scope

Future versions of Agro-Haat AI can include:

- AI-based crop disease image detection
- Advanced crop recommendation models
- IoT-based soil monitoring
- Smart irrigation recommendations
- Satellite-based crop monitoring
- Real-time mandi price integration
- Voice-based agricultural assistant
- Multilingual AI assistance
- Personalized farmer recommendations
- AI-based yield prediction
- Advanced analytics dashboard
- Mobile application

---

# 📈 Project Highlights

- 🌱 AI-powered agriculture platform
- 🤖 Intelligent agricultural assistance
- 🐛 Pest and disease support
- 🌦️ Weather information
- 📊 Market insights
- 🛒 Digital marketplace
- 👨‍🌾 Farmer management
- 👤 Customer management
- 🔐 Authentication and security
- 📧 OTP-based password recovery
- ☁️ Cloud database deployment
- 🚀 Live production deployment

---

# 🔒 Security Best Practices

For production usage:

- Never commit `.env` files.
- Never expose API keys.
- Never expose database passwords.
- Use strong session secrets.
- Use secure password hashing.
- Use HTTPS in production.
- Validate user input.
- Restrict database permissions.
- Keep dependencies updated.

---

# 👨‍💻 Developer

### Neeraj Sahni
### Pranshu Gupta

**B.Tech — Computer Science**  
Noida Institute of Engineering and Technology (NIET), Greater Noida

GitHub:  
- https://github.com/Neeraj-Sahni
- https://github.com/pranshu2810



---

# 📜 License

This project is developed for educational, academic, and demonstration purposes.

---

# ⭐ Support

If you find this project useful, consider giving the repository a ⭐ on GitHub.

---

## 🌾 Agro-Haat AI 2.0

### *Technology for Smarter Farming, Better Decisions, and a Connected Agricultural Future.*

```text
AI + Agriculture + Marketplace + Cloud
                 ↓
          Agro-Haat AI 2.0
```

