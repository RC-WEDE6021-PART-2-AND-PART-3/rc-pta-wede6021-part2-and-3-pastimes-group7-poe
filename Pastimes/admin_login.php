<?php
session_start();
include 'DBConn.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];  // Changed from username to email
    $password = md5($_POST['password']);
    
    // Check against email, not username
    $result = mysqli_query($conn, "SELECT * FROM tblAdmin WHERE Email='$email' AND Password='$password'");
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['admin_id'] = $row['AdminID'];
        $_SESSION['admin_username'] = $row['Username'];
        $_SESSION['admin_email'] = $row['Email'];
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid admin credentials! Use admin email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - ClothingStore</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Admin Login</h2>
        <p>Please enter your admin email and password</p>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="email" placeholder="admin@clothingstore.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Admin Login →</button>
        </form>
        <p style="margin-top: 20px; text-align: center;">Default: admin@clothingstore.com / admin123</p>
    </div>
</div>
</body>
</html>