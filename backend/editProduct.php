<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to access user information
session_start();

// Set header to return JSON
header('Content-Type: application/json');

// Debug: Log the incoming data
$debug_log = [
    'session' => isset($_SESSION['user_id']) ? 'Session exists' : 'No session',
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'files' => isset($_FILES['product_image']) ? 'File uploaded' : 'No file'
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in', 'debug' => $debug_log]);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method', 'debug' => $debug_log]);
    exit;
}

// Get product ID and updated information from POST data
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$product_name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

// Add data to debug log
$debug_log['parsed_data'] = [
    'product_id' => $product_id,
    'product_name' => $product_name,
    'price' => $price,
    'description' => strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description,
    'category' => $category,
    'quantity' => $quantity
];

// Validate data
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID', 'debug' => $debug_log]);
    exit;
}

if (empty($product_name) || $price <= 0 || empty($description) || empty($category) || $quantity <= 0) {
    echo json_encode(['success' => false, 'error' => 'All fields are required and must be valid', 'debug' => $debug_log]);
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
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error, 'debug' => $debug_log]);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];
$debug_log['user_id'] = $user_id;

try {
    // First, verify that the product belongs to the logged-in user
    $verify_stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND user_id = ?");
    if (!$verify_stmt) {
        throw new Exception("Prepare statement error: " . $conn->error);
    }
    
    $verify_stmt->bind_param("ii", $product_id, $user_id);
    if (!$verify_stmt->execute()) {
        throw new Exception("Execute verify statement error: " . $verify_stmt->error);
    }
    
    $verify_result = $verify_stmt->get_result();
    if ($verify_result->num_rows === 0) {
        // Product doesn't exist or doesn't belong to this user
        $verify_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Product not found or access denied', 'debug' => $debug_log]);
        exit;
    }

    // Get the original product data
    $product_data = $verify_result->fetch_assoc();
    $verify_stmt->close();

    // Handle file upload if a new image was provided
    $image_path = $product_data['image_path']; // Default to current image path
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $target_dir = "../uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate unique filename
        $filename = time() . "_" . basename($_FILES['product_image']['name']);
        $target_file = $target_dir . $filename;
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            // Delete old image if it exists
            if (!empty($product_data['image_path'])) {
                $old_file = "../" . $product_data['image_path'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            
            $image_path = "uploads/" . $filename; // Store relative path in database
        } else {
            throw new Exception("Failed to upload image: " . $_FILES['product_image']['error']);
        }
    }

    // Update the product
    $update_stmt = $conn->prepare("UPDATE products SET 
                                product_name = ?, 
                                price = ?, 
                                description = ?, 
                                category = ?, 
                                quantity = ?, 
                                image_path = ? 
                                WHERE product_id = ? AND user_id = ?");
    
    if (!$update_stmt) {
        throw new Exception("Prepare update statement error: " . $conn->error);
    }
    
    $update_stmt->bind_param("sdssisi", 
                            $product_name, 
                            $price, 
                            $description, 
                            $category, 
                            $quantity, 
                            $image_path, 
                            $product_id, 
                            $user_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Execute update statement error: " . $update_stmt->error);
    }
    
    // Get the updated product data to return
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $updated_product = $result->fetch_assoc();
    $stmt->close();
    $update_stmt->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Product updated successfully',
        'product' => $updated_product
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error updating product: ' . $e->getMessage(),
        'debug' => $debug_log
    ]);
} finally {
    // Close connection
    $conn->close();
}
?>