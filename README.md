# 🌾 Agro-Haat AI 2.0

> An AI-powered smart agriculture platform designed to connect farmers, customers, agricultural information, and digital marketplace services in one intelligent web application.

[![Live Demo](https://img.shields.io/badge/Live-Demo-success)](https://agro-haat-ai.onrender.com)
[![Node.js](https://img.shields.io/badge/Node.js-24.x-green)](https://nodejs.org/)
[![Express.js](https://img.shields.io/badge/Express.js-Backend-lightgrey)](https://expressjs.com/)
[![MySQL](https://img.shields.io/badge/Database-MySQL-blue)](https://www.mysql.com/)
[![Render](https://img.shields.io/badge/Deployed%20on-Render-purple)](https://render.com/)

---

## 📌 About the Project

**Agro-Haat AI 2.0** is a smart agriculture and digital marketplace platform developed to help farmers access useful agricultural information, AI-powered assistance, market insights, and digital commerce services through a single web application.

The platform provides separate experiences for **farmers and customers**, allowing farmers to manage agricultural products and access intelligent farming assistance while customers can browse products, interact with the marketplace, and manage their orders.

The project combines **Artificial Intelligence, web technologies, cloud database infrastructure, authentication, marketplace functionality, and email services** into one integrated platform.

---

## 🎯 Problem Statement

Farmers often face difficulties in accessing reliable agricultural information, understanding crop-related problems, finding market opportunities, and connecting directly with customers.

Some common challenges include:

- Limited access to intelligent agricultural assistance
- Difficulty in finding useful farming information
- Lack of convenient digital marketplace access
- Difficulty connecting farmers with customers
- Managing agricultural products and orders manually
- Limited access to technology-driven farming support
- Difficulty accessing different agricultural services from one platform

---

## 💡 Our Solution

Agro-Haat AI 2.0 provides a unified digital platform that combines:

- AI-powered agricultural assistance
- Farming-related information
- Weather-related support
- Pest and disease assistance
- Market and mandi insights
- Farmer marketplace
- Product management
- Customer browsing and ordering
- Order management
- User authentication
- Email and OTP-based functionality

The goal is to make agricultural technology more accessible and useful for farmers while creating a digital marketplace connecting farmers and customers.

---

# ✨ Key Features

## 🤖 AI-Powered Assistance

The platform provides AI-based assistance for agriculture-related queries and recommendations.

Users can interact with AI services to receive useful information related to farming and agricultural decision-making.

---

## 🌱 Smart Farming Support

Agro-Haat provides farmers with technology-assisted farming support, helping them access information that can assist in better agricultural decision-making.

---

## 🌦️ Weather Information

Weather-related information can help farmers understand environmental conditions and make better decisions regarding farming activities.

---

## 🐛 Pest & Disease Assistance

The platform provides assistance related to crop pests and diseases, helping farmers understand possible agricultural problems and potential solutions.

---

## 💰 Market & Mandi Insights

Agro-Haat provides market-related information to help farmers understand agricultural product pricing and make better selling decisions.

---

## 🛒 Digital Marketplace

Farmers can list agricultural products on the platform while customers can browse available products.

The marketplace provides a digital connection between farmers and customers.

---

## 📦 Product & Order Management

The platform supports product and order management functionality for marketplace operations.

Farmers can manage their products, while customers can interact with available products and manage their orders.

---

## 👨‍🌾 Farmer Dashboard

Farmers have access to dedicated functionality for:

- Managing products
- Viewing orders
- Accessing agricultural assistance
- Managing account information
- Using marketplace services

---

## 👤 Customer Dashboard

Customers can:

- Browse agricultural products
- View product information
- Manage their account
- Place and manage orders
- Interact with marketplace services

---

## 🔐 Authentication & Security

The application provides authentication functionality using:

- User registration
- Login
- Password hashing
- Session-based authentication
- Password recovery functionality

Passwords are securely handled using **bcryptjs**.

---

## 📧 Email & OTP Functionality

The application uses **Nodemailer** for email-related functionality such as password recovery and OTP-based processes.

---

# 🏗️ System Architecture

```text
                         ┌──────────────────────┐
                         │        Users         │
                         │ Farmer / Customer    │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │    EJS Frontend      │
                         │    HTML / CSS / JS   │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │   Node.js + Express  │
                         │      Backend         │
                         └──────────┬───────────┘
                                    │
              ┌─────────────────────┼─────────────────────┐
              │                     │                     │
              ▼                     ▼                     ▼
      ┌───────────────┐     ┌───────────────┐     ┌───────────────┐
      │ AI Services   │     │ Authentication│     │  Marketplace  │
      │               │     │ & Sessions    │     │ & Orders      │
      └───────┬───────┘     └───────┬───────┘     └───────┬───────┘
              │                      │                     │
              └──────────────────────┼─────────────────────┘
                                     │
                                     ▼
                           ┌───────────────────┐
                           │   Aiven MySQL     │
                           │   Cloud Database  │
                           └───────────────────┘

                                     │
                                     ▼
                           ┌───────────────────┐
                           │      Render       │
                           │ Cloud Deployment  │
                           └───────────────────┘


Deployment Stack
Source Code: GitHub
Application Hosting: Render
Database Hosting: Aiven MySQL
Backend: Node.js + Express.js

## 🌐 Live Demo

The deployed application is available at:

## 👉 https://agro-haat-ai.onrender.com

You can visit the live application to explore the platform.

## 🔄 Deployment Workflow

The project follows a Git-based deployment workflow:

Developer
    │
    ▼
Local Development
    │
    ▼
Git Commit
    │
    ▼
GitHub
    │
    ▼
Render
    │
    ▼
Production Application
    │
    ▼
Aiven MySQL Database

When new code is pushed to the main GitHub branch, Render can automatically build and deploy the updated application.

## 🗄️ Database

The application uses MySQL as its relational database.

The production database is hosted on Aiven Cloud.

The database contains tables supporting different application modules, including areas such as:

Users
Products
Farm Products
Orders
Order Items
Messages
Machinery
Subscription Plans
Customer Subscriptions
AI Recommendations

The application connects to the database using the mysql2 Node.js driver.

## 🔐 Authentication Flow

User
  │
  ▼
Registration / Login
  │
  ▼
Express Authentication Routes
  │
  ▼
Password Verification
  │
  ▼
Session Creation
  │
  ▼
Authenticated Dashboard

Passwords are handled using secure hashing through bcryptjs.

## 🤖 AI Workflow

User Query
    │
    ▼
Express Backend
    │
    ▼
AI Service
    │
    ▼
GitHub Model
    │
    ▼
AI Response
    │
    ▼
User Interface

The AI layer is designed to provide intelligent assistance for agriculture-related use cases.

## 📈 Future Scope

The platform can be further enhanced with:

Advanced crop recommendation systems
Real-time mandi price integration
Multilingual agricultural assistance
Voice-based AI assistant
Mobile application
IoT-based soil monitoring
Real-time crop disease detection
Satellite-based agricultural insights
Personalized farmer recommendations
Advanced analytics dashboards
Digital payment integration
Improved logistics and delivery tracking

## 🌍 Sustainable Development Goals

Agro-Haat AI 2.0 supports technology-driven agricultural development and can contribute toward:

SDG 2 — Zero Hunger

Supporting better agricultural decision-making and improving access to agricultural resources and markets.

SDG 13 — Climate Action

Using technology and information to support more informed and sustainable agricultural practices.

## 🎓 Project Highlights

AI-powered agriculture platform
Farmer-focused digital services
Integrated agricultural marketplace
Cloud-hosted MySQL database
Cloud deployment using Render
GitHub-based development workflow
Separate farmer and customer experiences
Authentication and session management
Email-based password recovery
Modular Node.js and Express.js backend

## 🔒 Security Best Practices

The project follows basic application security practices including:

Password hashing using bcryptjs
Environment variables for sensitive configuration
Session-based authentication
Separation of configuration from source code
Secure cloud database connection
No sensitive credentials stored in the README
