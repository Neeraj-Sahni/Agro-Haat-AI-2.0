<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="public/css/style.css?v=1">
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
        <center><h1>WELCOME</h1></center>
        <h1> AI Based Smart Farming Info Portal</h1>
        <div class="farmepage">
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
                <div class="box">
                    <h2><b>Login</b></h2>
                    
                    <?php if (isset($_SESSION['msg'])): ?>
                        <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?>">
                            <?= $_SESSION['msg'] ?>
                        </div>
                        <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
                    <?php endif; ?>

                    <form action="actions/login_action.php" method="POST">
                        <label><b>Email :</b></label>
                        <input type="email" name="email" placeholder="Enter Email" required><br><br>
                        <label><b>Password :</b></label>
                        <input type="password" name="password" placeholder="Enter Password" required><br><br>
                        <button type="submit">Login</button>
                    </form>
                    <p style="color: aliceblue;">
                        New User? <a href="register.php">Register Here</a>
                    </p>
                    <a href="#" id="showForgot">Forgot Password?</a>
                    <div id="forgotBox" class="modal">
                        <div class="modal-box">
                            <span class="close">❌</span>
                            <h4>Reset Password</h4>
                            <form id="forgotForm">
                                <input type="email" name="email" placeholder="Enter your email" required>
                                <button type="submit">Send OTP</button>
                            </form>

                            <div id="resetResult"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="public/js/logic.js?v=2S"></script>
    <script>
        // Auto-open modal if redirected from registration "Forgot Password" link
        if (window.location.search.includes('forgot=1')) {
            // Wait for logic.js to load the showBox function/modal
            setTimeout(() => {
                if (typeof openModal === 'function') openModal();
            }, 300);
        }
    </script>
</body>
</html>
