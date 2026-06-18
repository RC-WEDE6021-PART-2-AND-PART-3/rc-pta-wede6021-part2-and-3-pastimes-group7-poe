<?php
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    if (empty($address) || empty($phone)) {
        $error = "Please fill all fields.";
    } else {
        $_SESSION['delivery_address'] = $address;
        $_SESSION['phone'] = $phone;
        header("Location: order_confirm.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delivery - ClothingStore</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php $active = 'delivery'; include 'navbar.php'; ?>
<div class="page-wrapper">
    <h1>Delivery Details</h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <textarea name="address" placeholder="Enter your delivery address" required></textarea>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <button type="submit" class="btn btn-primary">Continue to Payment</button>
    </form>
</div>
</body>
</html>