<?php
// Database connection
$productDB = new mysqli("localhost", "root", "root", "productInfo_db");

if ($productDB->connect_error) {
    die("Database connection failed: " . $productDB->connect_error);
}

// Get post ID from request
if (isset($_GET['id'])) {
    $postId = intval($_GET['id']);

    // Delete the post from `products` table
    $deletePostQuery = "DELETE FROM products WHERE id = ?";
    $stmt = $productDB->prepare($deletePostQuery);
    $stmt->bind_param("i", $postId);

    if ($stmt->execute()) {
        echo "Post removed successfully.";
    } else {
        echo "Error removing post: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid post ID.";
}

$productDB->close();
?>
