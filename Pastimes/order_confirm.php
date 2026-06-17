<?php
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$address = $_SESSION['delivery_address'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$order_placed = false;
$order_ids = [];
$total_amount = 0;

// Create order in database
if (!empty($_SESSION['cart']) && !empty($address)) {
    // Generate a unique reference number
    $reference_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Get cart items with quantities
    $cart_items = [];
    foreach ($_SESSION['cart'] as $index => $product_id) {
        $quantity = isset($_SESSION['cart_quantities'][$index]) ? $_SESSION['cart_quantities'][$index] : 1;
        $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tblclothes WHERE ClothesID=$product_id"));
        if ($product) {
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['Name'],
                'price' => $product['Price'],
                'quantity' => $quantity,
                'total' => $product['Price'] * $quantity
            ];
        }
    }
    
    // Calculate total
    $total_amount = array_sum(array_column($cart_items, 'total'));
    
    // Insert each item into tblaorder
    foreach ($cart_items as $item) {
        $query = "INSERT INTO tblaorder (UserID, ClothesID, Quantity, TotalAmount, DeliveryAddress, Phone, Status, OrderDate) 
                  VALUES ('$user_id', '{$item['id']}', '{$item['quantity']}', '{$item['total']}', '$address', '$phone', 'pending', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $order_ids[] = mysqli_insert_id($conn);
            
            // DECREMENT STOCK AFTER CHECKOUT
            $new_stock = $item['stock'] - $item['quantity'];
            mysqli_query($conn, "UPDATE tblclothes SET Stock = $new_stock WHERE ClothesID = {$item['id']}");
        }
    }
    
    // CLEAR CART AFTER CHECKOUT
    $_SESSION['cart'] = [];
    $_SESSION['cart_quantities'] = [];
    unset($_SESSION['delivery_address']);
    unset($_SESSION['phone']);
    
    $order_placed = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmed - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .confirmation-container {
            max-width: 700px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .success-icon {
            font-size: 5rem;
            text-align: center;
            margin-bottom: 20px;
        }
        .confirmation-card {
            background: white;
            border: 1px solid #e5e0d5;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
        }
        .order-details {
            text-align: left;
            background: #faf9f7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .order-details p {
            margin: 8px 0;
        }
        .reference-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #e67e22;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: #e67e22;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
        }
        .btn-secondary {
            background: #2c3e50;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="confirmation-container">
    <?php if ($order_placed && !empty($order_ids)): ?>
        <div class="success-icon">✅</div>
        <div class="confirmation-card">
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been received.</p>
            
            <div class="order-details">
                <p><strong>📋 Reference Number:</strong> <span class="reference-number"><?php echo $reference_number; ?></span></p>
                <p><strong>🆔 Order Number(s):</strong> #<?php echo implode(', #', $order_ids); ?></p>
                <p><strong>💰 Total Amount:</strong> R<?php echo number_format($total_amount, 2); ?></p>
                <p><strong>📦 Delivery Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                <p><strong>📞 Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                <p><strong>📅 Order Date:</strong> <?php echo date('F j, Y \a\t g:i a'); ?></p>
                <p><strong>📊 Status:</strong> <span style="color: #e67e22;">Pending</span></p>
            </div>
            
            <p>A confirmation email has been sent to <strong><?php echo $_SESSION['email']; ?></strong></p>
            <p>Your order will be processed within 2-3 business days.</p>
            
            <div class="btn-group">
                <a href="shop.php" class="btn-primary">Continue Shopping →</a>
                <a href="order_history.php" class="btn-secondary">View Order History</a>
            </div>
        </div>
    <?php else: ?>
        <div class="confirmation-card">
            <div style="font-size: 4rem;">🛒</div>
            <h1>No Order Found</h1>
            <p>Your cart is empty or something went wrong.</p>
            <a href="shop.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">Start Shopping →</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>