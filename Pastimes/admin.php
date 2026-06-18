<?php
session_start();
include 'DBConn.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ==================== USER MANAGEMENT ====================

// Verify user
if (isset($_GET['verify'])) {
    $id = $_GET['verify'];
    mysqli_query($conn, "UPDATE tbluser SET Status='verified' WHERE UserID=$id");
    header("Location: admin.php?msg=User verified");
    exit();
}

// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tbluser WHERE UserID=$id");
    header("Location: admin.php?msg=User deleted");
    exit();
}

// Add user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $status = $_POST['status'];
    
    mysqli_query($conn, "INSERT INTO tbluser (Username, Email, Password, Status) 
                         VALUES ('$username', '$email', '$password', '$status')");
    header("Location: admin.php?msg=User added");
    exit();
}

// Update user status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $id = $_POST['user_id'];
    $status = $_POST['status'];
    mysqli_query($conn, "UPDATE tbluser SET Status='$status' WHERE UserID=$id");
    header("Location: admin.php?msg=User updated");
    exit();
}

// ==================== PRODUCT MANAGEMENT ====================

// Add product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $image = '';
    
    // Create images folder if not exists
    if (!file_exists('images')) {
        mkdir('images', 0777, true);
    }
    
    // Handle image upload
    if (!empty($_FILES['product_image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $image = time() . '_' . basename($_FILES['product_image']['name']);
            move_uploaded_file($_FILES['product_image']['tmp_name'], "images/" . $image);
        }
    }
    
    mysqli_query($conn, "INSERT INTO tblclothes (Name, Brand, Description, Price, Stock, Category, Size, Color, Image) 
                         VALUES ('$name', '$brand', '$description', '$price', '$stock', '$category', '$size', '$color', '$image')");
    header("Location: admin.php?msg=Product added");
    exit();
}

// Update product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $id = $_POST['edit_product_id'];
    $name = $_POST['edit_name'];
    $brand = $_POST['edit_brand'];
    $description = $_POST['edit_description'];
    $price = $_POST['edit_price'];
    $stock = $_POST['edit_stock'];
    $category = $_POST['edit_category'];
    $size = $_POST['edit_size'];
    $color = $_POST['edit_color'];
    
    mysqli_query($conn, "UPDATE tblclothes SET 
        Name='$name', 
        Brand='$brand', 
        Description='$description', 
        Price='$price', 
        Stock='$stock', 
        Category='$category', 
        Size='$size', 
        Color='$color' 
        WHERE ClothesID=$id");
    header("Location: admin.php?msg=Product updated");
    exit();
}

// Delete product
if (isset($_GET['delete_product'])) {
    $id = (int)$_GET['delete_product'];
    mysqli_query($conn, "DELETE FROM tblclothes WHERE ClothesID=$id");
    header("Location: admin.php?msg=Product deleted");
    exit();
}

// ==================== FETCH DATA ====================

$users = mysqli_query($conn, "SELECT * FROM tbluser ORDER BY UserID DESC");
$products = mysqli_query($conn, "SELECT * FROM tblclothes ORDER BY ClothesID DESC");
$total_users = mysqli_num_rows($users);
$total_products = mysqli_num_rows($products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #e5e0d5;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #e67e22;
            font-family: 'Playfair Display', serif;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.85rem;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .admin-section {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e0d5;
            padding: 24px;
            margin-bottom: 40px;
        }
        
        .admin-section h2 {
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
            border-bottom: 2px solid #e67e22;
            display: inline-block;
            padding-bottom: 5px;
        }
        
        .add-form {
            background: #faf9f7;
            padding: 24px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .add-form h3 {
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .add-form input, .add-form select, .add-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }
        
        .add-form textarea {
            resize: vertical;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #e67e22;
            color: white;
        }
        
        .btn-primary:hover {
            background: #d35400;
        }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 0.8rem;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        
        .btn-verify {
            background: #27ae60;
            color: white;
        }
        
        .btn-verify:hover {
            background: #219a52;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-edit:hover {
            background: #2980b9;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c0392b;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
        }
        
        .data-table th, .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background: #2c3e50;
            color: white;
            font-weight: 600;
        }
        
        .data-table tr:hover {
            background: #faf9f7;
        }
        
        .verified {
            color: #27ae60;
            font-weight: 600;
        }
        
        .pending {
            color: #f39c12;
            font-weight: 600;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-content h3 {
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .modal-content input, .modal-content textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .data-table {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="admin-container">
    <h1>Admin Panel</h1>
    <p style="margin-bottom: 20px;">Welcome, Admin <?php echo $_SESSION['admin_username']; ?>! | <a href="logout.php">Logout</a></p>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">✅ <?php echo $_GET['msg']; ?> successfully!</div>
    <?php endif; ?>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_users; ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_products; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tblaorder")); ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tblmessages WHERE IsRead=0")); ?></div>
            <div class="stat-label">Unread Messages</div>
        </div>
    </div>
    
    <!-- ==================== USER MANAGEMENT SECTION ==================== -->
    <div class="admin-section">
        <h2>👥 Customer Management</h2>
        
        <!-- Add User Form -->
        <div class="add-form">
            <h3>➕ Add New Customer</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="username" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="status">
                        <option value="verified">Verified</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary">Add Customer</button>
            </form>
        </div>
        
        <!-- Users Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?php echo $row['UserID']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['Username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                    <td class="<?php echo $row['Status']; ?>"><?php echo ucfirst($row['Status']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($row['RegistrationDate'])); ?></td>
                    <td>
                        <?php if($row['Status'] == 'pending'): ?>
                            <a href="?verify=<?php echo $row['UserID']; ?>" class="btn-sm btn-verify">✓ Verify</a>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $row['UserID']; ?>">
                            <select name="status" onchange="this.form.submit()" style="padding: 4px;">
                                <option value="pending" <?php echo $row['Status']=='pending'?'selected':''; ?>>Pending</option>
                                <option value="verified" <?php echo $row['Status']=='verified'?'selected':''; ?>>Verified</option>
                            </select>
                            <input type="hidden" name="update_user" value="1">
                        </form>
                        <a href="?delete=<?php echo $row['UserID']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this user?')">🗑 Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- ==================== PRODUCT MANAGEMENT SECTION ==================== -->
    <div class="admin-section">
        <h2>👕 Product Management</h2>
        
        <!-- Add Product Form -->
        <div class="add-form">
            <h3>➕ Add New Product</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <input type="text" name="product_name" placeholder="Product Name" required>
                    <input type="text" name="brand" placeholder="Brand">
                    <input type="number" name="price" placeholder="Price (R)" step="0.01" required>
                    <input type="number" name="stock" placeholder="Stock" value="0">
                </div>
                <div class="form-row">
                    <input type="text" name="category" placeholder="Category">
                    <input type="text" name="size" placeholder="Size (S, M, L, XL)">
                    <input type="text" name="color" placeholder="Color">
                    <input type="file" name="product_image" accept="image/*">
                </div>
                <textarea name="description" placeholder="Description" rows="3"></textarea>
                <button type="submit" name="add_product" class="btn btn-primary" style="margin-top: 15px;">Add Product</button>
            </form>
        </div>
        
        <!-- Products Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($products, 0);
                while($prod = mysqli_fetch_assoc($products)): 
                ?>
                <tr>
                    <td><?php echo $prod['ClothesID']; ?></td>
                    <td>
                        <?php if (!empty($prod['Image']) && file_exists("images/".$prod['Image'])): ?>
                            <img src="images/<?php echo $prod['Image']; ?>" class="product-image">
                        <?php else: ?>
                            <span style="font-size: 1.5rem;">👕</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($prod['Name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($prod['Brand'] ?? '-'); ?></td>
                    <td>R<?php echo number_format($prod['Price'], 2); ?></td>
                    <td><?php echo $prod['Stock']; ?></td>
                    <td><?php echo $prod['Size'] ?? '-'; ?></td>
                    <td><?php echo $prod['Color'] ?? '-'; ?></td>
                    <td>
                        <button onclick="openEditModal(<?php echo $prod['ClothesID']; ?>)" class="btn-sm btn-edit">✏️ Edit</button>
                        <a href="?delete_product=<?php echo $prod['ClothesID']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this product?')">🗑 Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Product</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="edit_product_id" id="edit_id">
            <input type="text" name="edit_name" id="edit_name" placeholder="Product Name" required>
            <input type="text" name="edit_brand" id="edit_brand" placeholder="Brand">
            <textarea name="edit_description" id="edit_description" placeholder="Description" rows="3"></textarea>
            <input type="number" name="edit_price" id="edit_price" placeholder="Price (R)" step="0.01" required>
            <input type="number" name="edit_stock" id="edit_stock" placeholder="Stock">
            <input type="text" name="edit_category" id="edit_category" placeholder="Category">
            <input type="text" name="edit_size" id="edit_size" placeholder="Size">
            <input type="text" name="edit_color" id="edit_color" placeholder="Color">
            <div class="modal-buttons">
                <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                <button type="button" onclick="closeModal()" class="btn" style="background: #ccc;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(productId) {
    // Fetch product data via redirect with parameter
    window.location.href = '?edit_product=' + productId;
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Show modal if edit_product parameter is present
<?php if (isset($_GET['edit_product'])): 
    $edit_id = (int)$_GET['edit_product'];
    $edit_prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tblclothes WHERE ClothesID=$edit_id"));
    if ($edit_prod):
?>
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('edit_id').value = '<?php echo $edit_prod['ClothesID']; ?>';
    document.getElementById('edit_name').value = '<?php echo addslashes($edit_prod['Name']); ?>';
    document.getElementById('edit_brand').value = '<?php echo addslashes($edit_prod['Brand'] ?? ''); ?>';
    document.getElementById('edit_description').value = '<?php echo addslashes($edit_prod['Description'] ?? ''); ?>';
    document.getElementById('edit_price').value = '<?php echo $edit_prod['Price']; ?>';
    document.getElementById('edit_stock').value = '<?php echo $edit_prod['Stock']; ?>';
    document.getElementById('edit_category').value = '<?php echo addslashes($edit_prod['Category'] ?? ''); ?>';
    document.getElementById('edit_size').value = '<?php echo addslashes($edit_prod['Size'] ?? ''); ?>';
    document.getElementById('edit_color').value = '<?php echo addslashes($edit_prod['Color'] ?? ''); ?>';
<?php 
    endif;
endif; 
?>

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>