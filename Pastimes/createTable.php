<?php
// createTable.php - Creates tblUser and loads data from userData.txt
include 'DBConn.php';

// First, drop tables in correct order (child tables first, then parent)
mysqli_query($conn, "DROP TABLE IF EXISTS tblAorder");
mysqli_query($conn, "DROP TABLE IF EXISTS tblMessages");
mysqli_query($conn, "DROP TABLE IF EXISTS tblUser");
mysqli_query($conn, "DROP TABLE IF EXISTS tblAdmin");
mysqli_query($conn, "DROP TABLE IF EXISTS tblClothes");

// Recreate tblUser
$sql = "CREATE TABLE tblUser (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Status VARCHAR(20) DEFAULT 'pending',
    RegistrationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "✅ Table 'tblUser' created successfully.<br>";
} else {
    die("Error creating table: " . mysqli_error($conn));
}

// Recreate tblAdmin
mysqli_query($conn, "CREATE TABLE tblAdmin (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    FullName VARCHAR(100),
    Role VARCHAR(50) DEFAULT 'admin'
)");
echo "✅ Table 'tblAdmin' created.<br>";

// Recreate tblClothes
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
echo "✅ Table 'tblClothes' created.<br>";

// Recreate tblAorder with foreign keys
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
echo "✅ Table 'tblAorder' created.<br>";

// Insert default admin
mysqli_query($conn, "INSERT INTO tblAdmin (Username, Password, Email, FullName) 
                     VALUES ('admin', MD5('admin123'), 'admin@clothingstore.com', 'System Admin')");
echo "✅ Admin user inserted (admin/admin123)<br>";

// Insert sample products
$products = [
    ['Classic White T-Shirt', '100% cotton comfortable t-shirt', 199.99, 50, 'T-Shirt', 'M', 'White'],
    ['Slim Fit Jeans', 'Dark blue denim jeans', 599.99, 30, 'Jeans', '32', 'Blue'],
    ['Floral Summer Dress', 'Beautiful floral pattern', 449.99, 15, 'Dress', 'M', 'Floral'],
    ['Leather Jacket', 'Genuine leather jacket', 1299.99, 10, 'Jacket', 'L', 'Black'],
    ['Running Shoes', 'Lightweight athletic shoes', 899.99, 25, 'Shoes', '9', 'Red']
];

foreach ($products as $p) {
    mysqli_query($conn, "INSERT INTO tblClothes (Name, Description, Price, Stock, Category, Size, Color) 
                         VALUES ('$p[0]', '$p[1]', $p[2], $p[3], '$p[4]', '$p[5]', '$p[6]')");
}
echo "✅ Sample products inserted.<br>";

// Load data from userData.txt
$file = fopen("userData.txt", "r");
if ($file) {
    $count = 0;
    while (($line = fgets($file)) !== false) {
        $line = trim($line);
        if (!empty($line)) {
            $data = explode(" ", $line);
            if (count($data) >= 4) {
                $username = mysqli_real_escape_string($conn, $data[0] . " " . $data[1]);
                $email = mysqli_real_escape_string($conn, $data[2]);
                $password = mysqli_real_escape_string($conn, $data[3]);
                
                $insert = "INSERT INTO tblUser (Username, Email, Password, Status) 
                          VALUES ('$username', '$email', '$password', 'verified')";
                
                if (mysqli_query($conn, $insert)) {
                    $count++;
                    echo "📝 Inserted: $username - $email<br>";
                }
            }
        }
    }
    fclose($file);
    echo "<br>✅ Successfully loaded $count records into tblUser!";
} else {
    echo "❌ Error opening userData.txt";
}

mysqli_close($conn);
?>