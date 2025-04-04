<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user_id is provided
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo "Error: No user ID provided";
    exit;
}

$user_id = intval($_GET['user_id']);

// Database connections
$userDB = new mysqli("localhost", "root", "root", "userInfo_db");
if ($userDB->connect_error) {
    die("User DB Connection failed: " . $userDB->connect_error);
}

$productDB = new mysqli("localhost", "root", "root", "productInfo_db");
if ($productDB->connect_error) {
    die("Product DB Connection failed: " . $productDB->connect_error);
}

try {
    // Start transaction
    $userDB->begin_transaction();
    $productDB->begin_transaction();
    
    // First, get all products by this user to delete their image files
    $stmt = $productDB->prepare("SELECT image_path FROM products WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Delete product images from the file system
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['image_path'])) {
            $imagePath = "../" . $row['image_path']; // Adjust path if needed
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }
    $stmt->close();
    
    // Delete all products for this user
    $stmt = $productDB->prepare("DELETE FROM products WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $productsDeleted = $stmt->affected_rows;
    $stmt->close();
    
    // Delete the user from the database - no need to check for profile_image_url since it doesn't exist
    $stmt = $userDB->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userDeleted = $stmt->affected_rows;
    $stmt->close();
    
    // Commit transactions if everything went well
    $userDB->commit();
    $productDB->commit();
    
    echo "User and " . $productsDeleted . " product(s) have been removed successfully.";
} catch (Exception $e) {
    // Rollback transactions if any error occurred
    $userDB->rollback();
    $productDB->rollback();
    echo "Error: " . $e->getMessage();
}

// Close database connections
$userDB->close();
$productDB->close();
?>