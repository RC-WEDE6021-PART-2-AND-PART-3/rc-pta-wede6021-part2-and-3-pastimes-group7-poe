<?php
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$error = '';
$success = '';

// Create seller requests table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tblsellerrequest (
    RequestID INT PRIMARY KEY AUTO_INCREMENT,
    SellerID INT,
    SellerName VARCHAR(100),
    SellerEmail VARCHAR(100),
    ClothingName VARCHAR(100) NOT NULL,
    Brand VARCHAR(100),
    Description TEXT,
    Image VARCHAR(255),
    Price DECIMAL(10,2),
    Status VARCHAR(20) DEFAULT 'pending',
    RequestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SellerID) REFERENCES tbluser(UserID)
)");

// Create uploads folder if not exists
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_request'])) {
    $clothing_name = trim($_POST['clothing_name']);
    $brand = trim($_POST['brand']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $image = '';
    
    if (empty($clothing_name) || empty($brand) || empty($description) || empty($price)) {
        $error = "Please fill in all fields.";
    } else {
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $image = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
            } else {
                $error = "Invalid image format. Use JPG, PNG, GIF, or WEBP.";
            }
        }
        
        if (!$error) {
            $insert = "INSERT INTO tblsellerrequest (SellerID, SellerName, SellerEmail, ClothingName, Brand, Description, Image, Price, Status) 
                       VALUES ('$user_id', '$username', '$email', '$clothing_name', '$brand', '$description', '$image', '$price', 'pending')";
            
            if (mysqli_query($conn, $insert)) {
                $success = "Your request has been submitted! Admin will review it within 2-3 business days.";
            } else {
                $error = "Failed to submit request: " . mysqli_error($conn);
            }
        }
    }
}

// Get user's existing requests
$my_requests = mysqli_query($conn, "SELECT * FROM tblsellerrequest WHERE SellerID=$user_id ORDER BY RequestDate DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sell Your Clothes - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .seller-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .seller-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .form-card {
            background: white;
            border: 1px solid #e5e0d5;
            border-radius: 10px;
            padding: 30px;
        }
        .form-card h2 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e0d5;
            border-radius: 5px;
        }
        .request-item {
            background: white;
            border: 1px solid #e5e0d5;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .status-pending {
            color: #e67e22;
            font-weight: bold;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
        .btn-submit {
            background: #2c3e50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            background: #e67e22;
        }
        .request-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="seller-container">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1>Sell Your Clothes</h1>
        <p>Submit a request to sell your pre-loved items</p>
    </div>
    
    <div class="seller-layout">
        <!-- Request Form -->
        <div class="form-card">
            <h2>Submit a Selling Request</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Clothing Name *</label>
                    <input type="text" name="clothing_name" placeholder="e.g., Vintage Denim Jacket" required>
                </div>
                <div class="form-group">
                    <label>Brand *</label>
                    <input type="text" name="brand" placeholder="e.g., Levi's, Nike, Zara" required>
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" placeholder="Describe the item (condition, size, color, material, etc.)" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label>Price (R) *</label>
                    <input type="number" name="price" placeholder="e.g., 299.99" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Image (Optional)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button type="submit" name="submit_request" class="btn-submit">Submit Request →</button>
            </form>
        </div>
        
        <!-- My Requests -->
        <div>
            <h2>My Requests</h2>
            <?php if (mysqli_num_rows($my_requests) == 0): ?>
                <div class="form-card" style="text-align: center;">
                    <p>You haven't submitted any selling requests yet.</p>
                </div>
            <?php else: ?>
                <?php while($req = mysqli_fetch_assoc($my_requests)): ?>
                    <div class="request-item">
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <?php if (!empty($req['Image']) && file_exists("uploads/".$req['Image'])): ?>
                                <img src="uploads/<?php echo $req['Image']; ?>" class="request-image">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">👕</div>
                            <?php endif; ?>
                            <div style="flex: 1;">
                                <h3><?php echo htmlspecialchars($req['ClothingName']); ?></h3>
                                <p><strong>Brand:</strong> <?php echo htmlspecialchars($req['Brand']); ?></p>
                                <p><strong>Price:</strong> R<?php echo number_format($req['Price'], 2); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="status-<?php echo $req['Status']; ?>">
                                        <?php echo ucfirst($req['Status']); ?>
                                    </span>
                                </p>
                                <small>Submitted: <?php echo date('M j, Y', strtotime($req['RequestDate'])); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>