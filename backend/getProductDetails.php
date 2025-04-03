<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to access user information
session_start();

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Get product ID from GET parameter
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "productInfo_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch product details, ensuring it belongs to the current user
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Product not found or access denied']);
    exit;
}

$product = $result->fetch_assoc();

// Close connection
$stmt->close();
$conn->close();

// Return product details as JSON
echo json_encode(['success' => true, 'product' => $product]);
?>