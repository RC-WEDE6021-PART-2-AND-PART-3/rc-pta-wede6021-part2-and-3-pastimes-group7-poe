<?php
// loadClothingStore.php - Creates all tables with 30 entries
include 'DBConn.php';

// Drop all tables if exist
mysqli_query($conn, "DROP TABLE IF EXISTS tblAorder");
mysqli_query($conn, "DROP TABLE IF EXISTS tblClothes");
mysqli_query($conn, "DROP TABLE IF EXISTS tblUser");
mysqli_query($conn, "DROP TABLE IF EXISTS tblAdmin");

// Create tblUser
mysqli_query($conn, "CREATE TABLE tblUser (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Status VARCHAR(20) DEFAULT 'pending',
    RegistrationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create tblAdmin
mysqli_query($conn, "CREATE TABLE tblAdmin (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    FullName VARCHAR(100),
    Role VARCHAR(50) DEFAULT 'admin'
)");

// Create tblClothes
mysqli_query($conn, "CREATE TABLE tblClothes (
    ClothesID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Price DECIMAL(10,2) NOT NULL,
    Stock INT NOT NULL DEFAULT 0,
    Category VARCHAR(50),
    Image VARCHAR(255),
    Size VARCHAR(10),
    Color VARCHAR(30),
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create tblAorder
mysqli_query($conn, "CREATE TABLE tblAorder (
    OrderID INT PRIMARY KEY AUTO_INCREMENT,
    UserID INT,
    ClothesID INT,
    Quantity INT NOT NULL,
    OrderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status VARCHAR(50) DEFAULT 'pending',
    TotalAmount DECIMAL(10,2),
    DeliveryAddress TEXT,
    Phone VARCHAR(20),
    PaymentMethod VARCHAR(50),
    FOREIGN KEY (UserID) REFERENCES tblUser(UserID),
    FOREIGN KEY (ClothesID) REFERENCES tblClothes(ClothesID)
)");

echo "✅ All tables created!<br>";

// Insert 30 users
for ($i = 1; $i <= 30; $i++) {
    $username = "User$i";
    $email = "user$i@example.com";
    $password = md5("pass$i");
    $status = ($i % 2 == 0) ? 'verified' : 'pending';
    mysqli_query($conn, "INSERT INTO tblUser (Username, Email, Password, Status) 
                         VALUES ('$username', '$email', '$password', '$status')");
}
echo "✅ Inserted 30 users<br>";

// Insert admin
mysqli_query($conn, "INSERT INTO tblAdmin (Username, Password, Email, FullName) 
                     VALUES ('admin', MD5('admin123'), 'admin@clothingstore.com', 'Admin User')");
echo "✅ Inserted admin (admin/admin123)<br>";

// Insert 30 products
$categories = ['T-Shirt', 'Jeans', 'Dress', 'Jacket', 'Shoes'];
$sizes = ['S', 'M', 'L', 'XL'];
$colors = ['Red', 'Blue', 'Black', 'White'];

for ($i = 1; $i <= 30; $i++) {
    $name = $categories[array_rand($categories)] . " " . $i;
    $price = rand(199, 1999) / 10;
    $stock = rand(0, 50);
    $category = $categories[array_rand($categories)];
    $size = $sizes[array_rand($sizes)];
    $color = $colors[array_rand($colors)];
    
    mysqli_query($conn, "INSERT INTO tblClothes (Name, Description, Price, Stock, Category, Size, Color) 
                         VALUES ('$name', 'Sample description', $price, $stock, '$category', '$size', '$color')");
}
echo "✅ Inserted 30 products<br>";
echo "<br>🎉 Database setup complete!";
?>