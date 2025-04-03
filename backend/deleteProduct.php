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

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get product ID from POST data
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

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

// First, verify that the product belongs to the logged-in user
$verify_stmt = $conn->prepare("SELECT image_path FROM products WHERE product_id = ? AND user_id = ?");
$verify_stmt->bind_param("ii", $product_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    // Product doesn't exist or doesn't belong to this user
    $verify_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'error' => 'Product not found or access denied']);
    exit;
}

// Get the image path to delete the file later
$row = $verify_result->fetch_assoc();
$image_path = $row['image_path'];
$verify_stmt->close();

// Delete the product
$delete_stmt = $conn->prepare("DELETE FROM products WHERE product_id = ? AND user_id = ?");
$delete_stmt->bind_param("ii", $product_id, $user_id);
$success = $delete_stmt->execute();

if ($success) {
    // Try to delete the product image if it exists
    if (!empty($image_path)) {
        $file_path = '../' . $image_path;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete product: ' . $delete_stmt->error]);
}

// Close statement and connection
$delete_stmt->close();
$conn->close();
?>