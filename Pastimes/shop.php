<?php
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$products = mysqli_query($conn, "SELECT * FROM tblClothes WHERE stock > 0 ORDER BY ClothesID DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shop - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php $active = 'shop'; include 'navbar.php'; ?>
<div class="page-wrapper">
    <div class="page-header">
        <h1>Archivist's Collection</h1>
        <p>Curated vintage pieces with storied pasts</p>
    </div>
    
    <div class="product-grid">
        <?php while($row = mysqli_fetch_assoc($products)): ?>
        <div class="product-card">
            <div class="product-image">
                <?php 
                $image_path = "images/" . $row['Image'];
                if (!empty($row['Image']) && file_exists($image_path)): ?>
                    <img src="<?php echo $image_path; ?>" alt="<?php echo $row['Name']; ?>">
                <?php else: ?>
                    <div style="font-size: 4rem;">👕</div>
                <?php endif; ?>
                <div class="product-badge">ARCHIVE</div>
            </div>
            <div class="product-info">
                <div class="product-name"><?php echo $row['Name']; ?></div>
                <div class="product-desc"><?php echo substr($row['Description'], 0, 60); ?>...</div>
                <div class="product-meta">
                    <span><?php echo $row['Size']; ?></span>
                    <span><?php echo $row['Color']; ?></span>
                </div>
                <div class="product-price">R<?php echo number_format($row['Price'], 2); ?></div>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="product_id" value="<?php echo $row['ClothesID']; ?>">
                    <button type="submit" name="add" class="btn-add">Add to Cart →</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>