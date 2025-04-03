<?php
// Start session to access user information
session_start();

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "root"; // Changed from empty string to "root" to match your other files
$dbname = "productInfo_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user's products from database
$stmt = $conn->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY date_listed DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Store products in an array
$products = [];
while ($row = $result->fetch_assoc()) {
    // Make sure image path is properly formatted
    if (!empty($row['image_path'])) {
        // If the path doesn't start with http or /, make it relative to the root
        if (strpos($row['image_path'], 'http') !== 0 && strpos($row['image_path'], '/') !== 0) {
            $row['image_path'] = '../' . $row['image_path'];
        }
    }
    $products[] = $row;
}

// Close connection
$stmt->close();
$conn->close();

// Return products as JSON with total count
echo json_encode([
    'products' => $products,
    'total' => count($products)
]);
?>