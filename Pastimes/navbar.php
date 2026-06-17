<?php
if (!isset($active)) $active = '';
$user = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$is_admin = isset($_SESSION['admin_id']);
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<nav class="navbar">
    <a href="<?php echo ($user || $is_admin) ? 'shop.php' : 'login.php'; ?>" class="nav-brand">
        Pastimes<span>.</span>
    </a>
    <div class="nav-links">
        <a href="shop.php" class="<?php echo $active==='shop'?'active':''; ?>">SHOP</a>
        <?php if ($user && !$is_admin): ?>
            <a href="message.php" class="<?php echo $active==='message'?'active':''; ?>">MESSAGES</a>
            <a href="delivery.php" class="<?php echo $active==='delivery'?'active':''; ?>">DELIVERY</a>
        <?php endif; ?>
        <?php if ($is_admin): ?>
            <a href="admin.php" class="<?php echo $active==='admin'?'active':''; ?>">ADMIN</a>
        <?php endif; ?>
    </div>
    <div class="nav-icons">
        <?php if ($user || $is_admin): ?>
            <a href="cart.php">🛒<?php if($cart_count > 0): ?><span class="cart-count"><?php echo $cart_count; ?></span><?php endif; ?></a>
            <a href="order_history.php">📋 ORDERS</a>
            <a href="logout.php">🔓</a>
        <?php else: ?>
            <a href="login.php">🔑</a>
            <a href="register.php">✍️</a>
        <?php endif; ?>
    </div>
    <!-- Add after existing links -->
<a href="seller_request.php" class="<?php echo $active==='seller'?'active':''; ?>">💰 SELL</a>
<!-- Add after existing links -->
<a href="seller_request.php" class="<?php echo $active==='seller'?'active':''; ?>">💰 SELL</a>
<a href="communication.php" class="<?php echo $active==='communication'?'active':''; ?>">💬 CHAT</a>

</nav>
