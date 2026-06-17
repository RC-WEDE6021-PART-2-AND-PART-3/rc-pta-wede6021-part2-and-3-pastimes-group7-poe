<?php
session_start();
include 'DBConn.php';

if (isset($_SESSION['user_id'])) {
    header("Location: shop.php");
    exit();
}

$error = '';
$sticky_email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $sticky_email = $email;
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $result = mysqli_query($conn, "SELECT * FROM tblUser WHERE Email='$email'");
        
        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['Password'] == md5($password)) {
                if ($row['Status'] == 'verified') {
                    $_SESSION['user_id'] = $row['UserID'];
                    $_SESSION['username'] = $row['Username'];
                    $_SESSION['email'] = $row['Email'];
                    header("Location: shop.php");
                    exit();
                } else {
                    $error = "Account pending verification.";
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">Pastimes<span>.</span></div>
        <div class="auth-tagline">CURATING YOUR MOMENTS OF LEISURE</div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="EMAIL ADDRESS" 
                   value="<?php echo htmlspecialchars($sticky_email); ?>" required>
            <input type="password" name="password" placeholder="PASSWORD" required>
            <div class="forgot-password">
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit" class="btn-primary">Sign In →</button>
        </form>
        
        <div class="auth-footer">
            New to the manor? <a href="register.php">Register</a>
        </div>
    </div>
</div>
</body>
</html>