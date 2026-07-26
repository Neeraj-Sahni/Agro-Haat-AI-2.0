<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style1.css?v=1">
    <style>
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }
        .alert-danger { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    </style>
</head>
<body class="page">
        <div class="description">
            <p>
                <center><b>Smart farming using Artificial Intelligence</b></center>
            </p>
            <p>This portal allows farmers to register and log in to access AI-based smart farming services.
            After registration, farmers can securely log in to their account and receive personalized crop recommendations, 
            weather updates, soil-based advice, pest and disease alerts, and market price information.</p>
            <p>The system uses Artificial Intelligence to analyze farmer data such as location, soil type, 
            and crop details to provide accurate and useful farming suggestions, helping farmers increase productivity and reduce losses.</p>
        </div>
        <div class="container">
            <h2>Registration</h2>
            
            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?>">
                    <?= $_SESSION['msg'] ?>
                </div>
                <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
            <?php endif; ?>

            <form action="actions/register_action.php" method="POST" onsubmit="return validateForm()">
                <input type="text" name="fullname" placeholder="Enter Full Name" required><br><br>
                <input type="email" name="email" placeholder="Enter Email" pattern="[a-zA-Z0-9._%+-]+@[a-z]+\.[a-z]{2,3}"  required><br><br>
                <input type="tel" name="phone" placeholder="Enter Phone Number (10 digits)"pattern="[0-9]{10}" maxlength="10" minlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)" title="Please enter exactly 10 digit phone number" required><br><br>
                <input type="text" name="username" placeholder="Choose Username" required><br><br>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="Farmer">Farmer</option>
                    <option value="Customer">Customer</option>
                </select><br><br>
                <input type="password" id="password" name="password" placeholder="Create Password" required><br><br>
                <input type="password"id='cpassword' placeholder="Confirm Password" required><br><br>
                <button type="submit">Register</button>
            </form>
            <div class="login">
                <p>
                <b>Already have an account?</b>
                    <a href="login.php"> Login Here</a>
                </p>
                <p>
                    <a href="login.php?forgot=1" class="text-decoration-none small text-muted">Forgot Password?</a>
                </p>
            </div>
        </div>
    <script src="js/logic.js"></script>
</body>
</html>
