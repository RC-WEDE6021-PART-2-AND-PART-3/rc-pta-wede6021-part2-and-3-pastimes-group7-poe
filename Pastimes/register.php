<?php
session_start();
include 'DBConn.php';

if (isset($_SESSION['user_id'])) {
    header("Location: shop.php");
    exit();
}

$error = '';
$success = '';
$sticky_name = '';
$sticky_email = '';
$sticky_username = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    $sticky_name = $name;
    $sticky_email = $email;
    $sticky_username = $username;
    
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters.";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM tblUser WHERE Username='$username' OR Email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username or email already exists.";
        } else {
            $hashed = md5($password);
            $insert = "INSERT INTO tblUser (Username, Email, Password, Status) 
                       VALUES ('$username', '$email', '$hashed', 'pending')";
            if (mysqli_query($conn, $insert)) {
                $success = "Account created! Please wait for admin verification.";
                $sticky_name = $sticky_email = $sticky_username = '';
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">Pastimes<span>.</span></div>
        <div class="auth-tagline">Start your journey with Pastimes today</div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?> <a href="login.php">Login here</a></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="name" placeholder="FULL NAME" value="<?php echo htmlspecialchars($sticky_name); ?>" required>
            <input type="email" name="email" placeholder="EMAIL ADDRESS" value="<?php echo htmlspecialchars($sticky_email); ?>" required>
            <input type="text" name="username" placeholder="USERNAME" value="<?php echo htmlspecialchars($sticky_username); ?>" required>
            <input type="password" name="password" placeholder="PASSWORD" required>
            <input type="password" name="confirm_password" placeholder="CONFIRM PASSWORD" required>
            <button type="submit" class="btn-primary">Create Account →</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php">Login to Pastimes →</a>
        </div>
    </div>
</div>
</body>
</html>