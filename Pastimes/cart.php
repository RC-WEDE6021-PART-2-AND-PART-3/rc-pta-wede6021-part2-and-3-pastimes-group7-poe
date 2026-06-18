<?php
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart and quantities
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['cart_quantities'])) {
    $_SESSION['cart_quantities'] = [];
}

// Add to cart - INCREASE QUANTITY IF SAME ITEM ADDED
if (isset($_POST['add'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Check if product already in cart
    $existing_index = array_search($product_id, $_SESSION['cart']);
    if ($existing_index !== false) {
        // Increase quantity if already in cart
        $_SESSION['cart_quantities'][$existing_index]++;
    } else {
        // Add new item
        $_SESSION['cart'][] = $product_id;
        $_SESSION['cart_quantities'][] = 1;
    }
    header("Location: shop.php");
    exit();
}

// Update quantity
if (isset($_POST['update_qty'])) {
    $index = (int)$_POST['index'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $_SESSION['cart_quantities'][$index] = $quantity;
    } else {
        // Remove if quantity is 0
        unset($_SESSION['cart'][$index]);
        unset($_SESSION['cart_quantities'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        $_SESSION['cart_quantities'] = array_values($_SESSION['cart_quantities']);
    }
    header("Location: cart.php");
    exit();
}

// Remove item
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    unset($_SESSION['cart'][$index]);
    unset($_SESSION['cart_quantities'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    $_SESSION['cart_quantities'] = array_values($_SESSION['cart_quantities']);
    header("Location: cart.php");
    exit();
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_quantities'] = [];
    header("Location: cart.php");
    exit();
}

// Calculate totals
$subtotal = 0;
$cart_items = [];
foreach ($_SESSION['cart'] as $index => $id) {
    $result = mysqli_query($conn, "SELECT * FROM tblclothes WHERE ClothesID=$id");
    $product = mysqli_fetch_assoc($result);
    if ($product) {
        $quantity = isset($_SESSION['cart_quantities'][$index]) ? $_SESSION['cart_quantities'][$index] : 1;
        $item_total = $product['Price'] * $quantity;
        
        $product['cart_index'] = $index;
        $product['quantity'] = $quantity;
        $product['item_total'] = $item_total;
        $cart_items[] = $product;
        $subtotal += $item_total;
    }
}
$tax = $subtotal * 0.15; // 15% VAT
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cart - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .cart-table th, .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e0d5;
            text-align: left;
            vertical-align: middle;
        }
        .cart-table th {
            background: #f5f2ed;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.8rem;
        }
        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #e5e0d5;
            border-radius: 4px;
        }
        .btn-update {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.7rem;
        }
        .btn-update:hover {
            background: #e67e22;
        }
        .btn-remove {
            color: #c0392b;
            text-decoration: none;
            font-size: 0.8rem;
        }
        .btn-remove:hover {
            text-decoration: underline;
        }
        .cart-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: space-between;
        }
        .btn-clear {
            background: #c0392b;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-continue {
            background: #2c3e50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }
        .checkout-btn {
            background: #e67e22;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }
        .checkout-btn:hover {
            background: #d35400;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="cart-container">
    <div class="cart-header">
        <h1>Shopping Cart</h1>
        <p>Review and edit your curated selections</p>
    </div>
    
    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 60px;">
            <div style="font-size: 4rem; margin-bottom: 20px;">🛒</div>
            <h3>Your collection is empty</h3>
            <p>Discover pieces that speak to you</p>
            <a href="shop.php" class="btn-add" style="display: inline-block; width: auto; margin-top: 20px;">Browse Collection →</a>
        </div>
    <?php else: ?>
    
    <!-- Cart Items Table -->
    <table class="cart-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cart_items as $item): ?>
            <tr>
                <td>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <div style="width: 60px; height: 60px; background: #f5f2ed; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                            <?php if (!empty($item['Image']) && file_exists("images/".$item['Image'])): ?>
                                <img src="images/<?php echo $item['Image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <div style="font-size: 1.5rem;">👕</div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="cart-item-title" style="font-weight: 600;"><?php echo htmlspecialchars($item['Name']); ?></div>
                            <div class="cart-item-sku" style="font-size: 0.7rem; color: #888;">Ref: <?php echo $item['ClothesID']; ?> | <?php echo $item['Size']; ?> | <?php echo $item['Color']; ?></div>
                            <?php if(!empty($item['Brand'])): ?>
                                <div style="font-size: 0.7rem; color: #e67e22;"><?php echo htmlspecialchars($item['Brand']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td>R<?php echo number_format($item['Price'], 2); ?></td>
                <td>
                    <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                        <input type="hidden" name="index" value="<?php echo $item['cart_index']; ?>">
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" max="99" class="quantity-input">
                        <button type="submit" name="update_qty" class="btn-update">Update</button>
                    </form>
                </td>
                <td style="font-weight: bold; color: #e67e22;">R<?php echo number_format($item['item_total'], 2); ?></td>
                <td>
                    <a href="?remove=<?php echo $item['cart_index']; ?>" class="btn-remove" onclick="return confirm('Remove this item?')">Remove</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Cart Actions -->
    <div class="cart-actions">
        <a href="shop.php" class="btn-continue">← Continue Shopping</a>
        <a href="?clear=1" class="btn-clear" onclick="return confirm('Clear entire cart?')">Clear Cart</a>
    </div>
    
    <!-- Cart Summary -->
    <div class="cart-summary" style="margin-top: 30px; max-width: 400px; margin-left: auto;">
        <h3>Order Summary</h3>
        <div class="summary-row">
            <span>Subtotal</span>
            <span>R<?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping</span>
            <span>Complimentary</span>
        </div>
        <div class="summary-row">
            <span>VAT (15%)</span>
            <span>R<?php echo number_format($tax, 2); ?></span>
        </div>
        <div class="summary-total">
            <span>Total</span>
            <span>R<?php echo number_format($total, 2); ?></span>
        </div>
        <a href="delivery.php">
            <button class="checkout-btn" style="width: 100%; margin-top: 20px;">Proceed to Checkout →</button>
        </a>
    </div>
    
    <?php endif; ?>
</div>
</body>
</html>