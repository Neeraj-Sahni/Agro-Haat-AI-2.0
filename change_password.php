<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/change_password.css">
</head>
<body>
    <div class="password-box">
        <h2>Change Password</h2>
        <?php if (isset($_GET['error'])){?>
            <p class="error"><?=$_GET['error'] ?></p>
        <?php } ?>
        <?php if (isset($_GET['success'])){ ?>
            <p class="success"><?= $_GET['success'] ?></p>
        <?php } ?>

        <form action="backend/change_password_action.php" method="POST" onsubmit="return validatePassword()">
            <input type="password" name="old_password" placeholder="Enter your old Password" required>
            <input type="password" id="new_password" name="new_password" placeholder="Enter your New Password" required>
            <input type="password" id="confirm_password" placeholder="confirm Password" required>
            <button type="submit">Update Password</button>
            <a href="index.php" class="btn">Back to Home</a>
        </form>
    </div>
    <script src="js/change_password.js"></script>
</body>
</html>