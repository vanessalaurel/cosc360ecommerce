<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if product_id is provided
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo "Error: No product ID provided";
    exit;
}

$product_id = intval($_GET['product_id']);

// Database connection
$productDB = new mysqli("localhost", "root", "root", "productInfo_db");
if ($productDB->connect_error) {
    die("Product DB Connection failed: " . $productDB->connect_error);
}

try {
    // Get product image path first
    $stmt = $productDB->prepare("SELECT image_path FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        echo "Error: Product not found";
        exit;
    }
    
    // Delete the product from the database
    $stmt = $productDB->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();
    
    // Delete the image file if it exists
    if ($deleted && !empty($product['image_path'])) {
        $imagePath = "../" . $product['image_path']; // Adjust path if needed
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    echo "Product has been removed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Close database connection
$productDB->close();
?>
