<?php
include 'DBConn.php';

// Create images folder if not exists
if (!file_exists('images')) {
    mkdir('images', 0777, true);
    echo "✅ Created 'images' folder<br>";
}

// Sample image URLs (you need to download these manually)
$images = [
    'tshirt.jpg' => 'https://images.pexels.com/photos/428338/pexels-photo-428338.jpeg?w=200',
    'jeans.jpg' => 'https://images.pexels.com/photos/1598507/pexels-photo-1598507.jpeg?w=200',
    'dress.jpg' => 'https://images.pexels.com/photos/1159670/pexels-photo-1159670.jpeg?w=200',
    'jacket.jpg' => 'https://images.pexels.com/photos/983497/pexels-photo-983497.jpeg?w=200',
    'shoes.jpg' => 'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg?w=200'
];

echo "<h2>Image Setup Instructions:</h2>";
echo "<p>Please manually download these images and save them to the 'images' folder:</p>";
echo "<ul>";
foreach ($images as $filename => $url) {
    echo "<li><strong>$filename</strong> - <a href='$url' target='_blank'>Download</a></li>";
}
echo "</ul>";

// Update database with image names
echo "<h3>Updating database...</h3>";

// Update based on category
mysqli_query($conn, "UPDATE tblclothes SET Image = 'tshirt.jpg' WHERE Category = 'T-Shirt' OR Name LIKE '%T-Shirt%'");
echo "✅ Updated T-Shirts<br>";

mysqli_query($conn, "UPDATE tblclothes SET Image = 'jeans.jpg' WHERE Category = 'Jeans' OR Name LIKE '%Jeans%'");
echo "✅ Updated Jeans<br>";

mysqli_query($conn, "UPDATE tblclothes SET Image = 'dress.jpg' WHERE Category = 'Dress' OR Name LIKE '%Dress%'");
echo "✅ Updated Dresses<br>";

mysqli_query($conn, "UPDATE tblclothes SET Image = 'jacket.jpg' WHERE Category = 'Jacket' OR Name LIKE '%Jacket%'");
echo "✅ Updated Jackets<br>";

mysqli_query($conn, "UPDATE tblclothes SET Image = 'shoes.jpg' WHERE Category = 'Shoes' OR Name LIKE '%Shoes%'");
echo "✅ Updated Shoes<br>";

// Set default for any remaining
mysqli_query($conn, "UPDATE tblclothes SET Image = 'tshirt.jpg' WHERE Image IS NULL OR Image = ''");
echo "✅ Set default images for remaining products<br>";

// Show results
$result = mysqli_query($conn, "SELECT ClothesID, Name, Image FROM tblclothes LIMIT 10");
echo "<h3>Sample Products with Images:</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Image File</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['ClothesID']}</td>";
    echo "<td>{$row['Name']}</td>";
    echo "<td>{$row['Image']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Download the images from the links above</li>";
echo "<li>Save them to: <strong>C:/xampp/htdocs/websites/Pastimes/Pastimes/images/</strong></li>";
echo "<li>Visit your shop page to see the images: <a href='shop.php'>Shop Page</a></li>";
echo "</ol>";

mysqli_close($conn);
?>